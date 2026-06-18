<?php

declare(strict_types=1);

use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateValue;

it('computes the weekday as 0=Sunday..6=Saturday', function () {
    expect((new DateValue(2026, 6, 18))->dayOfWeek())->toBe(4) // Thursday
        ->and((new DateValue(2000, 1, 1))->dayOfWeek())->toBe(6) // Saturday
        ->and((new DateValue(2024, 2, 29))->dayOfWeek())->toBe(4) // Thursday
        ->and((new DateValue(2026, 6, 14))->dayOfWeek())->toBe(0); // Sunday
});

it('computes the ISO weekday as 1=Monday..7=Sunday', function () {
    expect((new DateValue(2026, 6, 14))->isoDayOfWeek())->toBe(7) // Sunday
        ->and((new DateValue(2026, 6, 15))->isoDayOfWeek())->toBe(1); // Monday
});

it('detects leap years', function () {
    expect(DateValue::isLeapYear(2024))->toBeTrue()
        ->and(DateValue::isLeapYear(2000))->toBeTrue()
        ->and(DateValue::isLeapYear(1900))->toBeFalse()
        ->and(DateValue::isLeapYear(2023))->toBeFalse();
});

it('reports the number of days in a month', function () {
    expect(DateValue::daysInMonth(2024, 2))->toBe(29)
        ->and(DateValue::daysInMonth(2023, 2))->toBe(28)
        ->and(DateValue::daysInMonth(2026, 4))->toBe(30)
        ->and(DateValue::daysInMonth(2026, 12))->toBe(31);
});

it('adds days across month and year boundaries with no timezone drift', function () {
    expect((new DateValue(2026, 12, 31))->addDays(1)->toIsoString())->toBe('2027-01-01')
        ->and((new DateValue(2026, 3, 1))->addDays(-1)->toIsoString())->toBe('2026-02-28')
        ->and((new DateValue(2024, 2, 28))->addDays(1)->toIsoString())->toBe('2024-02-29')
        ->and((new DateValue(2026, 6, 18))->addDays(0)->toIsoString())->toBe('2026-06-18');
});

it('adds months and clamps the day to the target month length', function () {
    expect((new DateValue(2026, 1, 31))->addMonths(1)->toIsoString())->toBe('2026-02-28')
        ->and((new DateValue(2024, 1, 31))->addMonths(1)->toIsoString())->toBe('2024-02-29')
        ->and((new DateValue(2026, 12, 15))->addMonths(1)->toIsoString())->toBe('2027-01-15')
        ->and((new DateValue(2026, 1, 15))->addMonths(-2)->toIsoString())->toBe('2025-11-15');
});

it('rejects out-of-range components', function () {
    expect(fn () => new DateValue(2026, 13, 1))->toThrow(InvalidArgumentException::class)
        ->and(fn () => new DateValue(2026, 0, 1))->toThrow(InvalidArgumentException::class)
        ->and(fn () => new DateValue(2026, 2, 30))->toThrow(InvalidArgumentException::class)
        ->and(fn () => new DateValue(2023, 2, 29))->toThrow(InvalidArgumentException::class);
});

it('compares and checks equality', function () {
    $a = new DateValue(2026, 6, 18);
    $b = new DateValue(2026, 6, 19);

    expect($a->equals(new DateValue(2026, 6, 18)))->toBeTrue()
        ->and($a->isBefore($b))->toBeTrue()
        ->and($b->isAfter($a))->toBeTrue()
        ->and($a->compareTo($b))->toBeLessThan(0);
});

it('flags weekends', function () {
    expect((new DateValue(2026, 6, 13))->isWeekend())->toBeTrue() // Saturday
        ->and((new DateValue(2026, 6, 14))->isWeekend())->toBeTrue() // Sunday
        ->and((new DateValue(2026, 6, 15))->isWeekend())->toBeFalse(); // Monday
});

it('serialises to ISO string and array', function () {
    $date = new DateValue(2026, 6, 5);

    expect($date->toIsoString())->toBe('2026-06-05')
        ->and($date->toArray())->toBe(['year' => 2026, 'month' => 6, 'day' => 5]);
});
