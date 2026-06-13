<?php

namespace App\Exceptions;

use App\Exceptions\Contracts\ProvidesHttpStatus;
use App\Models\Fixture;
use RuntimeException;

class FixtureAlreadyPlayedException extends RuntimeException implements ProvidesHttpStatus
{
    public static function for(Fixture $fixture): self
    {
        return new self("Fixture {$fixture->id} has already been played.");
    }

    public function httpStatus(): int
    {
        return 409;
    }
}
