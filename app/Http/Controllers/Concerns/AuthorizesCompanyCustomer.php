<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Customer;
use Illuminate\Support\Facades\Auth;

trait AuthorizesCompanyCustomer
{
    protected function authorizeCompanyCustomer(Customer $customer): void
    {
        if ($customer->company_id !== Auth::user()->company_id) {
            abort(403);
        }
    }
}
