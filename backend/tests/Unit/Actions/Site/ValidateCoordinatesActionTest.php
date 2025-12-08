<?php

namespace Tests\Unit\Actions\Site;

use App\Actions\Site\ValidateCoordinatesAction;
use InvalidArgumentException;
use Tests\TestCase;

class ValidateCoordinatesActionTest extends TestCase
{
    private ValidateCoordinatesAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new ValidateCoordinatesAction();
    }

    public function test_execute_passes_with_valid_coordinates(): void
    {
        $this->action->execute(40.7128, -74.0060);
        $this->assertTrue(true); // No exception thrown
    }

    public function test_execute_passes_with_boundary_values(): void
    {
        $this->action->execute(90, 180);
        $this->action->execute(-90, -180);
        $this->action->execute(0, 0);
        $this->assertTrue(true);
    }

    public function test_execute_throws_exception_when_latitude_too_high(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Latitude must be between -90 and 90');

        $this->action->execute(91, 0);
    }

    public function test_execute_throws_exception_when_latitude_too_low(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Latitude must be between -90 and 90');

        $this->action->execute(-91, 0);
    }

    public function test_execute_throws_exception_when_longitude_too_high(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Longitude must be between -180 and 180');

        $this->action->execute(0, 181);
    }

    public function test_execute_throws_exception_when_longitude_too_low(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Longitude must be between -180 and 180');

        $this->action->execute(0, -181);
    }
}
