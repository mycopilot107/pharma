<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ExpenseStatus;
use App\Enums\ExpenseType;
use App\Http\Controllers\Controller;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $expenses = Expense::where('user_id', $user->id)
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->latest('expense_date')
            ->paginate(15);

        $summary = [
            'pending' => (float) Expense::where('user_id', $user->id)->where('status', ExpenseStatus::Pending)->sum('amount'),
            'approved' => (float) Expense::where('user_id', $user->id)->where('status', ExpenseStatus::Approved)->sum('amount'),
            'total_count' => Expense::where('user_id', $user->id)->count(),
        ];

        return ExpenseResource::collection($expenses)->additional(['summary' => $summary]);
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

        $path = $request->file('receipt')->store('expense-receipts/'.$request->user()->id, 'public');

        $expense = Expense::create([
            'company_id' => $request->user()->company_id,
            'user_id' => $request->user()->id,
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'currency' => $request->user()->company?->currency ?? currency_code(),
            'expense_date' => $validated['expense_date'],
            'description' => $validated['description'] ?? null,
            'receipt_path' => $path,
            'receipt_original_name' => $request->file('receipt')->getClientOriginalName(),
            'status' => ExpenseStatus::Pending,
        ]);

        return (new ExpenseResource($expense))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Expense $expense)
    {
        $this->authorizeExpense($expense, $request);

        return new ExpenseResource($expense);
    }

    public function destroy(Request $request, Expense $expense)
    {
        $this->authorizeExpense($expense, $request);

        if (! $expense->isPending()) {
            return response()->json(['message' => 'Only pending expenses can be deleted.'], 422);
        }

        if ($expense->receipt_path) {
            Storage::disk('public')->delete($expense->receipt_path);
        }

        $expense->delete();

        return response()->json(['message' => 'Expense removed.']);
    }

    protected function authorizeExpense(Expense $expense, Request $request): void
    {
        if ($expense->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
