<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CompanyUserController extends Controller
{
    public function index(Request $request)
    {
        $company = $request->user()->company;

        $representatives = User::where('company_id', $company->id)
            ->where('role', UserRole::Representative)
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->withCount(['visits', 'targets'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('users.index', [
            'company' => $company,
            'representatives' => $representatives,
            'usedSlots' => $company->representativesCount(),
            'activeCount' => User::where('company_id', $company->id)
                ->where('role', UserRole::Representative)
                ->where('is_active', true)
                ->count(),
            'remainingSlots' => $company->remainingSlots(),
            'canAdd' => $company->canAddRepresentative(),
        ]);
    }

    public function create(Request $request)
    {
        $company = $request->user()->company;

        if (! $company->canAddRepresentative()) {
            return redirect()->route('users.index')
                ->with('error', 'You have reached the user limit for your plan ('.$company->user_limit.' users).');
        }

        return view('users.create', [
            'company' => $company,
            'remainingSlots' => $company->remainingSlots(),
        ]);
    }

    public function store(Request $request)
    {
        $company = $request->user()->company;

        if (! $company->canAddRepresentative()) {
            return back()->with('error', 'Plan user limit reached. Upgrade your plan to add more representatives.');
        }

        $validated = $this->validateRepresentative($request);

        User::create([
            'company_id' => $company->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'role' => UserRole::Representative,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('users.index')
            ->with('success', 'Medical representative added successfully.');
    }

    public function show(Request $request, User $user)
    {
        $this->authorizeRepresentative($user, $request);

        $user->loadCount([
            'visits',
            'targets',
            'visits as completed_visits_count' => fn ($q) => $q->where('status', \App\Enums\VisitStatus::Completed),
        ]);

        $ordersCount = \App\Models\Order::where('user_id', $user->id)->count();
        $expensesCount = \App\Models\Expense::where('user_id', $user->id)->count();

        return view('users.show', [
            'user' => $user,
            'company' => $request->user()->company,
            'ordersCount' => $ordersCount,
            'expensesCount' => $expensesCount,
        ]);
    }

    public function edit(Request $request, User $user)
    {
        $this->authorizeRepresentative($user, $request);

        return view('users.edit', [
            'user' => $user,
            'company' => $request->user()->company,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeRepresentative($user, $request);

        $validated = $this->validateRepresentative($request, $user);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if (! empty($validated['password'])) {
            $user->update(['password' => $validated['password']]);
        }

        return redirect()->route('users.show', $user)
            ->with('success', 'Representative updated successfully.');
    }

    public function destroy(Request $request, User $user)
    {
        $this->authorizeRepresentative($user, $request);

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Representative removed.');
    }

    protected function validateRepresentative(Request $request, ?User $user = null): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['sometimes', 'boolean'],
        ];

        if ($user) {
            $rules['password'] = ['nullable', 'string', 'confirmed', Password::min(8)];
        } else {
            $rules['password'] = ['required', 'string', 'confirmed', Password::min(8)];
        }

        return $request->validate($rules);
    }

    protected function authorizeRepresentative(User $user, Request $request): void
    {
        if ($user->company_id !== $request->user()->company_id
            || $user->role !== UserRole::Representative) {
            abort(403);
        }
    }
}
