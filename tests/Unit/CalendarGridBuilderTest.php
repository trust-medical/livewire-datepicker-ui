<?php

declare(strict_types=1);

use TrustMedical\LivewireDatepickerUi\Domain\Services\CalendarGridBuilder;
use TrustMedical\LivewireDatepickerUi\Domain\Services\DisabledRule;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateRange;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateTimeValue;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateValue;

beforeEach(function () {
    $this->builder = new CalendarGridBuilder;
});

it('always builds a stable six-week grid', function () {
    $month = $this->builder->build(2026, 6, 0, new DateValue(2026, 6, 18), null);

    expect($month->days)->toHaveCount(42)
        ->and($month->weeks())->toHaveCount(6)
        ->and($month->weeks()[0])->toHaveCount(7);
});

it('aligns the grid to a Sunday first day of week', function () {
    // June 2026 starts on a Monday; with Sunday-first the grid starts 2026-05-31.
    $month = $this->builder->build(2026, 6, 0, new DateValue(2026, 6, 18), null);

    expect($month->days[0]->date->toIsoString())->toBe('2026-05-31')
        ->and($month->days[0]->isOutsideMonth)->toBeTrue()
        ->and($month->weekdayOrder())->toBe([0, 1, 2, 3, 4, 5, 6]);
});

it('aligns the grid to a Monday first day of week', function () {
    // With Monday-first the grid starts on 2026-06-01 itself (a Monday).
    $month = $this->builder->build(2026, 6, 1, new DateValue(2026, 6, 18), null);

    expect($month->days[0]->date->toIsoString())->toBe('2026-06-01')
        ->and($month->weekdayOrder())->toBe([1, 2, 3, 4, 5, 6, 0]);
});

it('flags today, selected, outside-month and disabled days', function () {
    $rule = new DisabledRule(new DateRange(
        DateTimeValue::fromComponents(2026, 6, 10),
        null,
    ));

    $month = $this->builder->build(
        2026,
        6,
        0,
        today: new DateValue(2026, 6, 18),
        selected: new DateValue(2026, 6, 18),
        rule: $rule,
    );

    $byDate = [];
    foreach ($month->days as $day) {
        $byDate[$day->date->toIsoString()] = $day;
    }

    expect($byDate['2026-06-18']->isToday)->toBeTrue()
        ->and($byDate['2026-06-18']->isSelected)->toBeTrue()
        ->and($byDate['2026-05-31']->isOutsideMonth)->toBeTrue()
        ->and($byDate['2026-06-05']->isDisabled)->toBeTrue() // before the min
        ->and($byDate['2026-06-18']->isDisabled)->toBeFalse();
});
