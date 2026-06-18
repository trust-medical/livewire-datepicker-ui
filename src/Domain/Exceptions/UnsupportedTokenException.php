<?php

declare(strict_types=1);

namespace TrustMedical\LivewireDatepickerUi\Domain\Exceptions;

/**
 * Thrown when a format string contains a token the package does not support.
 */
final class UnsupportedTokenException extends DatepickerException
{
    public static function forToken(string $token): self
    {
        return new self(sprintf(
            'The format token "%s" is not supported. See the README for the supported token set.',
            $token,
        ));
    }
}
