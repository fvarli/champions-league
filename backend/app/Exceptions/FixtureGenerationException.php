<?php

namespace App\Exceptions;

use RuntimeException;

class FixtureGenerationException extends RuntimeException
{
    public static function invalidTeamCount(int $actual): self
    {
        return new self("Fixture generation requires exactly 4 teams, {$actual} given.");
    }

    public static function alreadyGenerated(): self
    {
        return new self('Fixtures have already been generated.');
    }
}
