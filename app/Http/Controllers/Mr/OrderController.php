<?php

namespace App\Http\Controllers\Mr;

use App\Enums\CustomerType;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function __construct(protected OrderService $orders) {}

    public function index(Request $request)
    {
        $orders = Order::where('user_id', Auth::id())
            ->with('customer:id,name,type')
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->latest('order_date')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => Order::where('user_id', Auth::id())->count(),
            'pending' => Order::where('user_id', Auth::id())->where('status', OrderStatus::Pending)->count(),
            'value' => (float) Order::where('user_id', Auth::id())
                ->whereIn('status', [OrderStatus::Confirmed, OrderStatus::Delivered])
                ->sum('total_amount'),
        ];

        return view('mr.orders.index', compact('orders', 'summary'));
    }

    public function create()
    {
        $companyId = Auth::user()->company_id;

        $customers = Customer::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'city']);

        $products = Product::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('mr.orders.create', [
            'customers' => $customers,
            'products' => $products,
            'customerTypes' => CustomerType::cases(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'order_date' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99999'],
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);
        if ($customer->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $order = $this->orders->create(
            Auth::user(),
            $customer,
            $validated['items'],
            $validated['order_date'],
            $validated['notes'] ?? null,
        );

        return redirect()->route('mr.orders.show', $order)
            ->with('success', 'Order submitted for approval.');
    }

    public function show(Order $order)
    {
        $this->authorizeOrder($order);
        $order->load(['customer', 'items.product', 'reviewer']);

        return view('mr.orders.show', compact('order'));
    }

    public function cancel(Order $order)
    {
        $this->authorizeOrder($order);

        if (! $order->isPending()) {
            return back()->with('error', 'Only pending orders can be cancelled.');
        }

        $order->update(['status' => OrderStatus::Cancelled]);

        return redirect()->route('mr.orders.index')
            ->with('success', 'Order cancelled.');
    }

    protected function authorizeOrder(Order $order): void
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }
    }
}
