@extends('layouts.mr')

@section('title', 'AI Reports')

@section('mr-content')
<h1 class="text-xl font-bold">AI reports</h1>
<p class="text-sm text-slate-500">Daily insights and performance analysis for your territory</p>

@if (! $aiAvailable)
    <p class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
        AI reporting is not enabled yet. Ask your company admin to configure OpenAI.
    </p>
@else
    <div class="mt-6 space-y-3">
        @foreach ([\App\Enums\AiReportType::DailyReport, \App\Enums\AiReportType::PerformanceAnalysis, \App\Enums\AiReportType::SmartRecommendations] as $type)
            <div class="rounded-xl border bg-white p-4">
                <p class="font-medium">{{ $type->icon() }} {{ $type->label() }}</p>
                <p class="text-xs text-slate-500 mt-1">{{ $type->description() }}</p>
                <form method="POST" action="{{ route('mr.ai-reports.generate') }}" class="mt-3 flex gap-2">
                    @csrf
                    <input type="hidden" name="type" value="{{ $type->value }}">
                    <button type="submit" class="flex-1 rounded-lg bg-violet-600 py-2 text-sm font-medium text-white">Generate</button>
                </form>
            </div>
        @endforeach
    </div>
@endif

@if ($reports->isNotEmpty())
<div class="mt-8">
    <h2 class="text-sm font-semibold text-slate-700">Your reports</h2>
    @foreach ($reports as $report)
        <a href="{{ route('mr.ai-reports.show', $report) }}" class="mt-2 block rounded-lg border bg-white p-3 text-sm hover:border-violet-300">
            {{ $report->type->icon() }} {{ $report->title }}
            <span class="text-slate-400">· {{ $report->created_at->diffForHumans() }}</span>
        </a>
    @endforeach
    {{ $reports->links() }}
</div>
@endif
@endsection
