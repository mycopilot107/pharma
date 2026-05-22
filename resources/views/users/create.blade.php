@extends('layouts.app')

@section('title', 'Add Representative')

@section('content')
<div class="mx-auto max-w-lg">
    <a href="{{ route('users.index') }}" class="text-sm text-teal-700 hover:underline">← All representatives</a>
    <h1 class="mt-4 text-2xl font-bold text-slate-900">Add medical representative</h1>
    <p class="mt-1 text-sm text-slate-600">{{ $remainingSlots }} slot(s) remaining on your plan.</p>

    <form method="POST" action="{{ route('users.store') }}" class="mt-8 space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @include('users._form')
        <button type="submit" class="w-full rounded-xl bg-teal-600 py-3 font-semibold text-white hover:bg-teal-700">Create representative</button>
    </form>
</div>
@endsection
