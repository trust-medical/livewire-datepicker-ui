<?php

declare(strict_types=1);

use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateTimeValue;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateValue;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\TimeValue;

it('wraps a date at midnight and a time at the epoch', function () {
    $fromDate = DateTimeValue::fromDate(new DateValue(2026, 6, 18));
    $fromTime = DateTimeValue::fromTime(new TimeValue(9, 30));

    expect($fromDate->time->toIsoString())->toBe('00:00:00')
        ->and($fromTime->date->toIsoString())->toBe('1970-01-01');
});

it('compares by date then time', function () {
    $a = DateTimeValue::fromComponents(2026, 6, 18, 9, 0);
    $b = DateTimeValue::fromComponents(2026, 6, 18, 9, 30);
    $c = DateTimeValue::fromComponents(2026, 6, 19, 0, 0);

    expect($a->isBefore($b))->toBeTrue()
        ->and($b->isBefore($c))->toBeTrue()
        ->and($a->equals(DateTimeValue::fromComponents(2026, 6, 18, 9, 0, 0)))->toBeTrue();
});

it('serialises to a nested array', function () {
    $value = DateTimeValue::fromComponents(2026, 6, 18, 9, 30, 15);

    expect($value->toArray())->toBe([
        'date' => ['year' => 2026, 'month' => 6, 'day' => 18],
        'time' => ['hour' => 9, 'minute' => 30, 'second' => 15],
    ]);
});
