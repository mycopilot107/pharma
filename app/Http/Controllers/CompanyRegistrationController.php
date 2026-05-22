<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\Plan;
use App\Models\User;
use App\Services\RazorpayService;
use App\Support\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CompanyRegistrationController extends Controller
{
    public function __construct(protected RazorpayService $razorpay) {}

    public function create()
    {
        $plans = Plan::where('is_active', true)->orderBy('user_limit')->get();
        $currencies = Currency::supported();

        return view('companies.register', compact('plans', 'currencies'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'company_email' => strtolower(trim((string) $request->input('company_email'))),
            'admin_email' => strtolower(trim((string) $request->input('admin_email'))),
            'company_phone' => preg_replace('/\D/', '', (string) $request->input('company_phone')),
        ]);

        $validated = $request->validate([
            'company_name' => ['required', 'string', 'min:2', 'max:255'],
            'company_email' => [
                'required',
                'string',
                'email:filter,rfc',
                'max:255',
                'unique:companies,email',
            ],
            'company_phone' => ['required', 'digits:10', 'regex:/^[6-9][0-9]{9}$/'],
            'address' => ['nullable', 'string', 'max:1000'],
            'plan_id' => ['required', Rule::exists('plans', 'id')->where('is_active', true)],
            'currency' => ['required', 'string', 'size:3', Currency::validationRule()],
            'admin_name' => ['required', 'string', 'min:2', 'max:255'],
            'admin_email' => [
                'required',
                'string',
                'email:filter,rfc',
                'max:255',
                'unique:users,email',
                'different:company_email',
            ],
            'admin_password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'company_name.required' => 'Company name is required.',
            'company_name.min' => 'Company name must be at least 2 characters.',
            'company_email.required' => 'Company email is required.',
            'company_email.email' => 'Enter a valid company email address.',
            'company_email.unique' => 'This company email is already registered.',
            'company_phone.required' => 'Mobile number is required.',
            'company_phone.digits' => 'Mobile number must be exactly 10 digits.',
            'company_phone.regex' => 'Enter a valid 10-digit mobile number (starts with 6–9).',
            'admin_email.required' => 'Admin email is required.',
            'admin_email.email' => 'Enter a valid admin email address.',
            'admin_email.unique' => 'This admin email is already in use.',
            'admin_email.different' => 'Admin email must be different from the company email.',
            'admin_password.confirmed' => 'Password confirmation does not match.',
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);
        $currency = strtoupper($validated['currency']);

        if ($plan->isFree()) {
            $admin = DB::transaction(function () use ($validated, $plan, $currency) {
                $company = Company::create([
                    'name' => $validated['company_name'],
                    'email' => $validated['company_email'],
                    'phone' => $validated['company_phone'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'plan_id' => $plan->id,
                    'user_limit' => $plan->user_limit,
                    'currency' => $currency,
                    'amount_paid_usd' => 0,
                    'status' => Company::STATUS_ACTIVE,
                    'subscribed_at' => now(),
                    'subscription_ends_at' => null,
                ]);

                return User::create([
                    'company_id' => $company->id,
                    'name' => $validated['admin_name'],
                    'email' => $validated['admin_email'],
                    'password' => $validated['admin_password'],
                    'role' => UserRole::CompanyAdmin,
                    'is_active' => true,
                ]);
            });

            Auth::login($admin);

            return redirect()->route('dashboard')
                ->with('success', 'Your free account is ready. You can add 1 medical representative.');
        }

        if (! $this->razorpay->isConfigured()) {
            return back()
                ->withInput()
                ->with('error', 'Payment gateway is not configured. Add RAZORPAY_KEY and RAZORPAY_SECRET to .env, or choose the Free plan.');
        }

        $company = DB::transaction(function () use ($validated, $plan, $currency) {
            $company = Company::create([
                'name' => $validated['company_name'],
                'email' => $validated['company_email'],
                'phone' => $validated['company_phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'plan_id' => $plan->id,
                'user_limit' => $plan->user_limit,
                'currency' => $currency,
                'amount_paid_usd' => $plan->price_usd,
                'status' => Company::STATUS_PENDING,
            ]);

            User::create([
                'company_id' => $company->id,
                'name' => $validated['admin_name'],
                'email' => $validated['admin_email'],
                'password' => $validated['admin_password'],
                'role' => UserRole::CompanyAdmin,
                'is_active' => false,
            ]);

            return $company;
        });

        $this->razorpay->createOrder($company, $plan);

        return redirect()->route('payment.checkout', $company)
            ->with('success', 'Company created. Complete payment to activate your account.');
    }

    public function planPrice(Request $request, Plan $plan)
    {
        $currency = $request->query('currency', config('currencies.default', 'USD'));
        if (! Currency::isSupported($currency)) {
            $currency = config('currencies.default', 'USD');
        }

        return response()->json([
            'user_limit' => $plan->user_limit,
            'price' => (float) $plan->price_usd,
            'formatted_price' => $plan->formattedPrice($currency),
            'price_per_user' => (float) config('pharma.price_per_user_usd', 3),
            'currency' => strtoupper($currency),
            'currency_symbol' => Currency::symbol($currency),
        ]);
    }
}
