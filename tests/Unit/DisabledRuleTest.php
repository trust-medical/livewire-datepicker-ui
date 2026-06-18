<?php

declare(strict_types=1);

use TrustMedical\LivewireDatepickerUi\Domain\Services\DisabledRule;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateRange;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateTimeValue;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateValue;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\TimeValue;

it('disables nothing by default', function () {
    $rule = DisabledRule::none();

    expect($rule->isDateDisabled(new DateValue(2026, 6, 18)))->toBeFalse()
        ->and($rule->isTimeDisabled(new TimeValue(9, 0)))->toBeFalse();
});

it('disables dates outside the min/max range', function () {
    $rule = new DisabledRule(new DateRange(
        DateTimeValue::fromComponents(2026, 6, 10),
        DateTimeValue::fromComponents(2026, 6, 20),
    ));

    expect($rule->isDateDisabled(new DateValue(2026, 6, 9)))->toBeTrue()
        ->and($rule->isDateDisabled(new DateValue(2026, 6, 10)))->toBeFalse()
        ->and($rule->isDateDisabled(new DateValue(2026, 6, 20)))->toBeFalse()
        ->and($rule->isDateDisabled(new DateValue(2026, 6, 21)))->toBeTrue();
});

it('disables explicit dates and weekdays', function () {
    $rule = new DisabledRule(
        disabledDates: [new DateValue(2026, 6, 18)],
        disabledWeekdays: [0, 6], // Sunday + Saturday
    );

    expect($rule->isDateDisabled(new DateValue(2026, 6, 18)))->toBeTrue() // explicit
        ->and($rule->isDateDisabled(new DateValue(2026, 6, 13)))->toBeTrue() // Saturday
        ->and($rule->isDateDisabled(new DateValue(2026, 6, 14)))->toBeTrue() // Sunday
        ->and($rule->isDateDisabled(new DateValue(2026, 6, 15)))->toBeFalse(); // Monday
});

it('enforces minute-step alignment', function () {
    $rule = new DisabledRule(minuteStep: 15);

    expect($rule->isTimeDisabled(new TimeValue(9, 0)))->toBeFalse()
        ->and($rule->isTimeDisabled(new TimeValue(9, 15)))->toBeFalse()
        ->and($rule->isTimeDisabled(new TimeValue(9, 30)))->toBeFalse()
        ->and($rule->isTimeDisabled(new TimeValue(9, 7)))->toBeTrue();
});

it('disables time windows and time-of-day bounds', function () {
    $rule = new DisabledRule(
        disabledTimes: [['from' => new TimeValue(12, 0), 'to' => new TimeValue(13, 0)]],
        minTime: new TimeValue(9, 0),
        maxTime: new TimeValue(17, 0),
    );

    expect($rule->isTimeDisabled(new TimeValue(8, 59)))->toBeTrue() // before min
        ->and($rule->isTimeDisabled(new TimeValue(17, 1)))->toBeTrue() // after max
        ->and($rule->isTimeDisabled(new TimeValue(12, 30)))->toBeTrue() // lunch window
        ->and($rule->isTimeDisabled(new TimeValue(10, 0)))->toBeFalse();
});

it('combines date and time rules for datetime values', function () {
    $rule = new DisabledRule(
        range: new DateRange(
            DateTimeValue::fromComponents(2026, 6, 18, 9, 0),
            DateTimeValue::fromComponents(2026, 6, 18, 17, 0),
        ),
    );

    expect($rule->isDateTimeDisabled(DateTimeValue::fromComponents(2026, 6, 18, 8, 0)))->toBeTrue()
        ->and($rule->isDateTimeDisabled(DateTimeValue::fromComponents(2026, 6, 18, 12, 0)))->toBeFalse()
        ->and($rule->isDateTimeDisabled(DateTimeValue::fromComponents(2026, 6, 19, 12, 0)))->toBeTrue();
});
