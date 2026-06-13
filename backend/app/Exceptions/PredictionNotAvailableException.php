<?php

namespace App\Exceptions;

use RuntimeException;

class PredictionNotAvailableException extends RuntimeException
{
    public static function needsMorePlayedFixtures(int $played, int $required): self
    {
        return new self(
            "Championship prediction needs at least {$required} played fixtures; only {$played} have been played."
        );
    }
}
