<?php

namespace App\Actions\Currency;

use App\Models\Country;

class FormatCurrencyAction
{
    /**
     * Format a numeric amount into a currency string using active country by default.
     * Returns a formatted string, e.g. "GHS 1,234.56" or localized form when intl is available.
     */
    public function execute(float $amount, ?string $currencyCode = null): string
    {
        $active = Country::where('is_active', true)->first();
        $currency = $currencyCode ?? ($active->currency_code ?? 'GHS');

        // Try using intl if available
        if (extension_loaded('intl')) {
            try {
                $fmt = new \NumberFormatter('en_US', \NumberFormatter::CURRENCY);
                $formatted = $fmt->formatCurrency($amount, $currency);
                if ($formatted !== false) return $formatted;
            } catch (\Throwable $e) {
                // fall through
            }
        }

        // Fallback: simple currency code + number_format
        return sprintf('%s %s', $currency, number_format($amount, 2));
    }

    /**
     * Return numeric + formatted currency metadata.
     * Example: ['amount' => 1234.5, 'formatted' => 'GHS 1,234.50', 'currency' => 'GHS']
     */
    public function formatMeta(float $amount, ?string $currencyCode = null): array
    {
        $active = Country::where('is_active', true)->first();
        $currency = $currencyCode ?? ($active->currency_code ?? 'GHS');

        $formatted = $this->execute($amount, $currency);

        return [
            'amount' => round($amount, 2),
            'formatted' => $formatted,
            'currency' => $currency,
        ];
    }
}
