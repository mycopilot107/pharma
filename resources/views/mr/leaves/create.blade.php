@extends('layouts.mr')

@section('title', 'Apply Leave')

@section('mr-content')
<a href="{{ route('mr.leaves.index') }}" class="text-sm text-teal-700">← Back</a>

<h1 class="mt-2 text-xl font-bold">Apply for leave</h1>

<form method="POST" action="{{ route('mr.leaves.store') }}" class="mt-6 space-y-4" id="leave-form">
    @csrf

    <div>
        <label class="block text-sm font-medium text-slate-700">Leave type</label>
        <select name="leave_type" required class="mt-1 w-full rounded-lg border px-3 py-2">
            @foreach ($leaveTypes as $type)
                <option value="{{ $type->value }}" @selected(old('leave_type') === $type->value)>
                    {{ $type->icon() }} {{ $type->label() }}
                    @if (($balance[$type->value]['remaining'] ?? null) !== null)
                        ({{ $balance[$type->value]['remaining'] }} left)
                    @endif
                </option>
            @endforeach
        </select>
        @error('leave_type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-sm font-medium text-slate-700">From</label>
            <input type="date" name="start_date" value="{{ old('start_date') }}" required
                min="{{ today()->toDateString() }}" class="mt-1 w-full rounded-lg border px-3 py-2">
            @error('start_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">To</label>
            <input type="date" name="end_date" value="{{ old('end_date') }}" required
                min="{{ today()->toDateString() }}" class="mt-1 w-full rounded-lg border px-3 py-2">
            @error('end_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" name="is_half_day" value="1" id="is_half_day" @checked(old('is_half_day')) class="rounded">
        Half day only
    </label>

    <div id="half-day-wrap" class="{{ old('is_half_day') ? '' : 'hidden' }}">
        <label class="block text-sm font-medium text-slate-700">Half day period</label>
        <select name="half_day_period" class="mt-1 w-full rounded-lg border px-3 py-2">
            <option value="first_half" @selected(old('half_day_period') === 'first_half')>First half</option>
            <option value="second_half" @selected(old('half_day_period') === 'second_half')>Second half</option>
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700">Reason</label>
        <textarea name="reason" rows="4" required minlength="10" maxlength="2000"
            class="mt-1 w-full rounded-lg border px-3 py-2" placeholder="Brief reason for leave...">{{ old('reason') }}</textarea>
        @error('reason')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <button type="submit" class="w-full rounded-lg bg-teal-600 py-3 font-medium text-white hover:bg-teal-700">
        Submit for approval
    </button>
</form>

<script>
document.getElementById('is_half_day')?.addEventListener('change', function() {
    const wrap = document.getElementById('half-day-wrap');
    const end = document.querySelector('[name=end_date]');
    if (this.checked) {
        wrap.classList.remove('hidden');
        if (end && document.querySelector('[name=start_date]').value) {
            end.value = document.querySelector('[name=start_date]').value;
        }
    } else {
        wrap.classList.add('hidden');
    }
});
</script>
@endsection
