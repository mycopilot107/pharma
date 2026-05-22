<?php

namespace App\Support;

use App\Models\Company;

class Currency
{
    protected static ?string $code = null;

    public static function set(?string $code): void
    {
        self::$code = self::isSupported($code) ? strtoupper($code) : null;
    }

    public static function fromCompany(?Company $company): void
    {
        self::set($company?->currency);
    }

    public static function code(): string
    {
        if (self::$code) {
            return self::$code;
        }

        return strtoupper(config('currencies.default', 'USD'));
    }

    public static function symbol(?string $code = null): string
    {
        $code = $code ? strtoupper($code) : self::code();

        return self::config($code)['symbol'] ?? $code;
    }

    public static function name(?string $code = null): string
    {
        $code = $code ? strtoupper($code) : self::code();

        return self::config($code)['name'] ?? $code;
    }

    public static function decimals(?string $code = null): int
    {
        $code = $code ? strtoupper($code) : self::code();

        return (int) (self::config($code)['decimals'] ?? 2);
    }

    public static function format(float|int|string|null $amount, ?string $code = null): string
    {
        $code = $code ? strtoupper($code) : self::code();
        $decimals = self::decimals($code);
        $symbol = self::symbol($code);
        $formatted = number_format((float) $amount, $decimals);

        return $symbol.' '.$formatted;
    }

    /** @return array<string, array{name: string, symbol: string, decimals: int}> */
    public static function supported(): array
    {
        return config('currencies.supported', []);
    }

    /** @return array<int, string> */
    public static function codes(): array
    {
        return array_keys(self::supported());
    }

    public static function isSupported(?string $code): bool
    {
        if (! $code) {
            return false;
        }

        return isset(self::supported()[strtoupper($code)]);
    }

    public static function validationRule(): string
    {
        return 'in:'.implode(',', self::codes());
    }

    protected static function config(string $code): array
    {
        return self::supported()[$code] ?? [];
    }
}
