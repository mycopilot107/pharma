<?php

namespace App\Services;

use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Cache;

class PlatformSettingsService
{
    protected const CACHE_KEY = 'platform_settings.all';

    public function get(string $key, ?string $default = null): ?string
    {
        $all = $this->all();

        return $all[$key] ?? $default;
    }

    public function set(string $key, ?string $value): void
    {
        PlatformSetting::updateOrCreate(
            ['key' => $key],
            ['value' => $value],
        );

        Cache::forget(self::CACHE_KEY);
    }

    /** @return array<string, string|null> */
    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            return PlatformSetting::query()
                ->pluck('value', 'key')
                ->all();
        });
    }

    public function googleMapsApiKey(): ?string
    {
        $key = $this->get('google_maps_api_key');

        if (filled($key)) {
            return $key;
        }

        $env = config('google.maps_api_key');

        return filled($env) ? $env : null;
    }

    public function googleMapsApiKeyMasked(): ?string
    {
        $key = $this->googleMapsApiKey();

        if (! $key) {
            return null;
        }

        if (strlen($key) <= 8) {
            return str_repeat('•', strlen($key));
        }

        return substr($key, 0, 4).str_repeat('•', max(4, strlen($key) - 8)).substr($key, -4);
    }
}
