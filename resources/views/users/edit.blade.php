@extends('layouts.app')

@section('title', 'Edit '.$user->name)

@section('content')
<div class="mx-auto max-w-lg">
    <a href="{{ route('users.show', $user) }}" class="text-sm text-teal-700 hover:underline">← {{ $user->name }}</a>
    <h1 class="mt-4 text-2xl font-bold">Edit representative</h1>
    <p class="mt-1 text-sm text-slate-600">Update profile, login email, or reset password.</p>

    <form method="POST" action="{{ route('users.update', $user) }}" class="mt-8 space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        @include('users._form', ['user' => $user])
        <button type="submit" class="w-full rounded-xl bg-teal-600 py-3 font-semibold text-white hover:bg-teal-700">Save changes</button>
    </form>
</div>
@endsection
