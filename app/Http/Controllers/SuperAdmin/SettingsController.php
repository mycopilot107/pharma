<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\PlatformSettingsService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct(protected PlatformSettingsService $settings) {}

    public function edit()
    {
        return view('super-admin.settings.edit', [
            'googleMapsKeyMasked' => $this->settings->googleMapsApiKeyMasked(),
            'hasGoogleMapsKey' => filled($this->settings->googleMapsApiKey()),
            'envFallback' => filled(config('google.maps_api_key')),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'google_maps_api_key' => ['nullable', 'string', 'max:255'],
            'clear_google_maps_api_key' => ['boolean'],
        ]);

        if ($request->boolean('clear_google_maps_api_key')) {
            $this->settings->set('google_maps_api_key', null);
        } elseif (filled($validated['google_maps_api_key'] ?? null)) {
            $this->settings->set('google_maps_api_key', trim($validated['google_maps_api_key']));
        }

        return redirect()
            ->route('super-admin.settings.edit')
            ->with('success', 'Platform settings saved.');
    }
}
