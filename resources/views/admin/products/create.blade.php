@extends('layouts.app')

@section('title', 'Add Product')

@section('content')
<a href="{{ route('admin.products.index') }}" class="text-sm text-teal-700">← Products</a>
<h1 class="mt-4 text-2xl font-bold">Add product</h1>

<form method="POST" action="{{ route('admin.products.store') }}" class="mt-6 max-w-2xl space-y-4 rounded-xl border bg-white p-6">
    @csrf
    @include('admin.products._form')
    <button type="submit" class="rounded-lg bg-teal-600 px-6 py-2.5 font-medium text-white hover:bg-teal-700">Save product</button>
</form>
@endsection
