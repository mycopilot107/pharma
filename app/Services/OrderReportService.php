<?php

namespace App\Services;

use App\Enums\CustomerType;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderReportService
{
    public function summary(int $companyId, array $filters = []): array
    {
        $query = $this->filteredOrders($companyId, $filters);

        $confirmed = (clone $query)->where('status', OrderStatus::Confirmed);
        $delivered = (clone $query)->where('status', OrderStatus::Delivered);
        $counted = (clone $query)->whereIn('status', [OrderStatus::Confirmed, OrderStatus::Delivered]);

        return [
            'total_orders' => (clone $query)->count(),
            'pending' => (clone $query)->where('status', OrderStatus::Pending)->count(),
            'confirmed' => (clone $confirmed)->count(),
            'delivered' => (clone $delivered)->count(),
            'revenue' => (float) (clone $counted)->sum('total_amount'),
            'pending_value' => (float) (clone $query)->where('status', OrderStatus::Pending)->sum('total_amount'),
        ];
    }

    public function topProducts(int $companyId, array $filters = [], int $limit = 10): array
    {
        $orderIds = $this->filteredOrders($companyId, $filters)->pluck('id');

        if ($orderIds->isEmpty()) {
            return [];
        }

        return OrderItem::query()
            ->select(
                'product_id',
                'product_name',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(line_total) as total_value'),
            )
            ->whereIn('order_id', $orderIds)
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_value')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'product_id' => $row->product_id,
                'product_name' => $row->product_name,
                'total_qty' => (int) $row->total_qty,
                'total_value' => (float) $row->total_value,
            ])
            ->all();
    }

    public function byRepresentative(int $companyId, array $filters = []): array
    {
        return $this->filteredOrders($companyId, $filters)
            ->select('user_id', DB::raw('COUNT(*) as order_count'), DB::raw('SUM(total_amount) as total_value'))
            ->whereIn('status', [OrderStatus::Confirmed, OrderStatus::Delivered, OrderStatus::Pending])
            ->groupBy('user_id')
            ->orderByDesc('total_value')
            ->get()
            ->map(function ($row) use ($companyId) {
                $user = User::where('company_id', $companyId)->find($row->user_id);

                return [
                    'user_id' => $row->user_id,
                    'name' => $user?->name ?? 'Unknown',
                    'order_count' => (int) $row->order_count,
                    'total_value' => (float) $row->total_value,
                ];
            })
            ->all();
    }

    public function byCustomerType(int $companyId, array $filters = []): array
    {
        $orderIds = $this->filteredOrders($companyId, $filters)->pluck('id');

        if ($orderIds->isEmpty()) {
            return [];
        }

        $rows = Order::query()
            ->join('customers', 'orders.customer_id', '=', 'customers.id')
            ->whereIn('orders.id', $orderIds)
            ->select('customers.type', DB::raw('COUNT(orders.id) as order_count'), DB::raw('SUM(orders.total_amount) as total_value'))
            ->groupBy('customers.type')
            ->get();

        return $rows->map(function ($row) {
            $type = CustomerType::tryFrom($row->type);

            return [
                'type' => $row->type,
                'label' => $type?->label() ?? $row->type,
                'icon' => $type?->icon() ?? '📋',
                'order_count' => (int) $row->order_count,
                'total_value' => (float) $row->total_value,
            ];
        })->all();
    }

    protected function filteredOrders(int $companyId, array $filters)
    {
        return Order::where('company_id', $companyId)
            ->when($filters['user_id'] ?? null, fn ($q, $id) => $q->where('user_id', $id))
            ->when($filters['customer_id'] ?? null, fn ($q, $id) => $q->where('customer_id', $id))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['date_from'] ?? null, fn ($q, $d) => $q->whereDate('order_date', '>=', $d))
            ->when($filters['date_to'] ?? null, fn ($q, $d) => $q->whereDate('order_date', '<=', $d))
            ->when($filters['customer_type'] ?? null, function ($q, $type) {
                $q->whereHas('customer', fn ($c) => $c->where('type', $type));
            });
    }
}
