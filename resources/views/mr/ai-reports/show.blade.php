@extends('layouts.mr')

@section('title', $report->title)

@section('mr-content')
<a href="{{ route('mr.ai-reports.index') }}" class="text-sm text-violet-700">← AI reports</a>

<h1 class="mt-4 text-xl font-bold">{{ $report->type->icon() }} {{ $report->title }}</h1>
<p class="text-xs text-slate-500">{{ $report->report_date->format('d M Y') }}</p>

<div class="mt-6 rounded-xl border bg-white p-4">
    @include('partials.ai-report-content', ['report' => $report])
</div>
@endsection
