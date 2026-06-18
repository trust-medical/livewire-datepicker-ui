<?php

declare(strict_types=1);

namespace TrustMedical\LivewireDatepickerUi\Domain\ValueObjects;

use DateTimeInterface;

/**
 * An immutable pairing of a {@see DateValue} and a {@see TimeValue}.
 *
 * Used as the canonical comparable value throughout the package: date-only
 * selections wrap a midnight time, time-only selections wrap a fixed epoch date,
 * so a single comparator covers every mode.
 */
final class DateTimeValue
{
    public function __construct(
        public readonly DateValue $date,
        public readonly TimeValue $time,
    ) {}

    public static function of(DateValue $date, TimeValue $time): self
    {
        return new self($date, $time);
    }

    /** Wrap a date with a midnight time (date-only selections). */
    public static function fromDate(DateValue $date): self
    {
        return new self($date, new TimeValue(0, 0, 0));
    }

    /** Wrap a time with the Unix epoch date (time-only selections). */
    public static function fromTime(TimeValue $time): self
    {
        return new self(new DateValue(1970, 1, 1), $time);
    }

    public static function fromComponents(
        int $year,
        int $month,
        int $day,
        int $hour = 0,
        int $minute = 0,
        int $second = 0,
    ): self {
        return new self(
            new DateValue($year, $month, $day),
            new TimeValue($hour, $minute, $second),
        );
    }

    public static function fromDateTimeInterface(DateTimeInterface $value): self
    {
        return new self(
            DateValue::fromDateTimeInterface($value),
            TimeValue::fromDateTimeInterface($value),
        );
    }

    public function equals(self $other): bool
    {
        return $this->date->equals($other->date) && $this->time->equals($other->time);
    }

    public function compareTo(self $other): int
    {
        $dateComparison = $this->date->compareTo($other->date);

        return $dateComparison !== 0 ? $dateComparison : $this->time->compareTo($other->time);
    }

    public function isBefore(self $other): bool
    {
        return $this->compareTo($other) < 0;
    }

    public function isAfter(self $other): bool
    {
        return $this->compareTo($other) > 0;
    }

    public function withDate(DateValue $date): self
    {
        return new self($date, $this->time);
    }

    public function withTime(TimeValue $time): self
    {
        return new self($this->date, $time);
    }

    /** @return array{date: array{year: int, month: int, day: int}, time: array{hour: int, minute: int, second: int}} */
    public function toArray(): array
    {
        return ['date' => $this->date->toArray(), 'time' => $this->time->toArray()];
    }
}
