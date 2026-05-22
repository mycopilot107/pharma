<div class="grid gap-4 sm:grid-cols-3">
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
        <p class="text-sm font-medium text-emerald-800">Completed targets</p>
        <p class="mt-2 text-3xl font-bold text-emerald-900">{{ $targetStats['completed'] }}</p>
        <p class="mt-1 text-xs text-emerald-700">Goals achieved</p>
    </div>
    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
        <p class="text-sm font-medium text-amber-800">Pending targets</p>
        <p class="mt-2 text-3xl font-bold text-amber-900">{{ $targetStats['pending'] }}</p>
        <p class="mt-1 text-xs text-amber-700">Still in progress</p>
    </div>
    <div class="rounded-2xl border border-indigo-200 bg-indigo-50 p-5 shadow-sm">
        <p class="text-sm font-medium text-indigo-800">Performance</p>
        <p class="mt-2 text-3xl font-bold text-indigo-900">{{ $targetStats['performance_percent'] }}%</p>
        <p class="mt-1 text-xs text-indigo-700">Overall achievement rate</p>
        <div class="mt-3 h-2 rounded-full bg-indigo-100 overflow-hidden">
            <div class="h-full rounded-full bg-indigo-500 transition-all" style="width: {{ min(100, $targetStats['performance_percent']) }}%"></div>
        </div>
    </div>
</div>
