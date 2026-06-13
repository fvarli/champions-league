<?php

namespace App\Exceptions;

use App\Exceptions\Contracts\ProvidesHttpStatus;
use RuntimeException;

class InvalidWeekException extends RuntimeException implements ProvidesHttpStatus
{
    public static function for(int $week): self
    {
        return new self("Week {$week} does not exist in the current schedule.");
    }

    public function httpStatus(): int
    {
        return 422;
    }
}
