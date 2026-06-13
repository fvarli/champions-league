<?php

namespace App\Exceptions;

use RuntimeException;

class WeekAlreadyPlayedException extends RuntimeException
{
    public static function for(int $week): self
    {
        return new self("Week {$week} has already been fully played.");
    }
}
