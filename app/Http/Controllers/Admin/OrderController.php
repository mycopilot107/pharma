<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $orders = Order::where('company_id', $companyId)
            ->with(['user:id,name', 'customer:id,name,type'])
            ->when($request->user_id, fn ($q, $id) => $q->where('user_id', $id))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->date_from, fn ($q, $d) => $q->whereDate('order_date', '>=', $d))
            ->when($request->date_to, fn ($q, $d) => $q->whereDate('order_date', '<=', $d))
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest('order_date')
            ->paginate(20)
            ->withQueryString();

        $representatives = User::where('company_id', $companyId)
            ->where('role', 'representative')
            ->orderBy('name')
            ->get(['id', 'name']);

        $base = Order::where('company_id', $companyId);
        $summary = [
            'pending' => (clone $base)->where('status', OrderStatus::Pending)->count(),
            'confirmed' => (clone $base)->where('status', OrderStatus::Confirmed)->count(),
            'revenue' => (float) (clone $base)->whereIn('status', [OrderStatus::Confirmed, OrderStatus::Delivered])->sum('total_amount'),
        ];

        return view('admin.orders.index', [
            'orders' => $orders,
            'representatives' => $representatives,
            'summary' => $summary,
        ]);
    }

    public function show(Order $order)
    {
        $this->authorizeOrder($order);
        $order->load(['user', 'customer', 'items.product', 'reviewer', 'visit']);

        return view('admin.orders.show', compact('order'));
    }

    public function confirm(Request $request, Order $order)
    {
        $this->authorizeOrder($order);

        if (! $order->isPending()) {
            return back()->with('error', 'This order was already processed.');
        }

        $validated = $request->validate([
            'manager_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $order->update([
            'status' => OrderStatus::Confirmed,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'manager_notes' => $validated['manager_notes'] ?? null,
        ]);

        return back()->with('success', 'Order confirmed.');
    }

    public function deliver(Order $order)
    {
        $this->authorizeOrder($order);

        if ($order->status !== OrderStatus::Confirmed) {
            return back()->with('error', 'Only confirmed orders can be marked delivered.');
        }

        $order->update(['status' => OrderStatus::Delivered]);

        return back()->with('success', 'Order marked as delivered.');
    }

    public function cancel(Request $request, Order $order)
    {
        $this->authorizeOrder($order);

        if (in_array($order->status, [OrderStatus::Delivered, OrderStatus::Cancelled], true)) {
            return back()->with('error', 'This order cannot be cancelled.');
        }

        $validated = $request->validate([
            'manager_notes' => ['required', 'string', 'max:1000'],
        ]);

        $order->update([
            'status' => OrderStatus::Cancelled,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'manager_notes' => $validated['manager_notes'],
        ]);

        return back()->with('success', 'Order cancelled.');
    }

    protected function authorizeOrder(Order $order): void
    {
        if ($order->company_id !== Auth::user()->company_id) {
            abort(403);
        }
    }
}
