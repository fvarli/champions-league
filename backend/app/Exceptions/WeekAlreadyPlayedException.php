<?php

namespace App\Exceptions;

use App\Exceptions\Contracts\ProvidesHttpStatus;
use RuntimeException;

class WeekAlreadyPlayedException extends RuntimeException implements ProvidesHttpStatus
{
    public static function for(int $week): self
    {
        return new self("Week {$week} has already been fully played.");
    }

    public function httpStatus(): int
    {
        return 409;
    }
}
