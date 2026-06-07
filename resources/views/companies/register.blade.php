@extends('layouts.app')

@section('title', 'Register Company')

@section('content')
<div class="mx-auto max-w-2xl">

    {{-- Step progress --}}
    <div class="mb-8 flex items-center gap-3">
        <div class="flex items-center gap-2">
            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-teal-600 text-xs font-bold text-white">1</span>
            <span class="text-sm font-semibold text-teal-700">Company details</span>
        </div>
        <div class="h-px flex-1 bg-slate-200"></div>
        <div class="flex items-center gap-2 opacity-50">
            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-slate-200 text-xs font-bold text-slate-500">2</span>
            <span class="text-sm text-slate-500">Payment</span>
        </div>
    </div>

    <h1 class="text-2xl font-bold text-slate-900">Register your pharma company</h1>
    <p class="mt-1 text-slate-500">Start free with 1 MR, or choose a paid plan.</p>

    <form method="POST" action="{{ route('companies.register.store') }}" class="mt-8 space-y-5">
        @csrf

        {{-- Subscription & billing --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-slate-50/70 px-6 py-4">
                <h2 class="text-sm font-semibold text-slate-800">Subscription &amp; billing</h2>
                <p class="mt-0.5 text-xs text-slate-500">Choose your plan and billing frequency.</p>
            </div>
            <div class="space-y-5 p-6">

                {{-- Currency --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700" for="currency">
                        Operating currency <span class="text-red-500">*</span>
                    </label>
                    <select name="currency" id="currency" required
                        class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                        @foreach ($currencies as $code => $meta)
                            <option value="{{ $code }}" data-symbol="{{ $meta['symbol'] }}"
                                @selected(old('currency', config('currencies.default', 'USD')) === $code)>
                                {{ $meta['symbol'] }} — {{ $code }} ({{ $meta['name'] }})
                            </option>
                        @endforeach
                    </select>
                    @error('currency')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                {{-- Billing cycle toggle --}}
                <div>
                    <p class="mb-2 block text-sm font-medium text-slate-700">
                        Billing cycle <span class="text-red-500">*</span>
                    </p>
                    <div class="inline-flex rounded-full bg-slate-100 p-1" role="group" aria-label="Billing cycle">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="billing_cycle" id="cycle_monthly" value="monthly"
                                class="sr-only"
                                @checked(old('billing_cycle', request('billing_cycle', 'monthly')) === 'monthly')>
                            <span id="cycle-monthly-btn"
                                class="block select-none rounded-full px-5 py-2 text-sm font-semibold transition-all duration-200">
                                Monthly
                            </span>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="billing_cycle" id="cycle_yearly" value="yearly"
                                class="sr-only"
                                @checked(old('billing_cycle', request('billing_cycle')) === 'yearly')>
                            <span id="cycle-yearly-btn"
                                class="block select-none rounded-full px-5 py-2 text-sm font-semibold transition-all duration-200">
                                Yearly
                                <span id="yearly-badge"
                                    class="ml-1 inline-block rounded-full px-1.5 py-0.5 text-xs font-bold">
                                    −{{ (int) round(config('pharma.yearly_discount', 0.10) * 100) }}%
                                </span>
                            </span>
                        </label>
                    </div>
                    @error('billing_cycle')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                {{-- Plan select --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700" for="plan_id">
                        Number of medical representatives <span class="text-red-500">*</span>
                    </label>
                    <select name="plan_id" id="plan_id" required
                        class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->id }}"
                                data-price="{{ $plan->price_usd }}"
                                data-yearly="{{ $plan->yearlyPrice() }}"
                                data-limit="{{ $plan->user_limit }}"
                                data-free="{{ $plan->isFree() ? '1' : '0' }}"
                                @selected(old('plan_id', request('plan')) == $plan->id)>
                                {{ $plan->user_limit }} users — {{ $plan->formattedPrice(old('currency', config('currencies.default', 'USD'))) }}/month
                            </option>
                        @endforeach
                    </select>
                    @error('plan_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                {{-- Price summary card --}}
                <div id="price-summary"
                    class="rounded-xl bg-gradient-to-br from-teal-600 to-teal-700 p-5 text-white shadow-md">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-teal-200" id="price-label">
                                Monthly subscription
                            </p>
                            <p class="mt-1 text-3xl font-bold tabular-nums" id="price-display">—</p>
                            <p class="mt-1 text-sm text-teal-100" id="price-breakdown"></p>
                        </div>
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/20">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-2.5 hidden text-xs font-semibold text-yellow-300" id="yearly-saving-note"></p>
                </div>

            </div>
        </div>

        {{-- Company details --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-slate-50/70 px-6 py-4">
                <h2 class="text-sm font-semibold text-slate-800">Company details</h2>
            </div>
            <div class="p-6">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700" for="company_name">
                            Company name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="company_name" id="company_name"
                            value="{{ old('company_name') }}" required minlength="2" maxlength="255"
                            class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                        @error('company_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="company_email">
                            Company email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="company_email" id="company_email"
                            value="{{ old('company_email') }}" required autocomplete="email"
                            maxlength="255" placeholder="company@example.com"
                            class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                        @error('company_email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="company_phone">
                            Mobile number <span class="text-red-500">*</span>
                        </label>
                        <input type="tel" name="company_phone" id="company_phone"
                            value="{{ old('company_phone') }}" required
                            inputmode="numeric" pattern="[6-9][0-9]{9}" maxlength="10" minlength="10"
                            placeholder="10-digit mobile" autocomplete="tel"
                            class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                        <p class="mt-1 text-xs text-slate-400">10 digits starting with 6–9</p>
                        @error('company_phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700" for="address">Address</label>
                        <textarea name="address" id="address" rows="2"
                            class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">{{ old('address') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Admin account --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-slate-50/70 px-6 py-4">
                <h2 class="text-sm font-semibold text-slate-800">Admin account</h2>
                <p class="mt-0.5 text-xs text-slate-500">These credentials will be used to log in to the admin dashboard.</p>
            </div>
            <div class="p-6">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700" for="admin_name">
                            Admin name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="admin_name" id="admin_name"
                            value="{{ old('admin_name') }}" required minlength="2" maxlength="255"
                            class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                        @error('admin_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="admin_email">
                            Login email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="admin_email" id="admin_email"
                            value="{{ old('admin_email') }}" required autocomplete="email"
                            maxlength="255" placeholder="admin@example.com"
                            class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                        @error('admin_email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="admin_password">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="admin_password" id="admin_password"
                            required minlength="8" autocomplete="new-password"
                            class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                        <p class="mt-1 text-xs text-slate-400">Min. 8 characters with letters and numbers</p>
                        @error('admin_password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="admin_password_confirmation">
                            Confirm password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="admin_password_confirmation" id="admin_password_confirmation"
                            required minlength="8" autocomplete="new-password"
                            class="mt-1.5 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
                        @error('admin_password_confirmation')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Submit --}}
        <button type="submit" id="submit-btn"
            class="flex w-full items-center justify-center gap-2 rounded-xl bg-teal-600 py-3.5 text-base font-semibold text-white shadow-lg transition-colors hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2">
            <span id="submit-text">Continue to payment</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
        </button>

        <p class="flex items-center justify-center gap-1 pb-4 text-xs text-slate-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            Secure &middot; SSL encrypted &middot; Powered by Razorpay
        </p>
    </form>
</div>

@push('head')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const planSelect     = document.getElementById('plan_id');
    const currencySelect = document.getElementById('currency');
    const priceDisplay   = document.getElementById('price-display');
    const priceBreakdown = document.getElementById('price-breakdown');
    const priceLabel     = document.getElementById('price-label');
    const savingNote     = document.getElementById('yearly-saving-note');
    const submitText     = document.getElementById('submit-text');
    const monthlyRadio   = document.getElementById('cycle_monthly');
    const yearlyRadio    = document.getElementById('cycle_yearly');
    const monthlyBtn     = document.getElementById('cycle-monthly-btn');
    const yearlyBtn      = document.getElementById('cycle-yearly-btn');
    const yearlyBadge    = document.getElementById('yearly-badge');
    const pricePerUser   = {{ config('pharma.price_per_user_usd', 3) }};
    const discountPct    = {{ (int) round(config('pharma.yearly_discount', 0.10) * 100) }};

    const ACTIVE   = ['bg-teal-600', 'text-white', 'shadow-sm'];
    const INACTIVE = ['text-slate-500'];

    function symbol() {
        return currencySelect.options[currencySelect.selectedIndex].dataset.symbol || '$';
    }

    function formatMoney(amount) {
        return symbol() + ' ' + Number(amount).toFixed(2);
    }

    function isYearly() { return yearlyRadio.checked; }

    function updateCycleButtons() {
        const yearly = isYearly();
        monthlyBtn.classList.remove(...ACTIVE, ...INACTIVE);
        yearlyBtn.classList.remove(...ACTIVE, ...INACTIVE);
        if (yearly) {
            monthlyBtn.classList.add(...INACTIVE);
            yearlyBtn.classList.add(...ACTIVE);
            yearlyBadge.className = 'ml-1 inline-block rounded-full bg-white/20 px-1.5 py-0.5 text-xs font-bold';
        } else {
            monthlyBtn.classList.add(...ACTIVE);
            yearlyBtn.classList.add(...INACTIVE);
            yearlyBadge.className = 'ml-1 inline-block rounded-full bg-teal-100 px-1.5 py-0.5 text-xs font-bold text-teal-700';
        }
    }

    function updatePrice() {
        const opt     = planSelect.options[planSelect.selectedIndex];
        const limit   = opt.dataset.limit;
        const monthly = parseFloat(opt.dataset.price);
        const yearly  = parseFloat(opt.dataset.yearly);
        const isFree  = opt.dataset.free === '1' || monthly <= 0;
        const cycle   = isYearly();

        if (isFree) {
            priceLabel.textContent     = 'Free plan';
            priceDisplay.textContent   = 'Free';
            priceBreakdown.textContent = '1 medical representative — no payment required';
            savingNote.classList.add('hidden');
            submitText.textContent     = 'Create free account';
        } else if (cycle) {
            const saved = (monthly * 12) - yearly;
            priceLabel.textContent     = 'Yearly subscription';
            priceDisplay.textContent   = formatMoney(yearly) + ' / year';
            priceBreakdown.textContent =
                limit + ' users × ' + formatMoney(pricePerUser) +
                ' × 12 months − ' + discountPct + '% discount';
            savingNote.textContent     = '✓ You save ' + formatMoney(saved) + ' vs monthly billing';
            savingNote.classList.remove('hidden');
            submitText.textContent     = 'Continue to payment (yearly)';
        } else {
            priceLabel.textContent     = 'Monthly subscription';
            priceDisplay.textContent   = formatMoney(monthly) + ' / month';
            priceBreakdown.textContent =
                limit + ' users × ' + formatMoney(pricePerUser) +
                ' = ' + formatMoney(monthly) + ' / month';
            savingNote.classList.add('hidden');
            submitText.textContent     = 'Continue to payment';
        }
    }

    planSelect.addEventListener('change', updatePrice);
    currencySelect.addEventListener('change', updatePrice);
    monthlyRadio.addEventListener('change', () => { updateCycleButtons(); updatePrice(); });
    yearlyRadio.addEventListener('change',  () => { updateCycleButtons(); updatePrice(); });

    updateCycleButtons();
    updatePrice();

    const phoneInput = document.getElementById('company_phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', () => {
            phoneInput.value = phoneInput.value.replace(/\D/g, '').slice(0, 10);
        });
        phoneInput.addEventListener('paste', e => {
            e.preventDefault();
            const digits = (e.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, 10);
            phoneInput.value = digits;
        });
    }
});
</script>
@endpush
@endsection
