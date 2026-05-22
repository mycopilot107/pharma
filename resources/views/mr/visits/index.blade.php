@extends('layouts.mr')

@section('title', 'My Visits')

@section('mr-content')
<h1 class="text-xl font-bold">Visit history</h1>

<form method="GET" class="mt-4 flex gap-2">
    <input type="date" name="date" value="{{ request('date') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
    <select name="status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <option value="">All statuses</option>
        @foreach (\App\Enums\VisitStatus::cases() as $s)
            <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>
        @endforeach
    </select>
    <button type="submit" class="rounded-lg bg-slate-100 px-4 py-2 text-sm">Filter</button>
</form>

<div class="mt-4 space-y-3">
    @forelse ($visits as $visit)
        <a href="{{ route('mr.visits.show', $visit) }}" class="block rounded-xl border border-slate-200 bg-white p-4 hover:border-teal-300">
            <div class="flex items-center gap-3">
                <span class="text-2xl">{{ $visit->visit_type->icon() }}</span>
                <div class="flex-1">
                    <p class="font-medium">{{ $visit->place_name }}</p>
                    <p class="text-xs text-slate-500">{{ $visit->created_at->format('d M Y, h:i A') }} · {{ $visit->status->label() }}</p>
                </div>
                @if ($visit->formattedDuration())
                    <span class="text-xs text-slate-400">{{ $visit->formattedDuration() }}</span>
                @endif
            </div>
        </a>
    @empty
        <p class="text-center text-sm text-slate-500 py-8">No visits found.</p>
    @endforelse
</div>

{{ $visits->links() }}
@endsection
