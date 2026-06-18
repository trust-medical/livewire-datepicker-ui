<?php

declare(strict_types=1);

namespace TrustMedical\LivewireDatepickerUi\Domain\ValueObjects;

use DateTimeInterface;
use InvalidArgumentException;

/**
 * An immutable "civil" calendar date (year / month / day) with no time and no
 * timezone.
 *
 * All arithmetic is performed on the integer components so a date-only value can
 * never shift across a day boundary the way `new Date('2026-06-18')` would when
 * interpreted as UTC. Weekday computation uses Zeller's congruence — pure
 * integer math, no timestamps, no `DateTime`.
 */
final class DateValue
{
    public function __construct(
        public readonly int $year,
        public readonly int $month,
        public readonly int $day,
    ) {
        if ($month < 1 || $month > 12) {
            throw new InvalidArgumentException("Month must be between 1 and 12, got {$month}.");
        }

        $maxDay = self::daysInMonth($year, $month);

        if ($day < 1 || $day > $maxDay) {
            throw new InvalidArgumentException("Day must be between 1 and {$maxDay} for {$year}-{$month}, got {$day}.");
        }
    }

    public static function fromComponents(int $year, int $month, int $day): self
    {
        return new self($year, $month, $day);
    }

    /** @param array{year: int, month: int, day: int} $data */
    public static function fromArray(array $data): self
    {
        return new self($data['year'], $data['month'], $data['day']);
    }

    public static function fromDateTimeInterface(DateTimeInterface $date): self
    {
        return new self(
            (int) $date->format('Y'),
            (int) $date->format('n'),
            (int) $date->format('j'),
        );
    }

    public static function isLeapYear(int $year): bool
    {
        return ($year % 4 === 0 && $year % 100 !== 0) || $year % 400 === 0;
    }

    public static function daysInMonth(int $year, int $month): int
    {
        $days = [31, self::isLeapYear($year) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

        return $days[$month - 1] ?? 31;
    }

    /**
     * Day of week as 0 (Sunday) .. 6 (Saturday) — matches JavaScript's
     * `Date.prototype.getDay()` and PHP's `w` token.
     */
    public function dayOfWeek(): int
    {
        $month = $this->month;
        $year = $this->year;

        if ($month < 3) {
            $month += 12;
            $year -= 1;
        }

        $k = $year % 100;
        $j = intdiv($year, 100);

        $h = ($this->day
            + intdiv(13 * ($month + 1), 5)
            + $k + intdiv($k, 4)
            + intdiv($j, 4) + 5 * $j) % 7;

        // Zeller yields 0=Saturday..6=Friday; convert to 0=Sunday..6=Saturday.
        return ($h + 6) % 7;
    }

    /** ISO-8601 day of week: 1 (Monday) .. 7 (Sunday). */
    public function isoDayOfWeek(): int
    {
        $weekday = $this->dayOfWeek();

        return $weekday === 0 ? 7 : $weekday;
    }

    public function isWeekend(): bool
    {
        $weekday = $this->dayOfWeek();

        return $weekday === 0 || $weekday === 6;
    }

    public function equals(self $other): bool
    {
        return $this->year === $other->year
            && $this->month === $other->month
            && $this->day === $other->day;
    }

    public function compareTo(self $other): int
    {
        return [$this->year, $this->month, $this->day] <=> [$other->year, $other->month, $other->day];
    }

    public function isBefore(self $other): bool
    {
        return $this->compareTo($other) < 0;
    }

    public function isAfter(self $other): bool
    {
        return $this->compareTo($other) > 0;
    }

    public function addDays(int $days): self
    {
        // gmmktime is UTC-based, so it has no DST and cannot shift the civil date.
        $timestamp = gmmktime(12, 0, 0, $this->month, $this->day + $days, $this->year);

        if ($timestamp === false) {
            throw new InvalidArgumentException('Date arithmetic produced an out-of-range timestamp.');
        }

        return new self(
            (int) gmdate('Y', $timestamp),
            (int) gmdate('n', $timestamp),
            (int) gmdate('j', $timestamp),
        );
    }

    public function addMonths(int $months): self
    {
        $totalMonths = $this->year * 12 + ($this->month - 1) + $months;
        $year = intdiv($totalMonths, 12);
        $month = $totalMonths % 12 + 1;

        if ($month < 1) {
            $month += 12;
            $year -= 1;
        }

        $day = min($this->day, self::daysInMonth($year, $month));

        return new self($year, $month, $day);
    }

    public function withYear(int $year): self
    {
        return new self($year, $this->month, min($this->day, self::daysInMonth($year, $this->month)));
    }

    public function withMonth(int $month): self
    {
        return new self($this->year, $month, min($this->day, self::daysInMonth($this->year, $month)));
    }

    public function withDay(int $day): self
    {
        return new self($this->year, $this->month, $day);
    }

    /** Zero-padded ISO date, e.g. "2026-06-18". */
    public function toIsoString(): string
    {
        return sprintf('%04d-%02d-%02d', $this->year, $this->month, $this->day);
    }

    /** @return array{year: int, month: int, day: int} */
    public function toArray(): array
    {
        return ['year' => $this->year, 'month' => $this->month, 'day' => $this->day];
    }
}
