@if (!empty($report->detected_insights))
    <div class="mb-6 rounded-xl border border-violet-300 bg-violet-50 p-4">
        <h2 class="text-sm font-semibold text-violet-900">Key alerts</h2>
        <ul class="mt-2 space-y-1 text-sm text-violet-800">
            @foreach ($report->detected_insights as $insight)
                <li class="flex gap-2"><span>•</span><span>{{ $insight }}</span></li>
            @endforeach
        </ul>
    </div>
@endif

<article class="prose prose-slate max-w-none prose-headings:text-slate-900 prose-p:text-slate-700">
    {!! \Illuminate\Support\Str::markdown($report->content) !!}
</article>
