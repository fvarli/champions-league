<?php

namespace App\Exceptions;

use RuntimeException;

class LeagueAlreadyCompleteException extends RuntimeException
{
    public static function make(): self
    {
        return new self('The league is already complete; there are no fixtures left to play.');
    }
}
