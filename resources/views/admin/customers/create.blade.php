@extends('layouts.app')

@section('title', 'Add Customer')

@section('content')
<a href="{{ route('admin.customers.index') }}" class="text-sm text-teal-700 hover:underline">← All customers</a>
<h1 class="mt-4 text-2xl font-bold">Add customer</h1>
<p class="text-slate-600">Add a doctor, hospital, clinic, chemist or distributor to your CRM.</p>

<form method="POST" action="{{ route('admin.customers.store') }}" class="mt-8 max-w-2xl space-y-5 rounded-2xl border bg-white p-6 shadow-sm">
    @csrf
    @include('admin.customers._form', ['customer' => null])
    <button type="submit" class="w-full rounded-xl bg-teal-600 py-3 font-semibold text-white hover:bg-teal-700">Save customer</button>
</form>
@endsection
