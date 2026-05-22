@extends('layouts.app')

@section('title', 'Edit Target')

@section('content')
<a href="{{ route('admin.targets.index') }}" class="text-sm text-teal-700 hover:underline">← All targets</a>
<h1 class="mt-4 text-2xl font-bold">Edit target</h1>

<form method="POST" action="{{ route('admin.targets.update', $target) }}" class="mt-8 max-w-2xl space-y-5 rounded-2xl border bg-white p-6 shadow-sm">
    @csrf
    @method('PUT')
    @include('admin.targets._form', ['target' => $target])
    <button type="submit" class="w-full rounded-xl bg-teal-600 py-3 font-semibold text-white hover:bg-teal-700">Update target</button>
</form>
@endsection
