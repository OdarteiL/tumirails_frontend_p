<?php

namespace App\Actions\Site;

use InvalidArgumentException;

class ValidateCoordinatesAction
{
    public function execute(float $latitude, float $longitude): void
    {
        if ($latitude < -90 || $latitude > 90) {
            throw new InvalidArgumentException('Latitude must be between -90 and 90');
        }

        if ($longitude < -180 || $longitude > 180) {
            throw new InvalidArgumentException('Longitude must be between -180 and 180');
        }
    }
}
