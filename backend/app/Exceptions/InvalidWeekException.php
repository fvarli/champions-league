<?php

namespace App\Exceptions;

use RuntimeException;

class InvalidWeekException extends RuntimeException
{
    public static function for(int $week): self
    {
        return new self("Week {$week} does not exist in the current schedule.");
    }
}
