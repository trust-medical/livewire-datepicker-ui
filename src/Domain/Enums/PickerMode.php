<?php

declare(strict_types=1);

namespace TrustMedical\LivewireDatepickerUi\Domain\Enums;

use TrustMedical\LivewireDatepickerUi\Domain\Exceptions\InvalidConfigurationException;

/**
 * The three picking modes the package supports.
 */
enum PickerMode: string
{
    case Date = 'date';
    case Time = 'time';
    case DateTime = 'datetime';

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
        return $this !== self::Date;
    }
}
