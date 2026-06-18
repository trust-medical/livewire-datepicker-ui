<?php

declare(strict_types=1);

namespace TrustMedical\LivewireDatepickerUi\Domain\Services;

use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\CalendarDay;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\CalendarMonth;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateValue;

/**
 * Builds the 6×7 calendar grid for a given month.
 *
 * Always emits 42 cells (six weeks) so the popover height is stable as the user
 * navigates between months. Leading/trailing days from adjacent months are
 * flagged `isOutsideMonth`. The same algorithm runs client-side for navigation.
 */
final class CalendarGridBuilder
{
    private const TOTAL_CELLS = 42;

    public function build(
        int $year,
        int $month,
        int $firstDayOfWeek,
        DateValue $today,
        ?DateValue $selected,
        ?DisabledRule $rule = null,
    ): CalendarMonth {
        $rule ??= DisabledRule::none();
        $firstDayOfWeek = (($firstDayOfWeek % 7) + 7) % 7;

        $firstOfMonth = new DateValue($year, $month, 1);
        $leadingDays = ($firstOfMonth->dayOfWeek() - $firstDayOfWeek + 7) % 7;
        $gridStart = $firstOfMonth->addDays(-$leadingDays);

        $days = [];

        for ($offset = 0; $offset < self::TOTAL_CELLS; $offset++) {
            $date = $gridStart->addDays($offset);

            $days[] = new CalendarDay(
                date: $date,
                isToday: $date->equals($today),
                isOutsideMonth: $date->month !== $month,
                isDisabled: $rule->isDateDisabled($date),
                isSelected: $selected !== null && $date->equals($selected),
                isWeekend: $date->isWeekend(),
            );
        }

        return new CalendarMonth($year, $month, $days, $firstDayOfWeek);
    }
}
