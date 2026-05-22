<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TargetType;
use App\Enums\TargetUnit;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Target;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TargetController extends Controller
{
    public function index(Request $request)
    {
        $company = Auth::user()->company;

        $targets = Target::where('company_id', $company->id)
            ->with(['user', 'assigner'])
            ->when($request->user_id, fn ($q, $id) => $q->where('user_id', $id))
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $representatives = User::where('company_id', $company->id)
            ->where('role', UserRole::Representative)
            ->orderBy('name')
            ->get();

        $summary = [
            'active' => Target::where('company_id', $company->id)->where('status', 'active')->count(),
            'monthly' => Target::where('company_id', $company->id)->where('type', 'monthly')->where('status', 'active')->count(),
            'product' => Target::where('company_id', $company->id)->where('type', 'product')->where('status', 'active')->count(),
            'sales' => Target::where('company_id', $company->id)->where('type', 'sales')->where('status', 'active')->count(),
            'area' => Target::where('company_id', $company->id)->where('type', 'area')->where('status', 'active')->count(),
        ];

        return view('admin.targets.index', compact('targets', 'representatives', 'summary'));
    }

    public function create()
    {
        $representatives = $this->companyRepresentatives();

        return view('admin.targets.create', [
            'representatives' => $representatives,
            'targetTypes' => TargetType::cases(),
            'targetUnits' => TargetUnit::cases(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateTarget($request);
        $company = Auth::user()->company;

        $this->ensureRepresentativeBelongsToCompany($validated['user_id'], $company->id);

        Target::create([
            ...$this->mapTargetData($validated),
            'company_id' => $company->id,
            'assigned_by' => Auth::id(),
            'status' => Target::STATUS_ACTIVE,
        ]);

        return redirect()->route('admin.targets.index')
            ->with('success', 'Target assigned successfully.');
    }

    public function edit(Target $target)
    {
        $this->authorizeTarget($target);

        return view('admin.targets.edit', [
            'target' => $target->load('user'),
            'representatives' => $this->companyRepresentatives(),
            'targetTypes' => TargetType::cases(),
            'targetUnits' => TargetUnit::cases(),
        ]);
    }

    public function update(Request $request, Target $target)
    {
        $this->authorizeTarget($target);

        $validated = $this->validateTarget($request, $target);
        $this->ensureRepresentativeBelongsToCompany($validated['user_id'], $target->company_id);

        $target->update($this->mapTargetData($validated));

        return redirect()->route('admin.targets.index')
            ->with('success', 'Target updated successfully.');
    }

    public function destroy(Target $target)
    {
        $this->authorizeTarget($target);
        $target->delete();

        return redirect()->route('admin.targets.index')
            ->with('success', 'Target removed.');
    }

    public function updateProgress(Request $request, Target $target)
    {
        $this->authorizeTarget($target);

        $validated = $request->validate([
            'achieved_value' => ['required', 'numeric', 'min:0'],
            'status' => ['nullable', Rule::in([Target::STATUS_ACTIVE, Target::STATUS_COMPLETED, Target::STATUS_CANCELLED])],
        ]);

        $target->update([
            'achieved_value' => $validated['achieved_value'],
            'status' => $validated['status'] ?? (
                (float) $validated['achieved_value'] >= (float) $target->target_value
                    ? Target::STATUS_COMPLETED
                    : Target::STATUS_ACTIVE
            ),
        ]);

        return back()->with('success', 'Progress updated.');
    }

    protected function validateTarget(Request $request, ?Target $target = null): array
    {
        $rules = [
            'user_id' => ['required', 'exists:users,id'],
            'type' => ['required', Rule::enum(TargetType::class)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'target_value' => ['required', 'numeric', 'min:0.01'],
            'achieved_value' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['required', Rule::enum(TargetUnit::class)],
            'period_month' => ['nullable', 'date_format:Y-m'],
            'period_start' => ['nullable', 'date'],
            'period_end' => ['nullable', 'date', 'after_or_equal:period_start'],
            'product_name' => ['nullable', 'string', 'max:255'],
            'area_name' => ['nullable', 'string', 'max:255'],
        ];

        $validated = $request->validate($rules);

        $type = TargetType::from($validated['type']);

        if ($type === TargetType::Product && empty($validated['product_name'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'product_name' => 'Product name is required for product targets.',
            ]);
        }

        if ($type === TargetType::Area && empty($validated['area_name'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'area_name' => 'Area name is required for area-wise targets.',
            ]);
        }

        if (in_array($type, [TargetType::Monthly, TargetType::Sales], true) && empty($validated['period_month']) && empty($validated['period_start'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'period_month' => 'Select a month or date range for this target.',
            ]);
        }

        return $validated;
    }

    protected function mapTargetData(array $validated): array
    {
        $type = TargetType::from($validated['type']);
        $periodStart = $validated['period_start'] ?? null;
        $periodEnd = $validated['period_end'] ?? null;

        if (! empty($validated['period_month'])) {
            $month = \Carbon\Carbon::createFromFormat('Y-m', $validated['period_month']);
            $periodStart = $month->copy()->startOfMonth()->toDateString();
            $periodEnd = $month->copy()->endOfMonth()->toDateString();
        }

        return [
            'user_id' => $validated['user_id'],
            'type' => $type,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'product_name' => $type === TargetType::Product ? $validated['product_name'] : null,
            'area_name' => $type === TargetType::Area ? $validated['area_name'] : null,
            'target_value' => $validated['target_value'],
            'achieved_value' => $validated['achieved_value'] ?? 0,
            'unit' => $validated['unit'],
        ];
    }

    protected function companyRepresentatives()
    {
        return User::where('company_id', Auth::user()->company_id)
            ->where('role', UserRole::Representative)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    protected function ensureRepresentativeBelongsToCompany(int $userId, int $companyId): void
    {
        $exists = User::where('id', $userId)
            ->where('company_id', $companyId)
            ->where('role', UserRole::Representative)
            ->exists();

        if (! $exists) {
            abort(422, 'Invalid representative selected.');
        }
    }

    protected function authorizeTarget(Target $target): void
    {
        if ($target->company_id !== Auth::user()->company_id) {
            abort(403);
        }
    }
}
