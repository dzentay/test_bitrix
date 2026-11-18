<?php

declare(strict_types=1);

namespace Vendor\Catalog\Exceptions;

use RuntimeException;

final class ValidationException extends RuntimeException
{
    public array $errors = [];

    public static function withErrors(array $errors, string $message = 'Validation failed'): self
    {
        $exception = new self($message, 422);
        $exception->errors = $errors;

        return $exception;
    }
}