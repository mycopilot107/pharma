<?php

namespace App\Http\Controllers\Mr;

use App\Enums\FollowUpStatus;
use App\Enums\PurchaseFrequency;
use App\Http\Controllers\Concerns\AuthorizesCompanyCustomer;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerFollowUp;
use App\Models\CustomerPrescription;
use App\Models\CustomerPurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CustomerCrmController extends Controller
{
    use AuthorizesCompanyCustomer;

    public function storeFollowUp(Request $request, Customer $customer)
    {
        $this->authorizeCompanyCustomer($customer);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'due_at' => ['required', 'date'],
            'visit_id' => ['nullable', 'exists:visits,id'],
        ]);

        if (! empty($validated['visit_id'])) {
            $this->assertVisitBelongsToCustomer($validated['visit_id'], $customer);
        }

        CustomerFollowUp::create([
            ...$validated,
            'company_id' => $customer->company_id,
            'customer_id' => $customer->id,
            'user_id' => Auth::id(),
            'status' => FollowUpStatus::Pending,
        ]);

        return back()->with('success', 'Follow-up scheduled.');
    }

    public function completeFollowUp(CustomerFollowUp $followUp)
    {
        $this->authorizeFollowUp($followUp);

        $followUp->update([
            'status' => FollowUpStatus::Completed,
            'completed_at' => now(),
        ]);

        return back()->with('success', 'Follow-up marked complete.');
    }

    public function storePrescription(Request $request, Customer $customer)
    {
        $this->authorizeCompanyCustomer($customer);

        $validated = $request->validate([
            'product_name' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'strength' => ['nullable', 'string', 'max:100'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit' => ['nullable', 'string', 'max:50'],
            'prescribed_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'visit_id' => ['nullable', 'exists:visits,id'],
        ]);

        if (! empty($validated['visit_id'])) {
            $this->assertVisitBelongsToCustomer($validated['visit_id'], $customer);
        }

        CustomerPrescription::create([
            ...$validated,
            'company_id' => $customer->company_id,
            'customer_id' => $customer->id,
            'user_id' => Auth::id(),
        ]);

        return back()->with('success', 'Prescription recorded.');
    }

    public function storePurchase(Request $request, Customer $customer)
    {
        $this->authorizeCompanyCustomer($customer);

        $validated = $request->validate([
            'product_name' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit' => ['nullable', 'string', 'max:50'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'purchase_frequency' => ['nullable', Rule::enum(PurchaseFrequency::class)],
            'purchased_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'visit_id' => ['nullable', 'exists:visits,id'],
        ]);

        if (! empty($validated['visit_id'])) {
            $this->assertVisitBelongsToCustomer($validated['visit_id'], $customer);
        }

        CustomerPurchase::create([
            ...$validated,
            'company_id' => $customer->company_id,
            'customer_id' => $customer->id,
            'user_id' => Auth::id(),
        ]);

        return back()->with('success', 'Purchase recorded.');
    }

    protected function authorizeFollowUp(CustomerFollowUp $followUp): void
    {
        if ($followUp->company_id !== Auth::user()->company_id || $followUp->user_id !== Auth::id()) {
            abort(403);
        }
    }

    protected function assertVisitBelongsToCustomer(int $visitId, Customer $customer): void
    {
        $exists = $customer->visits()
            ->where('id', $visitId)
            ->where('user_id', Auth::id())
            ->exists();

        if (! $exists) {
            abort(422, 'Invalid visit for this customer.');
        }
    }
}
