<?php

namespace App\Exceptions;

use App\Exceptions\Contracts\ProvidesHttpStatus;
use RuntimeException;

class FixtureGenerationException extends RuntimeException implements ProvidesHttpStatus
{
    private function __construct(string $message, private readonly int $status)
    {
        parent::__construct($message);
    }

    public static function invalidTeamCount(int $actual): self
    {
        return new self("Fixture generation requires exactly 4 teams, {$actual} given.", 422);
    }

    public static function alreadyGenerated(): self
    {
        return new self('Fixtures have already been generated.', 409);
    }

    public function httpStatus(): int
    {
        return $this->status;
    }
}
