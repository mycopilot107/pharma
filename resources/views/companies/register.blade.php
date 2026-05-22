@extends('layouts.app')

@section('title', 'Register Company')

@section('content')
<div class="mx-auto max-w-3xl">
    <h1 class="text-3xl font-bold text-slate-900">Register your pharma company</h1>
    <p class="mt-2 text-slate-600">Start free with 1 MR, or choose a paid plan and pay with Razorpay to activate.</p>

    <form method="POST" action="{{ route('companies.register.store') }}" class="mt-8 space-y-8">
        @csrf

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-800">Subscription & currency</h2>
            <p class="mt-1 text-sm text-slate-500">Currency applies to expenses, products, orders and reports across your company.</p>

            <label class="mt-4 block text-sm font-medium text-slate-700" for="currency">Operating currency *</label>
            <select name="currency" id="currency" required class="mt-2 w-full rounded-lg border border-slate-300 px-4 py-3 focus:border-teal-500 focus:ring-teal-500">
                @foreach ($currencies as $code => $meta)
                    <option value="{{ $code }}" data-symbol="{{ $meta['symbol'] }}"
                        @selected(old('currency', config('currencies.default', 'USD')) === $code)>
                        {{ $meta['symbol'] }} — {{ $code }} ({{ $meta['name'] }})
                    </option>
                @endforeach
            </select>
            @error('currency')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror

            <label class="mt-4 block text-sm font-medium text-slate-700" for="plan_id">Number of medical representatives *</label>
            <select name="plan_id" id="plan_id" required class="mt-2 w-full rounded-lg border border-slate-300 px-4 py-3 focus:border-teal-500 focus:ring-teal-500">
                @foreach ($plans as $plan)
                    <option value="{{ $plan->id }}" data-price="{{ $plan->price_usd }}" data-limit="{{ $plan->user_limit }}" data-free="{{ $plan->isFree() ? '1' : '0' }}"
                        @selected(old('plan_id', request('plan')) == $plan->id)>
                        {{ $plan->user_limit }} users — {{ $plan->formattedPrice(old('currency', config('currencies.default', 'USD'))) }}/month
                    </option>
                @endforeach
            </select>
            @error('plan_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror

            <div id="price-summary" class="mt-4 rounded-xl bg-teal-50 p-4 text-teal-900">
                <p class="text-sm">Monthly subscription</p>
                <p class="text-2xl font-bold" id="price-display">—</p>
                <p class="text-sm text-teal-700" id="price-breakdown"></p>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-800">Company details</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700" for="company_name">Company name *</label>
                    <input type="text" name="company_name" id="company_name" value="{{ old('company_name') }}" required minlength="2" maxlength="255"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-teal-500 focus:ring-teal-500">
                    @error('company_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700" for="company_email">Company email *</label>
                    <input type="email" name="company_email" id="company_email" value="{{ old('company_email') }}" required
                        autocomplete="email" maxlength="255" placeholder="company@example.com"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-teal-500 focus:ring-teal-500">
                    @error('company_email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700" for="company_phone">Mobile number *</label>
                    <input type="tel" name="company_phone" id="company_phone" value="{{ old('company_phone') }}" required
                        inputmode="numeric" pattern="[6-9][0-9]{9}" maxlength="10" minlength="10"
                        placeholder="10-digit mobile" autocomplete="tel"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-teal-500 focus:ring-teal-500">
                    <p class="mt-1 text-xs text-slate-500">Digits only, 10 numbers (e.g. 9876543210)</p>
                    @error('company_phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700" for="address">Address</label>
                    <textarea name="address" id="address" rows="2" class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-teal-500 focus:ring-teal-500">{{ old('address') }}</textarea>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-800">Company admin account</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700" for="admin_name">Admin name *</label>
                    <input type="text" name="admin_name" id="admin_name" value="{{ old('admin_name') }}" required minlength="2" maxlength="255"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-teal-500 focus:ring-teal-500">
                    @error('admin_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700" for="admin_email">Admin email (login) *</label>
                    <input type="email" name="admin_email" id="admin_email" value="{{ old('admin_email') }}" required
                        autocomplete="email" maxlength="255" placeholder="admin@example.com"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-teal-500 focus:ring-teal-500">
                    @error('admin_email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700" for="admin_password">Password *</label>
                    <input type="password" name="admin_password" id="admin_password" required minlength="8" autocomplete="new-password"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-teal-500 focus:ring-teal-500">
                    <p class="mt-1 text-xs text-slate-500">Min. 8 characters with letters and numbers</p>
                    @error('admin_password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700" for="admin_password_confirmation">Confirm password *</label>
                    <input type="password" name="admin_password_confirmation" id="admin_password_confirmation" required minlength="8" autocomplete="new-password"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-teal-500 focus:ring-teal-500">
                    @error('admin_password_confirmation')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <button type="submit" id="submit-btn" class="w-full rounded-xl bg-teal-600 py-3.5 text-lg font-semibold text-white hover:bg-teal-700">
            Continue to payment
        </button>
    </form>
</div>

@push('head')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const planSelect = document.getElementById('plan_id');
    const currencySelect = document.getElementById('currency');
    const priceDisplay = document.getElementById('price-display');
    const priceBreakdown = document.getElementById('price-breakdown');
    const pricePerUser = {{ config('pharma.price_per_user_usd', 3) }};
    const submitBtn = document.getElementById('submit-btn');

    function symbol() {
        return currencySelect.options[currencySelect.selectedIndex].dataset.symbol || '$';
    }

    function formatMoney(amount) {
        return symbol() + ' ' + Number(amount).toFixed(2);
    }

    function updatePrice() {
        const opt = planSelect.options[planSelect.selectedIndex];
        const limit = opt.dataset.limit;
        const price = parseFloat(opt.dataset.price);
        const isFree = opt.dataset.free === '1' || price <= 0;

        if (isFree) {
            priceDisplay.textContent = 'Free';
            priceBreakdown.textContent = '1 medical representative — no payment required';
            submitBtn.textContent = 'Create free account';
        } else {
            priceDisplay.textContent = formatMoney(price);
            priceBreakdown.textContent = limit + ' users × ' + formatMoney(pricePerUser) + ' = ' + formatMoney(price) + '/month';
            submitBtn.textContent = 'Continue to Razorpay payment';
        }
    }

    planSelect.addEventListener('change', updatePrice);
    currencySelect.addEventListener('change', updatePrice);
    updatePrice();

    const phoneInput = document.getElementById('company_phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', () => {
            phoneInput.value = phoneInput.value.replace(/\D/g, '').slice(0, 10);
        });
        phoneInput.addEventListener('paste', (e) => {
            e.preventDefault();
            const digits = (e.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, 10);
            phoneInput.value = digits;
        });
    }
});
</script>
@endpush
@endsection
