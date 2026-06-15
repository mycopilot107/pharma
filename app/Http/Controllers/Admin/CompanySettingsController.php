<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Currency;
use Illuminate\Http\Request;

class CompanySettingsController extends Controller
{
    public function edit(Request $request)
    {
        $company    = $request->user()->company;
        $currencies = Currency::supported();

        return view('admin.settings.edit', compact('company', 'currencies'));
    }

    public function update(Request $request)
    {
        $company = $request->user()->company;

        $validated = $request->validate([
            'currency' => ['required', 'string', 'size:3', Currency::validationRule()],
        ]);

        $company->update(['currency' => strtoupper($validated['currency'])]);

        return redirect()->route('admin.settings.edit')
            ->with('success', 'Company settings updated successfully.');
    }
}
