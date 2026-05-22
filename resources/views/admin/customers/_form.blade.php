<div>
    <label class="block text-sm font-medium text-slate-700">Customer type *</label>
    <select name="type" required class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5">
        @foreach ($customerTypes as $type)
            <option value="{{ $type->value }}" @selected(old('type', $customer?->type?->value) === $type->value)>
                {{ $type->icon() }} {{ $type->label() }}
            </option>
        @endforeach
    </select>
    @error('type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">Name *</label>
    <input type="text" name="name" value="{{ old('name', $customer?->name) }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5">
    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">Contact person</label>
    <input type="text" name="contact_person" value="{{ old('contact_person', $customer?->contact_person) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5" placeholder="For hospitals, clinics, distributors">
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">Specialty</label>
    <input type="text" name="specialty" value="{{ old('specialty', $customer?->specialty) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5" placeholder="For doctors">
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label class="block text-sm font-medium text-slate-700">Phone</label>
        <input type="text" name="phone" value="{{ old('phone', $customer?->phone) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Email</label>
        <input type="email" name="email" value="{{ old('email', $customer?->email) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5">
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">Address</label>
    <textarea name="address" rows="2" class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5">{{ old('address', $customer?->address) }}</textarea>
</div>

<div class="grid gap-4 sm:grid-cols-3">
    <div>
        <label class="block text-sm font-medium text-slate-700">City</label>
        <input type="text" name="city" value="{{ old('city', $customer?->city) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">State</label>
        <input type="text" name="state" value="{{ old('state', $customer?->state) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Pincode</label>
        <input type="text" name="pincode" value="{{ old('pincode', $customer?->pincode) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5">
    </div>
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label class="block text-sm font-medium text-slate-700">Latitude</label>
        <input type="text" name="latitude" value="{{ old('latitude', $customer?->latitude) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Longitude</label>
        <input type="text" name="longitude" value="{{ old('longitude', $customer?->longitude) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5">
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-slate-700">Notes</label>
    <textarea name="notes" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5">{{ old('notes', $customer?->notes) }}</textarea>
</div>

<label class="flex items-center gap-2 text-sm text-slate-700">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $customer?->is_active ?? true)) class="rounded border-slate-300">
    Active in CRM
</label>
