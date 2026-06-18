<?php

declare(strict_types=1);

namespace TrustMedical\LivewireDatepickerUi\Domain\Services;

use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateRange;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateTimeValue;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateValue;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\TimeValue;

/**
 * Encapsulates every "is this selectable?" rule: min/max range, explicit
 * disabled dates, disabled weekdays, disabled time windows, time-of-day bounds
 * and minute-step alignment.
 *
 * The same logic is mirrored in TypeScript so the client greys out exactly what
 * the server would reject.
 */
final class DisabledRule
{
    /**
     * @param  list<DateValue>  $disabledDates
     * @param  list<int>  $disabledWeekdays  Each 0 (Sunday)..6 (Saturday).
     * @param  list<array{from: TimeValue, to: TimeValue}>  $disabledTimes
     */
    public function __construct(
        public readonly DateRange $range = new DateRange,
        public readonly array $disabledDates = [],
        public readonly array $disabledWeekdays = [],
        public readonly array $disabledTimes = [],
        public readonly int $minuteStep = 1,
        public readonly ?TimeValue $minTime = null,
        public readonly ?TimeValue $maxTime = null,
    ) {}

    public static function none(): self
    {
        return new self;
    }

    public function isDateDisabled(DateValue $date): bool
    {
        if ($this->range->min !== null && $date->isBefore($this->range->min->date)) {
            return true;
        }

        if ($this->range->max !== null && $date->isAfter($this->range->max->date)) {
            return true;
        }

        return $this->matchesDayRules($date);
    }

    public function isTimeDisabled(TimeValue $time): bool
    {
        if ($this->minTime !== null && $time->isBefore($this->minTime)) {
            return true;
        }

        if ($this->maxTime !== null && $time->isAfter($this->maxTime)) {
            return true;
        }

        if ($this->minuteStep > 1 && $time->minute % $this->minuteStep !== 0) {
            return true;
        }

        foreach ($this->disabledTimes as $window) {
            if ($time->compareTo($window['from']) >= 0 && $time->compareTo($window['to']) <= 0) {
                return true;
            }
        }

        return false;
    }

    public function isDateTimeDisabled(DateTimeValue $value): bool
    {
        if (! $this->range->contains($value)) {
            return true;
        }

        if ($this->matchesDayRules($value->date)) {
            return true;
        }

        return $this->isTimeDisabled($value->time);
    }

    public function isWeekdayDisabled(int $weekday): bool
    {
        return in_array($weekday, $this->disabledWeekdays, true);
    }

    public function isExplicitDateDisabled(DateValue $date): bool
    {
        foreach ($this->disabledDates as $disabled) {
            if ($disabled->equals($date)) {
                return true;
            }
        }

        return false;
    }

    private function matchesDayRules(DateValue $date): bool
    {
        return $this->isWeekdayDisabled($date->dayOfWeek()) || $this->isExplicitDateDisabled($date);
    }
}
