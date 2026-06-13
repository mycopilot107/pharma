@php
    $user = $user ?? null;
    $isEdit = (bool) $user;
@endphp

<div>
    <label class="block text-sm font-medium text-slate-700" for="name">Full name *</label>
    <input type="text" name="name" id="name" value="{{ old('name', $user?->name) }}" required
        class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-teal-500 focus:ring-teal-500">
    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div>
    <label class="block text-sm font-medium text-slate-700" for="email">Email (login) *</label>
    <input type="email" name="email" id="email" value="{{ old('email', $user?->email) }}" required
        class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-teal-500 focus:ring-teal-500">
    @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div>
    <label class="block text-sm font-medium text-slate-700" for="phone">Phone</label>
    <input type="text" name="phone" id="phone" value="{{ old('phone', $user?->phone) }}"
        class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-teal-500 focus:ring-teal-500">
    @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div>
    <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user?->is_active ?? true))
            class="rounded border-slate-300">
        Active (can log in to MR app)
    </label>
</div>

<div>
    <label class="block text-sm font-medium text-slate-700" for="password">
        {{ $isEdit ? 'New 4-digit PIN (leave blank to keep current)' : '4-digit PIN *' }}
    </label>
    <input type="tel" name="password" id="password"
        inputmode="numeric" pattern="[0-9]{4}" maxlength="4"
        placeholder="e.g. 1234"
        {{ $isEdit ? '' : 'required' }}
        class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 tracking-widest focus:border-teal-500 focus:ring-teal-500">
    <p class="mt-1 text-xs text-slate-400">Exactly 4 numbers — the MR uses this PIN to log in on the app.</p>
    @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div>
    <label class="block text-sm font-medium text-slate-700" for="password_confirmation">
        Confirm PIN{{ $isEdit ? '' : ' *' }}
    </label>
    <input type="tel" name="password_confirmation" id="password_confirmation"
        inputmode="numeric" pattern="[0-9]{4}" maxlength="4"
        placeholder="repeat PIN"
        {{ $isEdit ? '' : 'required' }}
        class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 tracking-widest focus:border-teal-500 focus:ring-teal-500">
</div>
