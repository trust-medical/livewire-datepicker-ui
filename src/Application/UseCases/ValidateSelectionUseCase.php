<?php

declare(strict_types=1);

namespace TrustMedical\LivewireDatepickerUi\Application\UseCases;

use TrustMedical\LivewireDatepickerUi\Application\DTO\ValidationResult;
use TrustMedical\LivewireDatepickerUi\Domain\Services\DisabledRule;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateTimeValue;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateValue;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\TimeValue;

/**
 * Validates a selected value against a {@see DisabledRule}, returning granular
 * reason codes. Server-side validation parity for the client-side greying-out.
 */
final class ValidateSelectionUseCase
{
    public function execute(
        DateValue|TimeValue|DateTimeValue $value,
        DisabledRule $rule,
    ): ValidationResult {
        $dateTime = $this->toDateTime($value);
        $reasons = [];

        if ($rule->range->isBelowMin($dateTime)) {
            $reasons[] = 'below_min';
        }

        if ($rule->range->isAboveMax($dateTime)) {
            $reasons[] = 'above_max';
        }

        $date = $this->datePart($value);
        if ($date !== null) {
            if ($rule->isWeekdayDisabled($date->dayOfWeek())) {
                $reasons[] = 'disabled_weekday';
            }

            if ($rule->isExplicitDateDisabled($date)) {
                $reasons[] = 'disabled_date';
            }
        }

        $time = $this->timePart($value);
        if ($time !== null && $rule->isTimeDisabled($time)) {
            $reasons[] = 'disabled_time';
        }

        return $reasons === [] ? ValidationResult::valid() : ValidationResult::invalid(array_values(array_unique($reasons)));
    }

    private function toDateTime(DateValue|TimeValue|DateTimeValue $value): DateTimeValue
    {
        return match (true) {
            $value instanceof DateValue => DateTimeValue::fromDate($value),
            $value instanceof TimeValue => DateTimeValue::fromTime($value),
            default => $value,
        };
    }

    private function datePart(DateValue|TimeValue|DateTimeValue $value): ?DateValue
    {
        return match (true) {
            $value instanceof DateValue => $value,
            $value instanceof DateTimeValue => $value->date,
            default => null,
        };
    }

    private function timePart(DateValue|TimeValue|DateTimeValue $value): ?TimeValue
    {
        return match (true) {
            $value instanceof TimeValue => $value,
            $value instanceof DateTimeValue => $value->time,
            default => null,
        };
    }
}
