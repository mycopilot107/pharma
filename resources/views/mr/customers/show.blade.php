@extends('layouts.mr')

@section('title', $customer->name)

@section('mr-content')
<a href="{{ route('mr.customers.index') }}" class="text-sm text-teal-700">← Customers</a>

<div class="mt-4">
    <span class="text-2xl">{{ $customer->type->icon() }}</span>
    <h1 class="mt-1 text-xl font-bold">{{ $customer->name }}</h1>
    <p class="text-sm text-slate-500">{{ $customer->type->label() }}</p>
</div>

@if ($customer->phone)
    <a href="tel:{{ $customer->phone }}" class="mt-4 block rounded-xl border bg-white p-3 text-teal-700 font-medium">{{ $customer->phone }}</a>
@endif

@if ($customer->fullAddress())
    <p class="mt-3 text-sm text-slate-600">{{ $customer->fullAddress() }}</p>
@endif

<a href="{{ route('mr.visits.create') }}?customer_id={{ $customer->id }}" class="mt-6 block w-full rounded-xl bg-teal-600 py-3 text-center font-semibold text-white">
    Start visit / meeting
</a>

@include('partials.customer-crm-tracking', [
    'customer' => $customer,
    'crmRoutePrefix' => 'mr',
    'meetings' => $meetings,
    'followUps' => $followUps,
    'prescriptions' => $prescriptions,
    'purchases' => $purchases,
    'purchasePatterns' => $purchasePatterns,
    'editable' => true,
])
@endsection
