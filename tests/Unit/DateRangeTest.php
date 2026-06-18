<?php

declare(strict_types=1);

use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateRange;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateTimeValue;

it('treats an unbounded range as containing everything', function () {
    $range = DateRange::unbounded();

    expect($range->isBounded())->toBeFalse()
        ->and($range->contains(DateTimeValue::fromComponents(1900, 1, 1)))->toBeTrue()
        ->and($range->contains(DateTimeValue::fromComponents(2999, 12, 31)))->toBeTrue();
});

it('respects inclusive min and max boundaries', function () {
    $range = new DateRange(
        DateTimeValue::fromComponents(2026, 6, 1),
        DateTimeValue::fromComponents(2026, 6, 30, 23, 59, 59),
    );

    expect($range->contains(DateTimeValue::fromComponents(2026, 6, 1)))->toBeTrue()
        ->and($range->contains(DateTimeValue::fromComponents(2026, 5, 31, 23, 59, 59)))->toBeFalse()
        ->and($range->contains(DateTimeValue::fromComponents(2026, 7, 1)))->toBeFalse()
        ->and($range->isBelowMin(DateTimeValue::fromComponents(2026, 5, 1)))->toBeTrue()
        ->and($range->isAboveMax(DateTimeValue::fromComponents(2026, 7, 1)))->toBeTrue();
});

it('clamps values into the range', function () {
    $min = DateTimeValue::fromComponents(2026, 6, 1);
    $max = DateTimeValue::fromComponents(2026, 6, 30);
    $range = new DateRange($min, $max);

    expect($range->clamp(DateTimeValue::fromComponents(2026, 5, 1))->equals($min))->toBeTrue()
        ->and($range->clamp(DateTimeValue::fromComponents(2026, 7, 1))->equals($max))->toBeTrue()
        ->and($range->clamp(DateTimeValue::fromComponents(2026, 6, 15))->equals(DateTimeValue::fromComponents(2026, 6, 15)))->toBeTrue();
});
