@extends('layouts.mr')

@section('title', 'New Visit')

@section('mr-content')
<h1 class="text-xl font-bold">New visit</h1>
<p class="text-sm text-slate-500">Doctor, chemist, or hospital visit with GPS check-in</p>

<form method="POST" action="{{ route('mr.visits.store') }}" class="mt-6 space-y-5" id="visit-form">
    @csrf
    <input type="hidden" name="latitude" id="visit-lat">
    <input type="hidden" name="longitude" id="visit-lng">
    @if ($todayRoute)
        <input type="hidden" name="daily_route_id" value="{{ $todayRoute->id }}">
    @endif

    <div>
        <label class="block text-sm font-medium text-slate-700">Visit type</label>
        <select name="visit_type" required class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5">
            @foreach ($visitTypes as $type)
                <option value="{{ $type->value }}">{{ $type->icon() }} {{ $type->label() }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700">Customer from CRM (optional)</label>
        <select name="customer_id" id="customer_id" class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5">
            <option value="">— New / manual entry —</option>
            @foreach ($customers as $customer)
                <option value="{{ $customer->id }}" data-name="{{ $customer->name }}" data-address="{{ $customer->address }}"
                    @selected(request('customer_id') == $customer->id)>
                    {{ $customer->type->icon() }} {{ $customer->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div id="place-fields">
        <label class="block text-sm font-medium text-slate-700" for="place_name">Place name</label>
        <input type="text" name="place_name" id="place_name" class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700" for="address">Address</label>
        <textarea name="address" id="address" rows="2" class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5"></textarea>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700" for="notes">Notes</label>
        <textarea name="notes" id="notes" rows="2" class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5" placeholder="Visit purpose, products discussed..."></textarea>
    </div>

    <p id="gps-status" class="text-xs text-slate-500"></p>

    <button type="button" onclick="document.getElementById('start_now').value=1; submitWithGps('visit-form','visit-lat','visit-lng','gps-status')" class="w-full rounded-xl bg-teal-600 py-3 font-semibold text-white">
        Check in now (GPS)
    </button>
    <input type="hidden" name="start_now" id="start_now" value="0">
    <button type="submit" class="w-full rounded-xl border border-slate-300 py-3 font-medium text-slate-700">
        Save as planned (check in later)
    </button>
</form>
@endsection

@push('head')
@include('partials.gps-script')
<script>
function fillFromCustomer(select) {
    const opt = select.options[select.selectedIndex];
    if (opt.value) {
        document.getElementById('place_name').value = opt.dataset.name || '';
        document.getElementById('address').value = opt.dataset.address || '';
    }
}
const customerSelect = document.getElementById('customer_id');
customerSelect?.addEventListener('change', function() { fillFromCustomer(this); });
if (customerSelect?.value) fillFromCustomer(customerSelect);
</script>
@endpush
