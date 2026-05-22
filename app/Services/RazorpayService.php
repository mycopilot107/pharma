<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Payment;
use App\Models\Plan;
use Razorpay\Api\Api;

class RazorpayService
{
    protected Api $api;

    public function __construct()
    {
        $this->api = new Api(
            config('services.razorpay.key'),
            config('services.razorpay.secret')
        );
    }

    public function createOrder(Company $company, Plan $plan): Payment
    {
        $order = $this->api->order->create([
            'receipt' => 'company_'.$company->id.'_'.time(),
            'amount' => $plan->amountCents(),
            'currency' => $company->currency ?? config('currencies.default', 'USD'),
            'notes' => [
                'company_id' => (string) $company->id,
                'plan_id' => (string) $plan->id,
            ],
        ]);

        $payment = Payment::create([
            'company_id' => $company->id,
            'plan_id' => $plan->id,
            'razorpay_order_id' => $order['id'],
            'amount_usd' => $plan->price_usd,
            'currency' => $company->currency ?? config('currencies.default', 'USD'),
            'status' => 'created',
            'meta' => is_array($order) ? $order : (array) $order,
        ]);

        $company->update(['razorpay_order_id' => $order['id']]);

        return $payment;
    }

    public function verifySignature(string $orderId, string $paymentId, string $signature): bool
    {
        try {
            $this->api->utility->verifyPaymentSignature([
                'razorpay_order_id' => $orderId,
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature' => $signature,
            ]);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function key(): ?string
    {
        return config('services.razorpay.key');
    }

    public function isConfigured(): bool
    {
        return filled(config('services.razorpay.key'))
            && filled(config('services.razorpay.secret'));
    }
}
