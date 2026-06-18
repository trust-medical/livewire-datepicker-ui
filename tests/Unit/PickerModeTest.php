<?php

declare(strict_types=1);

use TrustMedical\LivewireDatepickerUi\Domain\Enums\PickerMode;
use TrustMedical\LivewireDatepickerUi\Domain\Exceptions\InvalidConfigurationException;

it('parses from a string case-insensitively', function () {
    expect(PickerMode::fromString('date'))->toBe(PickerMode::Date)
        ->and(PickerMode::fromString('TIME'))->toBe(PickerMode::Time)
        ->and(PickerMode::fromString(' DateTime '))->toBe(PickerMode::DateTime)
        ->and(PickerMode::fromString('Month'))->toBe(PickerMode::Month);
});

it('reports date and time capability', function () {
    expect(PickerMode::Date->hasDate())->toBeTrue()
        ->and(PickerMode::Date->hasTime())->toBeFalse()
        ->and(PickerMode::Time->hasDate())->toBeFalse()
        ->and(PickerMode::Time->hasTime())->toBeTrue()
        ->and(PickerMode::DateTime->hasDate())->toBeTrue()
        ->and(PickerMode::DateTime->hasTime())->toBeTrue()
        ->and(PickerMode::Month->hasDate())->toBeTrue()
        ->and(PickerMode::Month->hasTime())->toBeFalse();
});

it('reports month capability', function () {
    expect(PickerMode::Month->isMonth())->toBeTrue()
        ->and(PickerMode::Date->isMonth())->toBeFalse()
        ->and(PickerMode::DateTime->isMonth())->toBeFalse()
        ->and(PickerMode::Time->isMonth())->toBeFalse();
});

it('throws on an unknown mode', function () {
    expect(fn () => PickerMode::fromString('week'))->toThrow(InvalidConfigurationException::class);
});
