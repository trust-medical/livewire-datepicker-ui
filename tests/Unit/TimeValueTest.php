<?php

declare(strict_types=1);

use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\TimeValue;

it('exposes total seconds and minutes', function () {
    $time = new TimeValue(13, 5, 30);

    expect($time->totalSeconds())->toBe(13 * 3600 + 5 * 60 + 30)
        ->and($time->totalMinutes())->toBe(13 * 60 + 5);
});

it('computes the 12-hour clock hour and meridiem', function () {
    expect((new TimeValue(0, 0))->hour12())->toBe(12)
        ->and((new TimeValue(0, 0))->isPm())->toBeFalse()
        ->and((new TimeValue(12, 0))->hour12())->toBe(12)
        ->and((new TimeValue(12, 0))->isPm())->toBeTrue()
        ->and((new TimeValue(13, 0))->hour12())->toBe(1)
        ->and((new TimeValue(23, 0))->hour12())->toBe(11);
});

it('rejects out-of-range components', function () {
    expect(fn () => new TimeValue(24, 0))->toThrow(InvalidArgumentException::class)
        ->and(fn () => new TimeValue(0, 60))->toThrow(InvalidArgumentException::class)
        ->and(fn () => new TimeValue(0, 0, 60))->toThrow(InvalidArgumentException::class)
        ->and(fn () => new TimeValue(-1, 0))->toThrow(InvalidArgumentException::class);
});

it('compares by absolute time', function () {
    $a = new TimeValue(9, 0);
    $b = new TimeValue(9, 30);

    expect($a->isBefore($b))->toBeTrue()
        ->and($b->isAfter($a))->toBeTrue()
        ->and($a->equals(new TimeValue(9, 0, 0)))->toBeTrue();
});

it('serialises to ISO string and array', function () {
    $time = new TimeValue(9, 5, 1);

    expect($time->toIsoString())->toBe('09:05:01')
        ->and($time->toArray())->toBe(['hour' => 9, 'minute' => 5, 'second' => 1]);
});
