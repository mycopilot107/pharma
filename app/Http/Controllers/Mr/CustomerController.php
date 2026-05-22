<?php

namespace App\Http\Controllers\Mr;

use App\Enums\CustomerType;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\CustomerCrmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function __construct(protected CustomerCrmService $crmService) {}

    public function index(Request $request)
    {
        $customers = Customer::where('company_id', Auth::user()->company_id)
            ->where('is_active', true)
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->when($request->search, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('mr.customers.index', [
            'customers' => $customers,
            'customerTypes' => CustomerType::cases(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', Rule::enum(CustomerType::class)],
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'specialty' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'pincode' => ['nullable', 'string', 'max:20'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        Customer::create([
            ...$validated,
            'company_id' => Auth::user()->company_id,
            'created_by' => Auth::id(),
        ]);

        return back()->with('success', 'Customer added to CRM.');
    }

    public function show(Customer $customer)
    {
        if ($customer->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $tracking = $this->crmService->loadTrackingData($customer, Auth::id());

        return view('mr.customers.show', [
            'customer' => $customer,
            'crmRoutePrefix' => 'mr',
            ...$tracking,
        ]);
    }
}
