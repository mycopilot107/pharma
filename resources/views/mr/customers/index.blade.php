@extends('layouts.mr')

@section('title', 'Customers')

@section('mr-content')
<h1 class="text-xl font-bold">Customers (CRM)</h1>
<p class="text-sm text-slate-500">Doctors, hospitals, clinics, chemists & distributors</p>

<form method="GET" class="mt-4 flex gap-2">
    <input type="search" name="search" value="{{ request('search') }}" placeholder="Search..." class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
    <select name="type" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <option value="">All types</option>
        @foreach ($customerTypes as $type)
            <option value="{{ $type->value }}" @selected(request('type') === $type->value)>{{ $type->label() }}</option>
        @endforeach
    </select>
    <button type="submit" class="rounded-lg bg-slate-100 px-3 py-2 text-sm">Go</button>
</form>

<details class="mt-4 rounded-xl border border-teal-200 bg-teal-50 p-4">
    <summary class="cursor-pointer font-medium text-teal-800">+ Add customer</summary>
    <form method="POST" action="{{ route('mr.customers.store') }}" class="mt-4 space-y-3">
        @csrf
        <select name="type" required class="w-full rounded-lg border px-3 py-2 text-sm">
            @foreach ($customerTypes as $type)
                <option value="{{ $type->value }}">{{ $type->icon() }} {{ $type->label() }}</option>
            @endforeach
        </select>
        <input type="text" name="name" placeholder="Name *" required class="w-full rounded-lg border px-3 py-2 text-sm">
        <input type="text" name="contact_person" placeholder="Contact person" class="w-full rounded-lg border px-3 py-2 text-sm">
        <input type="text" name="specialty" placeholder="Specialty (doctors)" class="w-full rounded-lg border px-3 py-2 text-sm">
        <input type="text" name="phone" placeholder="Phone" class="w-full rounded-lg border px-3 py-2 text-sm">
        <textarea name="address" placeholder="Address" rows="2" class="w-full rounded-lg border px-3 py-2 text-sm"></textarea>
        <input type="hidden" name="latitude" id="customer-lat">
        <input type="hidden" name="longitude" id="customer-lng">
        <button type="button" onclick="fillGpsFields('customer-lat','customer-lng')" class="text-xs text-teal-700">Use current GPS</button>
        <button type="submit" class="w-full rounded-lg bg-teal-600 py-2 text-sm font-medium text-white">Save customer</button>
    </form>
</details>

<div class="mt-4 space-y-2">
    @forelse ($customers as $customer)
        <a href="{{ route('mr.customers.show', $customer) }}" class="block rounded-xl border border-slate-200 bg-white p-3 hover:border-teal-300">
            <p class="font-medium">{{ $customer->type->icon() }} {{ $customer->name }}</p>
            @if ($customer->specialty)<p class="text-xs text-slate-500">{{ $customer->specialty }}</p>@endif
            @if ($customer->phone)<p class="text-xs text-slate-500">{{ $customer->phone }}</p>@endif
        </a>
    @empty
        <p class="text-sm text-slate-500 py-4 text-center">No customers yet.</p>
    @endforelse
</div>

{{ $customers->links() }}
@endsection

@push('head')
@include('partials.gps-script')
@endpush
