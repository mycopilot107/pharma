<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Plan;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $companies = Company::with('plan')
            ->withCount([
                'users as admins_count' => fn ($q) => $q->where('role', UserRole::CompanyAdmin),
                'users as reps_count' => fn ($q) => $q->where('role', UserRole::Representative),
            ])
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->access === 'active', fn ($q) => $q->where('status', Company::STATUS_ACTIVE))
            ->when($request->access === 'inactive', fn ($q) => $q->where('status', Company::STATUS_SUSPENDED))
            ->when($request->access === 'pending', fn ($q) => $q->where('status', Company::STATUS_PENDING))
            ->when($request->plan_id, fn ($q, $id) => $q->where('plan_id', $id))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('super-admin.companies.index', [
            'companies' => $companies,
            'plans' => Plan::orderBy('user_limit')->get(),
        ]);
    }

    public function show(Company $company)
    {
        $company->load(['plan', 'users' => fn ($q) => $q->orderBy('role')->orderBy('name')]);

        return view('super-admin.companies.show', [
            'company' => $company,
            'plans' => Plan::where('is_active', true)->orderBy('user_limit')->get(),
        ]);
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
            'user_limit' => ['required', 'integer', 'min:1', 'max:500'],
            'status' => ['required', 'in:'.implode(',', array_keys(Company::statusOptions()))],
            'subscription_ends_at' => ['nullable', 'date'],
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);

        $company->update([
            'plan_id' => $plan->id,
            'user_limit' => $validated['user_limit'],
            'status' => $validated['status'],
            'subscription_ends_at' => $validated['subscription_ends_at'] ?? $company->subscription_ends_at,
        ]);

        return back()->with('success', 'Company updated.');
    }

    public function activate(Company $company)
    {
        $updates = ['status' => Company::STATUS_ACTIVE];

        if (! $company->subscribed_at) {
            $updates['subscribed_at'] = now();
        }

        if (! $company->subscription_ends_at) {
            $updates['subscription_ends_at'] = now()->addDays((int) config('pharma.subscription_days', 30));
        }

        $company->update($updates);

        return back()->with('success', "{$company->name} is now active. Users can sign in.");
    }

    public function deactivate(Company $company)
    {
        $company->update(['status' => Company::STATUS_SUSPENDED]);

        return back()->with('success', "{$company->name} is now inactive. Users cannot sign in.");
    }
}
