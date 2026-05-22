@extends('layouts.app')

@section('title', 'AI Reporting')

@section('content')
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold">AI reporting</h1>
        <p class="text-slate-600">OpenAI-powered insights for visits, performance, and doctor engagement</p>
    </div>
    <a href="{{ route('dashboard') }}" class="rounded-lg border px-4 py-2 text-sm hover:bg-slate-50">← Dashboard</a>
</div>

@if (! $aiAvailable)
    <div class="mt-6 rounded-xl border border-amber-300 bg-amber-50 p-5">
        <p class="font-semibold text-amber-900">OpenAI not configured</p>
        <p class="mt-1 text-sm text-amber-800">Add these to your <code class="bg-amber-100 px-1 rounded">.env</code> file:</p>
        <pre class="mt-3 overflow-x-auto rounded-lg bg-white p-3 text-xs text-slate-700">OPENAI_API_KEY=sk-...
OPENAI_ENABLED=true
OPENAI_MODEL=gpt-4o-mini</pre>
    </div>
@else
    <p class="mt-4 text-sm text-emerald-700">✓ OpenAI connected ({{ config('openai.model') }})</p>

    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($reportTypes as $type)
            <div class="rounded-2xl border bg-white p-5 shadow-sm">
                <p class="text-2xl">{{ $type->icon() }}</p>
                <h2 class="mt-2 font-semibold text-slate-900">{{ $type->label() }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $type->description() }}</p>
                <form method="POST" action="{{ route('admin.ai-reports.generate') }}" class="mt-4 space-y-2">
                    @csrf
                    <input type="hidden" name="type" value="{{ $type->value }}">
                    <select name="user_id" class="w-full rounded-lg border px-3 py-2 text-sm">
                        <option value="">All MRs (company-wide)</option>
                        @foreach ($representatives as $rep)
                            <option value="{{ $rep->id }}">{{ $rep->name }}</option>
                        @endforeach
                    </select>
                    <label class="flex items-center gap-2 text-xs text-slate-600">
                        <input type="checkbox" name="force" value="1" class="rounded">
                        Regenerate if today's report exists
                    </label>
                    <button type="submit" class="w-full rounded-lg bg-violet-600 py-2 text-sm font-medium text-white hover:bg-violet-700">
                        Generate report
                    </button>
                </form>
            </div>
        @endforeach
    </div>
@endif

<section class="mt-10">
    <h2 class="text-lg font-semibold text-slate-800">Report history</h2>
    @if ($reports->isEmpty())
        <p class="mt-4 text-sm text-slate-500">No AI reports yet. Generate your first report above.</p>
    @else
        <div class="mt-4 space-y-2">
            @foreach ($reports as $report)
                <a href="{{ route('admin.ai-reports.show', $report) }}" class="flex items-center justify-between rounded-xl border bg-white p-4 hover:border-violet-300">
                    <div>
                        <p class="font-medium">{{ $report->type->icon() }} {{ $report->title }}</p>
                        <p class="text-xs text-slate-500">
                            {{ $report->report_date->format('d M Y') }}
                            @if ($report->user) · {{ $report->user->name }} @endif
                            · by {{ $report->generator->name }}
                        </p>
                    </div>
                    <span class="text-violet-600">→</span>
                </a>
            @endforeach
        </div>
        <div class="mt-4">{{ $reports->links() }}</div>
    @endif
</section>
@endsection
