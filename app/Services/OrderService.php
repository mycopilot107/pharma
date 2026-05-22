<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function create(
        User $user,
        Customer $customer,
        array $lineItems,
        string $orderDate,
        ?string $notes = null,
        ?int $visitId = null,
    ): Order {
        if ($customer->company_id !== $user->company_id) {
            throw ValidationException::withMessages([
                'customer_id' => ['Invalid customer for your company.'],
            ]);
        }

        if (empty($lineItems)) {
            throw ValidationException::withMessages([
                'items' => ['Add at least one product to the order.'],
            ]);
        }

        return DB::transaction(function () use ($user, $customer, $lineItems, $orderDate, $notes, $visitId) {
            $currency = $user->company?->currency ?? \App\Support\Currency::code();
            $total = 0;
            $resolvedItems = [];

            foreach ($lineItems as $row) {
                $product = Product::where('company_id', $user->company_id)
                    ->where('id', $row['product_id'])
                    ->where('is_active', true)
                    ->first();

                if (! $product) {
                    throw ValidationException::withMessages([
                        'items' => ['One or more products are invalid or inactive.'],
                    ]);
                }

                $qty = (int) $row['quantity'];
                if ($qty < 1) {
                    throw ValidationException::withMessages([
                        'items' => ['Quantity must be at least 1 for each product.'],
                    ]);
                }

                $unitPrice = isset($row['unit_price'])
                    ? (float) $row['unit_price']
                    : (float) $product->unit_price;

                $lineTotal = round($unitPrice * $qty, 2);
                $total += $lineTotal;

                $resolvedItems[] = [
                    'product' => $product,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ];
            }

            $order = Order::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'customer_id' => $customer->id,
                'visit_id' => $visitId,
                'order_number' => $this->generateOrderNumber($user->company_id),
                'order_date' => $orderDate,
                'status' => OrderStatus::Pending,
                'total_amount' => round($total, 2),
                'currency' => $currency,
                'notes' => $notes,
            ]);

            foreach ($resolvedItems as $item) {
                $product = $item['product'];
                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['line_total'],
                ]);
            }

            return $order->load(['items.product', 'customer', 'user']);
        });
    }

    protected function generateOrderNumber(int $companyId): string
    {
        $prefix = 'ORD-'.str_pad((string) $companyId, 4, '0', STR_PAD_LEFT).'-'.now()->format('Ymd');
        $last = Order::where('company_id', $companyId)
            ->where('order_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('order_number');

        $seq = 1;
        if ($last && preg_match('/-(\d+)$/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return $prefix.'-'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
