<?php

declare(strict_types=1);

use TrustMedical\LivewireDatepickerUi\Domain\Enums\PickerMode;
use TrustMedical\LivewireDatepickerUi\Domain\Exceptions\InvalidConfigurationException;

it('parses from a string case-insensitively', function () {
    expect(PickerMode::fromString('date'))->toBe(PickerMode::Date)
        ->and(PickerMode::fromString('TIME'))->toBe(PickerMode::Time)
        ->and(PickerMode::fromString(' DateTime '))->toBe(PickerMode::DateTime);
});

it('reports date and time capability', function () {
    expect(PickerMode::Date->hasDate())->toBeTrue()
        ->and(PickerMode::Date->hasTime())->toBeFalse()
        ->and(PickerMode::Time->hasDate())->toBeFalse()
        ->and(PickerMode::Time->hasTime())->toBeTrue()
        ->and(PickerMode::DateTime->hasDate())->toBeTrue()
        ->and(PickerMode::DateTime->hasTime())->toBeTrue();
});

it('throws on an unknown mode', function () {
    expect(fn () => PickerMode::fromString('week'))->toThrow(InvalidConfigurationException::class);
});
