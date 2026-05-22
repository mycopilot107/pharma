<?php

namespace App\Http\Controllers\Admin;

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
        $company = Auth::user()->company;

        $customers = Customer::where('company_id', $company->id)
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->when($request->search, fn ($q, $search) => $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            }))
            ->when($request->status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = [];
        foreach (CustomerType::cases() as $type) {
            $counts[$type->value] = Customer::where('company_id', $company->id)
                ->where('type', $type)
                ->where('is_active', true)
                ->count();
        }

        return view('admin.customers.index', [
            'customers' => $customers,
            'counts' => $counts,
            'customerTypes' => CustomerType::cases(),
        ]);
    }

    public function create()
    {
        return view('admin.customers.create', [
            'customerTypes' => CustomerType::cases(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateCustomer($request);

        Customer::create([
            ...$validated,
            'company_id' => Auth::user()->company_id,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer added to CRM.');
    }

    public function show(Customer $customer)
    {
        $this->authorizeCustomer($customer);
        $customer->load('creator');
        $tracking = $this->crmService->loadTrackingData($customer);

        return view('admin.customers.show', [
            'customer' => $customer,
            'crmRoutePrefix' => 'admin',
            ...$tracking,
        ]);
    }

    public function edit(Customer $customer)
    {
        $this->authorizeCustomer($customer);

        return view('admin.customers.edit', [
            'customer' => $customer,
            'customerTypes' => CustomerType::cases(),
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $this->authorizeCustomer($customer);
        $customer->update($this->validateCustomer($request));

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer updated.');
    }

    public function destroy(Customer $customer)
    {
        $this->authorizeCustomer($customer);
        $customer->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer removed.');
    }

    protected function validateCustomer(Request $request): array
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
            'notes' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        return $validated;
    }

    protected function authorizeCustomer(Customer $customer): void
    {
        if ($customer->company_id !== Auth::user()->company_id) {
            abort(403);
        }
    }
}
