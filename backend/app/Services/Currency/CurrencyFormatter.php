<?php

namespace App\Services\Currency;

class CurrencyFormatter
{
    public static function format(float $amount, string $currency = 'GHS', string $locale = 'en_GH'): string
    {
        // Simple formatter - centralized place for future localization
        return number_format($amount, 2);
    }
}
