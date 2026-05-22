<?php

namespace App\Services;

use App\Enums\FollowUpStatus;
use App\Models\Customer;
use App\Models\CustomerFollowUp;
use Illuminate\Support\Collection;

class CustomerCrmService
{
    public function loadTrackingData(Customer $customer, ?int $userId = null): array
    {
        $meetingsQuery = $customer->visits()->with('user')->latest();
        if ($userId) {
            $meetingsQuery->where('user_id', $userId);
        }

        $followUpsQuery = $customer->followUps()->with('user')->orderBy('due_at');
        if ($userId) {
            $followUpsQuery->where('user_id', $userId);
        }

        $prescriptionsQuery = $customer->prescriptions()->with('user')->latest('prescribed_at');
        if ($userId) {
            $prescriptionsQuery->where('user_id', $userId);
        }

        $purchasesQuery = $customer->purchases()->with('user')->latest('purchased_at');
        if ($userId) {
            $purchasesQuery->where('user_id', $userId);
        }

        $purchases = $purchasesQuery->get();

        return [
            'meetings' => $meetingsQuery->take(20)->get(),
            'followUps' => $followUpsQuery->get(),
            'prescriptions' => $prescriptionsQuery->get(),
            'purchases' => $purchases,
            'purchasePatterns' => $this->analyzePurchasePatterns($purchases),
        ];
    }

    public function analyzePurchasePatterns(Collection $purchases): array
    {
        if ($purchases->isEmpty()) {
            return [
                'total_records' => 0,
                'total_value' => 0,
                'top_products' => [],
                'frequencies' => [],
            ];
        }

        $byProduct = $purchases->groupBy(fn ($p) => strtolower($p->product_name));

        $topProducts = $byProduct->map(function ($items, $name) {
            return [
                'product' => $items->first()->product_name,
                'count' => $items->count(),
                'total_qty' => $items->sum('quantity'),
                'total_amount' => $items->sum('amount'),
                'last_purchased' => $items->max('purchased_at'),
                'frequency' => $items->pluck('purchase_frequency')->filter()->countBy(fn ($f) => $f->value)->sortDesc()->keys()->first(),
            ];
        })->sortByDesc('count')->values()->take(5);

        $frequencies = $purchases->pluck('purchase_frequency')->filter()->countBy();

        return [
            'total_records' => $purchases->count(),
            'total_value' => $purchases->sum('amount'),
            'top_products' => $topProducts,
            'frequencies' => $frequencies,
        ];
    }

    public function pendingFollowUpsCount(int $companyId, ?int $userId = null): int
    {
        $query = CustomerFollowUp::where('company_id', $companyId)
            ->where('status', FollowUpStatus::Pending);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->count();
    }

    public function overdueFollowUpsCount(int $companyId, ?int $userId = null): int
    {
        $query = CustomerFollowUp::where('company_id', $companyId)
            ->where('status', FollowUpStatus::Pending)
            ->where('due_at', '<', now());

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->count();
    }
}
