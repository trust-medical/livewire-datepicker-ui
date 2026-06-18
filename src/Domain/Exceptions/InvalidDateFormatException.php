<?php

declare(strict_types=1);

namespace TrustMedical\LivewireDatepickerUi\Domain\Exceptions;

/**
 * Thrown when an input string cannot be parsed against the configured format.
 */
final class InvalidDateFormatException extends DatepickerException
{
    public static function forInput(string $input, string $format): self
    {
        return new self(sprintf(
            'Unable to parse "%s" using the format "%s".',
            $input,
            $format,
        ));
    }

    public static function outOfRange(string $component, int|string $value): self
    {
        return new self(sprintf(
            'The parsed %s value "%s" is out of range.',
            $component,
            (string) $value,
        ));
    }
}
