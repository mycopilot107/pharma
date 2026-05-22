<?php

use App\Support\Currency;

if (! function_exists('currency_code')) {
    function currency_code(): string
    {
        return Currency::code();
    }
}

if (! function_exists('currency_symbol')) {
    function currency_symbol(?string $code = null): string
    {
        return Currency::symbol($code);
    }
}

if (! function_exists('format_money')) {
    function format_money(float|int|string|null $amount, ?string $code = null): string
    {
        return Currency::format($amount, $code);
    }
}

if (! function_exists('google_maps_api_key')) {
    function google_maps_api_key(): ?string
    {
        return app(\App\Services\PlatformSettingsService::class)->googleMapsApiKey();
    }
}

if (! function_exists('mongodb_dsn')) {
    /**
     * Build MongoDB DSN from MONGO_DB_* env vars, or use MONGODB_URI when set.
     */
    function mongodb_dsn(): string
    {
        if ($uri = env('MONGODB_URI')) {
            return $uri;
        }

        $host = env('MONGO_DB_HOST', '127.0.0.1');
        $port = env('MONGO_DB_PORT', '27017');
        $username = env('MONGO_DB_USERNAME');
        $password = env('MONGO_DB_PASSWORD');

        // MongoDB requires a username when embedding credentials in the URI.
        if (filled($username)) {
            $user = rawurlencode((string) $username);
            $pass = rawurlencode((string) ($password ?? ''));

            return "mongodb://{$user}:{$pass}@{$host}:{$port}";
        }

        return "mongodb://{$host}:{$port}";
    }
}

if (! function_exists('mongodb_database')) {
    function mongodb_database(): string
    {
        return env('MONGO_DB_DATABASE')
            ?: env('MONGODB_DATABASE', 'medrep_fleet_tracking');
    }
}
