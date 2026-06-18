<?php

declare(strict_types=1);

namespace TrustMedical\LivewireDatepickerUi\Domain\ValueObjects;

/**
 * A fully built month grid: a flat, week-aligned list of {@see CalendarDay}s
 * (always a whole number of weeks) plus the month it represents.
 */
final class CalendarMonth
{
    /**
     * @param  list<CalendarDay>  $days
     */
    public function __construct(
        public readonly int $year,
        public readonly int $month,
        public readonly array $days,
        public readonly int $firstDayOfWeek,
    ) {}

    /**
     * The grid grouped into weeks of 7 days.
     *
     * @return list<list<CalendarDay>>
     */
    public function weeks(): array
    {
        return array_chunk($this->days, 7);
    }

    /**
     * The weekday header order (0..6) starting at the configured first day.
     *
     * @return list<int>
     */
    public function weekdayOrder(): array
    {
        $order = [];

        for ($offset = 0; $offset < 7; $offset++) {
            $order[] = ($this->firstDayOfWeek + $offset) % 7;
        }

        return $order;
    }

    /**
     * @return array{
     *     year: int,
     *     month: int,
     *     firstDayOfWeek: int,
     *     weekdayOrder: list<int>,
     *     days: list<array<string, mixed>>
     * }
     */
    public function toArray(): array
    {
        return [
            'year' => $this->year,
            'month' => $this->month,
            'firstDayOfWeek' => $this->firstDayOfWeek,
            'weekdayOrder' => $this->weekdayOrder(),
            'days' => array_map(static fn (CalendarDay $day): array => $day->toArray(), $this->days),
        ];
    }
}
