<?php

declare(strict_types=1);

namespace TrustMedical\LivewireDatepickerUi\Domain\ValueObjects;

/**
 * One cell of a rendered calendar grid, carrying the state flags the view and
 * the client runtime use to style and label it.
 */
final class CalendarDay
{
    public function __construct(
        public readonly DateValue $date,
        public readonly bool $isToday = false,
        public readonly bool $isOutsideMonth = false,
        public readonly bool $isDisabled = false,
        public readonly bool $isSelected = false,
        public readonly bool $isWeekend = false,
    ) {}

    /**
     * @return array{
     *     date: string,
     *     day: int,
     *     month: int,
     *     year: int,
     *     weekday: int,
     *     isToday: bool,
     *     isOutsideMonth: bool,
     *     isDisabled: bool,
     *     isSelected: bool,
     *     isWeekend: bool
     * }
     */
    public function toArray(): array
    {
        return [
            'date' => $this->date->toIsoString(),
            'day' => $this->date->day,
            'month' => $this->date->month,
            'year' => $this->date->year,
            'weekday' => $this->date->dayOfWeek(),
            'isToday' => $this->isToday,
            'isOutsideMonth' => $this->isOutsideMonth,
            'isDisabled' => $this->isDisabled,
            'isSelected' => $this->isSelected,
            'isWeekend' => $this->isWeekend,
        ];
    }
}
