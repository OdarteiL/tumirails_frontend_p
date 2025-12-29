<?php

namespace App\Services;

use App\Actions\Currency\FormatCurrencyAction;

class CurrencyService
{
    protected FormatCurrencyAction $formatter;

    public function __construct()
    {
        $this->formatter = new FormatCurrencyAction();
    }

    public function format(float $amount, ?string $currencyCode = null): string
    {
        return $this->formatter->execute($amount, $currencyCode);
    }
}
