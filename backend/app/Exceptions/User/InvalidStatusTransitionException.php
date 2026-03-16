<?php

namespace App\Exceptions\User;

use Exception;

class InvalidStatusTransitionException extends Exception
{
    // Exception thrown when trying to transition to the same status
}
