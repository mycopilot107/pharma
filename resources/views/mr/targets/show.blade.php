@extends('layouts.mr')

@section('title', $target->title)

@section('mr-content')
<a href="{{ route('mr.targets.index') }}" class="text-sm text-teal-700 hover:underline">← My targets</a>

<div class="mt-4 rounded-2xl border bg-white p-5 shadow-sm">
    <div class="flex gap-3">
        <span class="text-3xl">{{ $target->type->icon() }}</span>
        <div>
            <h1 class="text-xl font-bold">{{ $target->title }}</h1>
            <p class="text-sm text-slate-500">{{ $target->type->label() }}</p>
            <p class="text-xs text-slate-400 mt-1">{{ $target->subtitle() }}</p>
        </div>
    </div>

    @if ($target->description)
        <p class="mt-4 text-sm text-slate-600">{{ $target->description }}</p>
    @endif

    <div class="mt-6">
        <div class="flex justify-between text-sm mb-2">
            <span class="font-medium">Progress</span>
            <span class="font-bold text-teal-700">{{ $target->progressPercent() }}%</span>
        </div>
        <div class="h-3 rounded-full bg-slate-100 overflow-hidden">
            <div class="h-full rounded-full {{ $target->progressPercent() >= 100 ? 'bg-emerald-500' : 'bg-teal-500' }}" style="width: {{ $target->progressPercent() }}%"></div>
        </div>
        <div class="mt-3 flex justify-between text-sm text-slate-600">
            <span>Achieved: <strong>{{ $target->formattedAchieved() }}</strong></span>
            <span>Goal: <strong>{{ $target->formattedTarget() }}</strong></span>
        </div>
    </div>

    <dl class="mt-6 grid grid-cols-2 gap-3 text-sm">
        <div class="rounded-lg bg-slate-50 p-3">
            <dt class="text-xs text-slate-500">Status</dt>
            <dd class="font-medium capitalize">{{ $target->status }}</dd>
        </div>
        <div class="rounded-lg bg-slate-50 p-3">
            <dt class="text-xs text-slate-500">Assigned by</dt>
            <dd class="font-medium">{{ $target->assigner->name }}</dd>
        </div>
        @if ($target->product_name)
            <div class="rounded-lg bg-slate-50 p-3 col-span-2">
                <dt class="text-xs text-slate-500">Product</dt>
                <dd class="font-medium">{{ $target->product_name }}</dd>
            </div>
        @endif
        @if ($target->area_name)
            <div class="rounded-lg bg-slate-50 p-3 col-span-2">
                <dt class="text-xs text-slate-500">Area</dt>
                <dd class="font-medium">{{ $target->area_name }}</dd>
            </div>
        @endif
        @if ($target->periodLabel())
            <div class="rounded-lg bg-slate-50 p-3 col-span-2">
                <dt class="text-xs text-slate-500">Period</dt>
                <dd class="font-medium">{{ $target->periodLabel() }}</dd>
            </div>
        @endif
    </dl>
</div>
@endsection
