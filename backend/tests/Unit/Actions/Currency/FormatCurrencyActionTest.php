<?php

namespace Tests\Unit\Actions\Currency;

use App\Actions\Currency\FormatCurrencyAction;
use Tests\TestCase;

class FormatCurrencyActionTest extends TestCase
{
    public function test_formats_with_explicit_currency()
    {
        $fmt = new FormatCurrencyAction();
        $out = $fmt->execute(1234.56, 'USD');
        // Should contain currency code or symbol and amount
        $this->assertIsString($out);
        $this->assertStringContainsString('1,234', $out);
    }

    public function test_formats_with_default_country_when_no_intl()
    {
        $fmt = new FormatCurrencyAction();
        $out = $fmt->execute(78.9, 'GHS');
        $this->assertIsString($out);
        $this->assertStringContainsString('GHS', $out);
    }

    public function test_format_meta_returns_amount_and_formatted()
    {
        $fmt = new FormatCurrencyAction();
        $meta = $fmt->formatMeta(1500.5, 'USD');
        $this->assertIsArray($meta);
        $this->assertArrayHasKey('amount', $meta);
        $this->assertArrayHasKey('formatted', $meta);
        $this->assertArrayHasKey('currency', $meta);
        $this->assertEquals(1500.5, $meta['amount']);
    }
}
