<?php

namespace App\Exceptions;

use App\Models\Fixture;
use RuntimeException;

class FixtureAlreadyPlayedException extends RuntimeException
{
    public static function for(Fixture $fixture): self
    {
        return new self("Fixture {$fixture->id} has already been played.");
    }
}
