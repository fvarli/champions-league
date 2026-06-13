<?php

namespace App\Exceptions;

use App\Exceptions\Contracts\ProvidesHttpStatus;
use RuntimeException;

class LeagueAlreadyCompleteException extends RuntimeException implements ProvidesHttpStatus
{
    public static function make(): self
    {
        return new self('The league is already complete; there are no fixtures left to play.');
    }

    public function httpStatus(): int
    {
        return 409;
    }
}
