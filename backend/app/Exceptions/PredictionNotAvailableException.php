<?php

namespace App\Exceptions;

use App\Exceptions\Contracts\ProvidesHttpStatus;
use RuntimeException;

class PredictionNotAvailableException extends RuntimeException implements ProvidesHttpStatus
{
    public static function needsMorePlayedFixtures(int $played, int $required): self
    {
        return new self(
            "Championship prediction needs at least {$required} played fixtures; only {$played} have been played."
        );
    }

    public function httpStatus(): int
    {
        return 422;
    }
}
