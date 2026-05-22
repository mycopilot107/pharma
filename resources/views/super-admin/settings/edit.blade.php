@extends('layouts.app')

@section('title', 'Platform Settings')

@section('content')
<div>
    <h1 class="text-2xl font-bold">Platform settings</h1>
    <p class="text-slate-600">Configure integrations used across all companies</p>
</div>

<form method="POST" action="{{ route('super-admin.settings.update') }}" class="mt-8 max-w-xl space-y-6">
    @csrf
    @method('PUT')

    <div class="rounded-xl border bg-white p-6 shadow-sm">
        <h2 class="font-semibold text-slate-900">Google Maps API</h2>
        <p class="mt-1 text-sm text-slate-600">
            Used for live tracking maps (admin panel) and the Flutter field app.
            Enable <strong>Maps JavaScript API</strong> and <strong>Geolocation</strong> in Google Cloud Console.
        </p>

        @if ($hasGoogleMapsKey)
            <p class="mt-3 rounded-lg bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
                Current key: <code class="font-mono">{{ $googleMapsKeyMasked }}</code>
            </p>
        @elseif ($envFallback)
            <p class="mt-3 rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-800">
                Using <code>GOOGLE_MAPS_API_KEY</code> from <code>.env</code> (no database key set).
            </p>
        @else
            <p class="mt-3 rounded-lg bg-slate-100 px-3 py-2 text-sm text-slate-600">
                No API key configured. Live tracking uses OpenStreetMap until a key is added.
            </p>
        @endif

        <div class="mt-4">
            <label class="block text-sm font-medium text-slate-700" for="google_maps_api_key">
                {{ $hasGoogleMapsKey ? 'Replace API key' : 'API key' }}
            </label>
            <input type="password" name="google_maps_api_key" id="google_maps_api_key" autocomplete="off"
                placeholder="{{ $hasGoogleMapsKey ? 'Enter new key to replace' : 'AIza...' }}"
                class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 font-mono text-sm focus:border-violet-500 focus:ring-violet-500">
            <p class="mt-1 text-xs text-slate-500">Leave blank to keep the current key.</p>
        </div>

        @if ($hasGoogleMapsKey)
            <label class="mt-4 flex items-center gap-2 text-sm text-red-700">
                <input type="checkbox" name="clear_google_maps_api_key" value="1" class="rounded border-red-300">
                Remove stored API key
            </label>
        @endif
    </div>

    <button type="submit" class="rounded-lg bg-violet-700 px-6 py-2.5 text-sm font-medium text-white hover:bg-violet-800">
        Save settings
    </button>
</form>
@endsection
