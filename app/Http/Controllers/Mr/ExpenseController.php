<?php

namespace App\Http\Controllers\Mr;

use App\Enums\ExpenseStatus;
use App\Enums\ExpenseType;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $expenses = Expense::where('user_id', Auth::id())
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->latest('expense_date')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'pending' => Expense::where('user_id', Auth::id())->where('status', ExpenseStatus::Pending)->sum('amount'),
            'approved' => Expense::where('user_id', Auth::id())->where('status', ExpenseStatus::Approved)->sum('amount'),
            'total_count' => Expense::where('user_id', Auth::id())->count(),
        ];

        return view('mr.expenses.index', [
            'expenses' => $expenses,
            'summary' => $summary,
            'expenseTypes' => ExpenseType::cases(),
        ]);
    }

    public function create()
    {
        return view('mr.expenses.create', [
            'expenseTypes' => ExpenseType::cases(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', Rule::enum(ExpenseType::class)],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'expense_date' => ['required', 'date', 'before_or_equal:today'],
            'description' => ['nullable', 'string', 'max:500'],
            'receipt' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $path = $request->file('receipt')->store('expense-receipts/'.Auth::id(), 'public');

        Expense::create([
            'company_id' => Auth::user()->company_id,
            'user_id' => Auth::id(),
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'currency' => Auth::user()->company?->currency ?? currency_code(),
            'expense_date' => $validated['expense_date'],
            'description' => $validated['description'] ?? null,
            'receipt_path' => $path,
            'receipt_original_name' => $request->file('receipt')->getClientOriginalName(),
            'status' => ExpenseStatus::Pending,
        ]);

        return redirect()->route('mr.expenses.index')
            ->with('success', 'Expense submitted for manager approval.');
    }

    public function show(Expense $expense)
    {
        $this->authorizeExpense($expense);

        return view('mr.expenses.show', compact('expense'));
    }

    public function destroy(Expense $expense)
    {
        $this->authorizeExpense($expense);

        if (! $expense->isPending()) {
            return back()->with('error', 'Only pending expenses can be deleted.');
        }

        if ($expense->receipt_path) {
            Storage::disk('public')->delete($expense->receipt_path);
        }

        $expense->delete();

        return redirect()->route('mr.expenses.index')
            ->with('success', 'Expense removed.');
    }

    protected function authorizeExpense(Expense $expense): void
    {
        if ($expense->user_id !== Auth::id()) {
            abort(403);
        }
    }
}
