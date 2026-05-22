<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ExpenseStatus;
use App\Enums\ExpenseType;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $expenses = Expense::where('company_id', $companyId)
            ->with(['user:id,name', 'reviewer:id,name'])
            ->when($request->user_id, fn ($q, $id) => $q->where('user_id', $id))
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->date_from, fn ($q, $d) => $q->whereDate('expense_date', '>=', $d))
            ->when($request->date_to, fn ($q, $d) => $q->whereDate('expense_date', '<=', $d))
            ->latest('expense_date')
            ->paginate(20)
            ->withQueryString();

        $representatives = User::where('company_id', $companyId)
            ->where('role', 'representative')
            ->orderBy('name')
            ->get(['id', 'name']);

        $baseQuery = Expense::where('company_id', $companyId);
        $summary = [
            'pending_count' => (clone $baseQuery)->where('status', ExpenseStatus::Pending)->count(),
            'pending_amount' => (clone $baseQuery)->where('status', ExpenseStatus::Pending)->sum('amount'),
            'approved_amount' => (clone $baseQuery)->where('status', ExpenseStatus::Approved)->sum('amount'),
            'fuel' => (clone $baseQuery)->where('type', ExpenseType::Fuel)->where('status', ExpenseStatus::Pending)->count(),
            'hotel' => (clone $baseQuery)->where('type', ExpenseType::Hotel)->where('status', ExpenseStatus::Pending)->count(),
            'food' => (clone $baseQuery)->where('type', ExpenseType::Food)->where('status', ExpenseStatus::Pending)->count(),
        ];

        return view('admin.expenses.index', [
            'expenses' => $expenses,
            'representatives' => $representatives,
            'summary' => $summary,
            'expenseTypes' => ExpenseType::cases(),
        ]);
    }

    public function show(Expense $expense)
    {
        $this->authorizeExpense($expense);
        $expense->load(['user', 'reviewer']);

        return view('admin.expenses.show', compact('expense'));
    }

    public function approve(Request $request, Expense $expense)
    {
        $this->authorizeExpense($expense);

        if (! $expense->isPending()) {
            return back()->with('error', 'This expense was already reviewed.');
        }

        $validated = $request->validate([
            'manager_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $expense->update([
            'status' => ExpenseStatus::Approved,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'manager_notes' => $validated['manager_notes'] ?? null,
        ]);

        return back()->with('success', 'Expense approved.');
    }

    public function reject(Request $request, Expense $expense)
    {
        $this->authorizeExpense($expense);

        if (! $expense->isPending()) {
            return back()->with('error', 'This expense was already reviewed.');
        }

        $validated = $request->validate([
            'manager_notes' => ['required', 'string', 'max:1000'],
        ]);

        $expense->update([
            'status' => ExpenseStatus::Rejected,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'manager_notes' => $validated['manager_notes'],
        ]);

        return back()->with('success', 'Expense rejected.');
    }

    protected function authorizeExpense(Expense $expense): void
    {
        if ($expense->company_id !== Auth::user()->company_id) {
            abort(403);
        }
    }
}
