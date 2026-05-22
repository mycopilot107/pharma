@extends('layouts.mr')

@section('title', 'Daily Routes')

@section('mr-content')
<h1 class="text-xl font-bold">Daily routes</h1>
<p class="text-sm text-slate-500">Track your planned field routes</p>

<div class="mt-6 space-y-3">
    @forelse ($routes as $route)
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="font-medium">{{ $route->title }}</p>
            <p class="text-sm text-slate-500">{{ $route->route_date->format('d M Y') }} · {{ ucfirst($route->status) }}</p>
            <p class="text-xs text-slate-400 mt-1">{{ $route->visits_count }} visits logged</p>
        </div>
    @empty
        <p class="text-sm text-slate-500">No routes yet. Start one from your dashboard.</p>
    @endforelse
</div>

{{ $routes->links() }}
@endsection
