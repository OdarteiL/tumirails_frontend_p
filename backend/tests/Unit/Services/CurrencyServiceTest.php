<?php

namespace Tests\Unit\Services;

use App\Services\CurrencyService;
use Tests\TestCase;

class CurrencyServiceTest extends TestCase
{
    public function test_format_returns_string()
    {
        $svc = new CurrencyService();
        $out = $svc->format(123.45, 'USD');
        $this->assertIsString($out);
        $this->assertStringContainsString('123', $out);
    }
}
