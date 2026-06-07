@extends('layouts.app')

@section('title', 'Complete Payment')

@section('content')
<div class="mx-auto max-w-lg">

    {{-- Step progress --}}
    <div class="mb-8 flex items-center gap-3">
        <div class="flex items-center gap-2 opacity-60">
            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-teal-600 text-xs font-bold text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </span>
            <span class="text-sm font-medium text-slate-500">Company details</span>
        </div>
        <div class="h-px flex-1 bg-teal-300"></div>
        <div class="flex items-center gap-2">
            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-teal-600 text-xs font-bold text-white">2</span>
            <span class="text-sm font-semibold text-teal-700">Payment</span>
        </div>
    </div>

    @php
        $billingCycle    = $payment->meta['billing_cycle'] ?? 'monthly';
        $formattedAmount = $billingCycle === 'yearly'
            ? $plan->formattedYearlyPrice($company->currency)
            : $plan->formattedPrice($company->currency);
        $cycleLabel      = $billingCycle === 'yearly'
            ? 'Yearly (save ' . ((int) round(config('pharma.yearly_discount', 0.10) * 100)) . '%)'
            : 'Monthly';
        $discountPct     = (int) round(config('pharma.yearly_discount', 0.10) * 100);
    @endphp

    <h1 class="text-2xl font-bold text-slate-900">Complete your subscription</h1>
    <p class="mt-1 text-slate-500">{{ $company->name }}</p>

    {{-- Order summary card --}}
    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 bg-slate-50/70 px-6 py-4">
            <h2 class="text-sm font-semibold text-slate-700">Order summary</h2>
        </div>
        <div class="px-6 py-5">
            <dl class="space-y-3.5 text-sm">
                <div class="flex items-center justify-between">
                    <dt class="text-slate-500">Plan</dt>
                    <dd class="font-semibold text-slate-800">{{ $plan->user_limit }} medical {{ $plan->user_limit === 1 ? 'representative' : 'representatives' }}</dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt class="text-slate-500">Billing cycle</dt>
                    <dd class="flex items-center gap-1.5 font-semibold text-slate-800">
                        {{ $cycleLabel }}
                        @if ($billingCycle === 'yearly')
                            <span class="rounded-full bg-teal-100 px-2 py-0.5 text-xs font-bold text-teal-700">
                                −{{ $discountPct }}%
                            </span>
                        @endif
                    </dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt class="text-slate-500">Company</dt>
                    <dd class="font-medium text-slate-800">{{ $company->name }}</dd>
                </div>
                <div class="flex items-center justify-between border-t border-slate-100 pt-4">
                    <dt class="font-semibold text-slate-700">Amount due</dt>
                    <dd class="text-2xl font-bold text-teal-700">{{ $formattedAmount }}</dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- What's included --}}
    <div class="mt-4 rounded-xl border border-slate-100 bg-slate-50 px-5 py-4">
        <p class="mb-2.5 text-xs font-semibold uppercase tracking-wider text-slate-500">Included in your plan</p>
        <ul class="space-y-1.5 text-sm text-slate-600">
            <li class="flex items-center gap-2">
                <span class="text-teal-500">✓</span> GPS visit tracking for all your MRs
            </li>
            <li class="flex items-center gap-2">
                <span class="text-teal-500">✓</span> Admin web dashboard + mobile app
            </li>
            <li class="flex items-center gap-2">
                <span class="text-teal-500">✓</span> Expenses, leaves, orders &amp; customer CRM
            </li>
            <li class="flex items-center gap-2">
                <span class="text-teal-500">✓</span> AI-powered visit summaries &amp; reports
            </li>
        </ul>
    </div>

    {{-- Pay button --}}
    <button id="rzp-button" type="button"
        class="mt-6 flex w-full items-center justify-center gap-2 rounded-xl bg-teal-600 py-3.5 text-base font-semibold text-white shadow-lg transition-colors hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
        </svg>
        Pay {{ $formattedAmount }} with Razorpay
    </button>

    {{-- Trust row --}}
    <div class="mt-4 flex items-center justify-center gap-4 text-xs text-slate-400">
        <span class="flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            SSL encrypted
        </span>
        <span class="text-slate-200">|</span>
        <span>Razorpay secure payment</span>
        <span class="text-slate-200">|</span>
        <span>Card details never stored</span>
    </div>

    <p class="mt-6 text-center text-xs text-slate-400">
        <a href="{{ route('companies.register') }}" class="text-teal-600 hover:underline">← Back to registration</a>
    </p>
</div>
@endsection

@push('head')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.getElementById('rzp-button').onclick = function () {
    const options = {
        key: @json($razorpayKey),
        amount: {{ (int) round((float) $payment->amount_usd * 100) }},
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
