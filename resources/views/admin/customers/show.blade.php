@extends('layouts.app')

@section('title', $customer->name)

@section('content')
<a href="{{ route('admin.customers.index') }}" class="text-sm text-teal-700 hover:underline">← All customers</a>

<div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <span class="rounded-full px-2 py-0.5 text-xs {{ $customer->type->color() }}">{{ $customer->type->icon() }} {{ $customer->type->label() }}</span>
        <h1 class="mt-2 text-2xl font-bold">{{ $customer->name }}</h1>
        @if ($customer->contact_person)
            <p class="text-slate-600">Contact: {{ $customer->contact_person }}</p>
        @endif
        @if ($customer->specialty)
            <p class="text-sm text-slate-500">{{ $customer->specialty }}</p>
        @endif
    </div>
    <a href="{{ route('admin.customers.edit', $customer) }}" class="rounded-lg border px-4 py-2 text-sm hover:bg-slate-50">Edit</a>
</div>

<div class="mt-6 grid gap-4 sm:grid-cols-2 max-w-3xl">
    <div class="rounded-xl border bg-white p-4">
        <p class="text-xs text-slate-500">Phone</p>
        <p class="font-medium">{{ $customer->phone ?? '—' }}</p>
    </div>
    <div class="rounded-xl border bg-white p-4">
        <p class="text-xs text-slate-500">Email</p>
        <p class="font-medium">{{ $customer->email ?? '—' }}</p>
    </div>
    <div class="rounded-xl border bg-white p-4 sm:col-span-2">
        <p class="text-xs text-slate-500">Address</p>
        <p class="font-medium">{{ $customer->fullAddress() ?: '—' }}</p>
    </div>
    @if ($customer->notes)
        <div class="rounded-xl border bg-white p-4 sm:col-span-2">
            <p class="text-xs text-slate-500">Notes</p>
            <p class="text-sm text-slate-700 whitespace-pre-wrap">{{ $customer->notes }}</p>
        </div>
    @endif
</div>

@include('partials.customer-crm-tracking', [
    'customer' => $customer,
    'crmRoutePrefix' => 'admin',
    'meetings' => $meetings,
    'followUps' => $followUps,
    'prescriptions' => $prescriptions,
    'purchases' => $purchases,
    'purchasePatterns' => $purchasePatterns,
    'editable' => true,
])
@endsection
