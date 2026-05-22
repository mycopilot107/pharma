<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\Payment;
use App\Services\RazorpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function __construct(protected RazorpayService $razorpay) {}

    public function checkout(Company $company)
    {
        if ($company->status === Company::STATUS_ACTIVE) {
            return redirect()->route('login')
                ->with('success', 'This company is already active. Please sign in.');
        }

        $payment = $company->payments()
            ->where('status', 'created')
            ->latest()
            ->first();

        if (! $payment) {
            return redirect()->route('companies.register')
                ->with('error', 'No pending payment found. Please register again.');
        }

        $admin = $company->users()->where('role', UserRole::CompanyAdmin)->first();

        return view('payment.checkout', [
            'company' => $company,
            'plan' => $company->plan,
            'payment' => $payment,
            'razorpayKey' => $this->razorpay->key(),
            'adminEmail' => $admin?->email,
            'adminPhone' => $company->phone,
        ]);
    }

    public function verify(Request $request)
    {
        $validated = $request->validate([
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        if (! $this->razorpay->verifySignature(
            $validated['razorpay_order_id'],
            $validated['razorpay_payment_id'],
            $validated['razorpay_signature']
        )) {
            return response()->json(['success' => false, 'message' => 'Payment verification failed.'], 422);
        }

        $payment = Payment::where('razorpay_order_id', $validated['razorpay_order_id'])->firstOrFail();
        $company = $payment->company;

        DB::transaction(function () use ($payment, $company, $validated) {
            $payment->update([
                'razorpay_payment_id' => $validated['razorpay_payment_id'],
                'razorpay_signature' => $validated['razorpay_signature'],
                'status' => 'paid',
            ]);

            $days = (int) config('pharma.subscription_days', 30);

            $company->update([
                'status' => Company::STATUS_ACTIVE,
                'razorpay_payment_id' => $validated['razorpay_payment_id'],
                'subscribed_at' => now(),
                'subscription_ends_at' => now()->addDays($days),
            ]);

            $company->users()->update(['is_active' => true]);
        });

        $admin = $company->users()->where('role', UserRole::CompanyAdmin)->first();
        if ($admin) {
            Auth::login($admin);
        }

        return response()->json([
            'success' => true,
            'redirect' => route('dashboard'),
        ]);
    }
}
