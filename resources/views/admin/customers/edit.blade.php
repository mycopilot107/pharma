@extends('layouts.app')

@section('title', 'Edit Customer')

@section('content')
<a href="{{ route('admin.customers.index') }}" class="text-sm text-teal-700 hover:underline">← All customers</a>
<h1 class="mt-4 text-2xl font-bold">Edit {{ $customer->name }}</h1>

<form method="POST" action="{{ route('admin.customers.update', $customer) }}" class="mt-8 max-w-2xl space-y-5 rounded-2xl border bg-white p-6 shadow-sm">
    @csrf
    @method('PUT')
    @include('admin.customers._form', ['customer' => $customer])
    <button type="submit" class="w-full rounded-xl bg-teal-600 py-3 font-semibold text-white hover:bg-teal-700">Update customer</button>
</form>

<form method="POST" action="{{ route('admin.customers.destroy', $customer) }}" class="mt-4 max-w-2xl" onsubmit="return confirm('Remove this customer from CRM?');">
    @csrf
    @method('DELETE')
    <button type="submit" class="text-sm text-red-600 hover:underline">Delete customer</button>
</form>
@endsection
