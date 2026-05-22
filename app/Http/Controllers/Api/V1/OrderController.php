<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Customer;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(protected OrderService $orders) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $orders = Order::where('user_id', $user->id)
            ->with('customer:id,name,type')
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->latest('order_date')
            ->paginate(15);

        $summary = [
            'total' => Order::where('user_id', $user->id)->count(),
            'pending' => Order::where('user_id', $user->id)->where('status', OrderStatus::Pending)->count(),
            'confirmed_value' => (float) Order::where('user_id', $user->id)
                ->whereIn('status', [OrderStatus::Confirmed, OrderStatus::Delivered])
                ->sum('total_amount'),
        ];

        return OrderResource::collection($orders)->additional(['summary' => $summary]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'order_date' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'visit_id' => ['nullable', 'exists:visits,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99999'],
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);
        if ($customer->company_id !== $request->user()->company_id) {
            abort(403);
        }

        $order = $this->orders->create(
            $request->user(),
            $customer,
            $validated['items'],
            $validated['order_date'],
            $validated['notes'] ?? null,
            $validated['visit_id'] ?? null,
        );

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Order $order)
    {
        $this->authorizeOrder($order, $request);
        $order->load(['customer', 'items']);

        return new OrderResource($order);
    }

    public function cancel(Request $request, Order $order)
    {
        $this->authorizeOrder($order, $request);

        if (! $order->isPending()) {
            return response()->json(['message' => 'Only pending orders can be cancelled.'], 422);
        }

        $order->update(['status' => OrderStatus::Cancelled]);

        return response()->json(['message' => 'Order cancelled.']);
    }

    protected function authorizeOrder(Order $order, Request $request): void
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
