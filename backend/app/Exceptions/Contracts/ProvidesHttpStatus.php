<?php

namespace App\Exceptions\Contracts;

/**
 * Domain exceptions implement this so the API layer can translate them into a
 * meaningful HTTP status without inspecting messages or exception classes.
 */
interface ProvidesHttpStatus
{
    public function httpStatus(): int;
}
