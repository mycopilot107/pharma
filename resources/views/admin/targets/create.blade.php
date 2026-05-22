@extends('layouts.app')

@section('title', 'Assign Target')

@section('content')
<a href="{{ route('admin.targets.index') }}" class="text-sm text-teal-700 hover:underline">← All targets</a>
<h1 class="mt-4 text-2xl font-bold">Assign new target</h1>
<p class="text-slate-600">Set monthly, product, sales or area-wise goals for a medical representative.</p>

<form method="POST" action="{{ route('admin.targets.store') }}" class="mt-8 max-w-2xl space-y-5 rounded-2xl border bg-white p-6 shadow-sm">
    @csrf
    @include('admin.targets._form', ['target' => null])
    <button type="submit" class="w-full rounded-xl bg-teal-600 py-3 font-semibold text-white hover:bg-teal-700">Assign target</button>
</form>
@endsection
