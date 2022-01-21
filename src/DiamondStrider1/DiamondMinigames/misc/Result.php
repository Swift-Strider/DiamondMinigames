<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\misc;

/**
 * A boolean value with an error message attached
 */
class Result
{
    private function __construct(
        private bool $success,
        private string $error = "",
    ) {
    }

    public function success(): bool
    {
        return $this->success;
    }

    public function getError(): string
    {
        return $this->error;
    }

    public static function ok(): Result
    {
        return new self(true);
    }

    public static function error(string $message = ""): Result
    {
        return new self(false, $message);
    }
}
