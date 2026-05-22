@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
<a href="{{ route('admin.products.index') }}" class="text-sm text-teal-700">← Products</a>
<h1 class="mt-4 text-2xl font-bold">Edit {{ $product->name }}</h1>

<form method="POST" action="{{ route('admin.products.update', $product) }}" class="mt-6 max-w-2xl space-y-4 rounded-xl border bg-white p-6">
    @csrf
    @method('PUT')
    @include('admin.products._form', ['product' => $product])
    <div class="flex gap-3">
        <button type="submit" class="rounded-lg bg-teal-600 px-6 py-2.5 font-medium text-white hover:bg-teal-700">Update</button>
    </div>
</form>

<form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="mt-4" onsubmit="return confirm('Delete this product?')">
    @csrf
    @method('DELETE')
    <button type="submit" class="rounded-lg border border-red-200 px-4 py-2 text-sm text-red-700 hover:bg-red-50">Delete product</button>
</form>
@endsection
