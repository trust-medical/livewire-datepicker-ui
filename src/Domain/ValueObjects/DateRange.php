<?php

declare(strict_types=1);

namespace TrustMedical\LivewireDatepickerUi\Domain\ValueObjects;

/**
 * An inclusive, optionally half-open min/max boundary.
 *
 * Works on {@see DateTimeValue} so it covers date, time and datetime modes
 * uniformly (callers wrap bare dates/times via the DateTimeValue factories).
 */
final class DateRange
{
    public function __construct(
        public readonly ?DateTimeValue $min = null,
        public readonly ?DateTimeValue $max = null,
    ) {}

    public static function unbounded(): self
    {
        return new self(null, null);
    }

    public function isBounded(): bool
    {
        return $this->min !== null || $this->max !== null;
    }

    public function isBelowMin(DateTimeValue $value): bool
    {
        return $this->min !== null && $value->isBefore($this->min);
    }

    public function isAboveMax(DateTimeValue $value): bool
    {
        return $this->max !== null && $value->isAfter($this->max);
    }

    public function contains(DateTimeValue $value): bool
    {
        return ! $this->isBelowMin($value) && ! $this->isAboveMax($value);
    }

    public function clamp(DateTimeValue $value): DateTimeValue
    {
        if ($this->isBelowMin($value) && $this->min !== null) {
            return $this->min;
        }

        if ($this->isAboveMax($value) && $this->max !== null) {
            return $this->max;
        }

        return $value;
    }
}
