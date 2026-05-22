<?php

namespace App\Models;

use App\Enums\ExpenseStatus;
use App\Enums\ExpenseType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Expense extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'type',
        'amount',
        'currency',
        'expense_date',
        'description',
        'receipt_path',
        'receipt_original_name',
        'status',
        'reviewed_by',
        'reviewed_at',
        'manager_notes',
    ];

    protected function casts(): array
    {
        return [
            'type' => ExpenseType::class,
            'status' => ExpenseStatus::class,
            'amount' => 'decimal:2',
            'expense_date' => 'date',
            'reviewed_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === ExpenseStatus::Pending;
    }

    public function formattedAmount(): string
    {
        return \App\Support\Currency::format($this->amount, $this->currency);
    }

    public function receiptUrl(): ?string
    {
        if (! $this->receipt_path) {
            return null;
        }

        return Storage::disk('public')->url($this->receipt_path);
    }

    public function isImageReceipt(): bool
    {
        if (! $this->receipt_original_name) {
            return false;
        }

        $ext = strtolower(pathinfo($this->receipt_original_name, PATHINFO_EXTENSION));

        return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
    }
}
