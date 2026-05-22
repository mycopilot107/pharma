@extends('layouts.mr')

@section('title', 'My Targets')

@section('mr-content')
<h1 class="text-xl font-bold">My targets</h1>
<p class="text-sm text-slate-500">{{ $activeCount }} active target(s) assigned by your manager</p>

<form method="GET" class="mt-4">
    <select name="type" onchange="this.form.submit()" class="w-full rounded-lg border px-3 py-2 text-sm">
        <option value="">All types</option>
        @foreach (\App\Enums\TargetType::cases() as $t)
            <option value="{{ $t->value }}" @selected(request('type') === $t->value)>{{ $t->label() }}</option>
        @endforeach
    </select>
</form>

<div class="mt-6 space-y-4">
    @forelse ($targets as $target)
        <a href="{{ route('mr.targets.show', $target) }}" class="block rounded-2xl border border-slate-200 bg-white p-4 hover:border-teal-300">
            <div class="flex gap-3">
                <span class="text-2xl">{{ $target->type->icon() }}</span>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-slate-900">{{ $target->title }}</p>
                    <p class="text-xs text-slate-500">{{ $target->type->label() }} · {{ $target->subtitle() }}</p>
                    <div class="mt-3">
                        <div class="flex justify-between text-xs mb-1">
                            <span>{{ $target->formattedAchieved() }} / {{ $target->formattedTarget() }}</span>
                            <span class="font-medium text-teal-700">{{ $target->progressPercent() }}%</span>
                        </div>
                        <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full rounded-full {{ $target->progressPercent() >= 100 ? 'bg-emerald-500' : 'bg-teal-500' }}" style="width: {{ $target->progressPercent() }}%"></div>
                        </div>
                    </div>
                    <span class="mt-2 inline-block rounded-full px-2 py-0.5 text-xs {{ $target->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                        {{ ucfirst($target->status) }}
                    </span>
                </div>
            </div>
        </a>
    @empty
        <p class="text-center text-sm text-slate-500 py-8">No targets assigned yet.</p>
    @endforelse
</div>

{{ $targets->links() }}
@endsection
