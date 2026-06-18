<?php

declare(strict_types=1);

namespace TrustMedical\LivewireDatepickerUi\Domain\Enums;

use TrustMedical\LivewireDatepickerUi\Domain\Exceptions\InvalidConfigurationException;

/**
 * The picking modes the package supports.
 *
 * `Month` selects a year + month only (the value's day is fixed to the 1st), the
 * HTML `type="month"` equivalent.
 */
enum PickerMode: string
{
    case Date = 'date';
    case Time = 'time';
    case DateTime = 'datetime';
    case Month = 'month';

    public static function fromString(string $value): self
    {
        return self::tryFrom(strtolower(trim($value)))
            ?? throw InvalidConfigurationException::unknownMode($value);
    }

    public function hasDate(): bool
    {
        return $this !== self::Time;
    }

    public function hasTime(): bool
    {
        return $this === self::Time || $this === self::DateTime;
    }

    public function isMonth(): bool
    {
        return $this === self::Month;
    }
}
