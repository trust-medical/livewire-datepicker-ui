<?php

declare(strict_types=1);

namespace TrustMedical\LivewireDatepickerUi\Domain\Exceptions;

/**
 * Thrown when component props or package config contain invalid values.
 */
final class InvalidConfigurationException extends DatepickerException
{
    public static function unknownMode(string $value): self
    {
        return new self(sprintf(
            'Unknown picker mode "%s". Expected one of: date, time, datetime.',
            $value,
        ));
    }

    public static function invalidWeekday(int|string $value): self
    {
        return new self(sprintf(
            'Invalid weekday "%s". Expected an integer between 0 (Sunday) and 6 (Saturday).',
            (string) $value,
        ));
    }
}
