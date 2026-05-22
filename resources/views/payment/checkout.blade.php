@extends('layouts.app')

@section('title', 'Complete Payment')

@section('content')
<div class="mx-auto max-w-lg text-center">
    <h1 class="text-2xl font-bold text-slate-900">Complete your subscription</h1>
    <p class="mt-2 text-slate-600">{{ $company->name }}</p>

    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 text-left shadow-sm">
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between">
                <dt class="text-slate-500">Plan</dt>
                <dd class="font-medium">{{ $plan->user_limit }} medical representatives</dd>
            </div>
            <div class="flex justify-between border-t border-slate-100 pt-3">
                <dt class="text-slate-500">Amount due</dt>
                <dd class="text-xl font-bold text-teal-700">{{ $plan->formattedPrice() }}</dd>
            </div>
        </dl>
    </div>

    <button id="rzp-button" type="button" class="mt-8 w-full rounded-xl bg-teal-600 py-3.5 text-lg font-semibold text-white hover:bg-teal-700">
        Pay with Razorpay
    </button>
    <p class="mt-4 text-xs text-slate-500">Secure payment powered by Razorpay</p>
</div>
@endsection

@push('head')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.getElementById('rzp-button').onclick = function () {
    const options = {
        key: @json($razorpayKey),
        amount: {{ $plan->amountCents() }},
        currency: @json($company->currency ?? config('currencies.default', 'USD')),
        name: @json(config('pharma.app_name')),
        description: @json($plan->name),
        order_id: @json($payment->razorpay_order_id),
        prefill: {
            name: @json($company->name),
            email: @json($adminEmail),
            contact: @json($adminPhone ?? ''),
        },
        theme: { color: '#0d9488' },
        handler: function (response) {
            fetch(@json(route('payment.verify')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    razorpay_order_id: response.razorpay_order_id,
                    razorpay_payment_id: response.razorpay_payment_id,
                    razorpay_signature: response.razorpay_signature,
                }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    alert(data.message || 'Payment verification failed');
                }
            })
            .catch(() => alert('Payment verification failed. Please contact support.'));
        },
    };
    new Razorpay(options).open();
};
</script>
@endpush
