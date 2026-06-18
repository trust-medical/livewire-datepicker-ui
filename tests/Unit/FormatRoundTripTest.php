<?php

declare(strict_types=1);

use TrustMedical\LivewireDatepickerUi\Domain\Enums\PickerMode;
use TrustMedical\LivewireDatepickerUi\Domain\Services\DateFormatter;
use TrustMedical\LivewireDatepickerUi\Domain\Services\DateParser;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateTimeValue;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateValue;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\TimeValue;

/**
 * Builds a value object from a fixture's mode + component map.
 *
 * @param  array<string, int>  $components
 */
function fixtureValue(PickerMode $mode, array $components): DateValue|TimeValue|DateTimeValue
{
    return match ($mode) {
        PickerMode::Date => new DateValue($components['year'], $components['month'], $components['day']),
        PickerMode::Time => new TimeValue($components['hour'], $components['minute'], $components['second'] ?? 0),
        PickerMode::DateTime => DateTimeValue::fromComponents(
            $components['year'],
            $components['month'],
            $components['day'],
            $components['hour'],
            $components['minute'],
            $components['second'] ?? 0,
        ),
    };
}

it('formats and round-trips the shared PHP/TS fixtures', function (array $case) {
    $formatter = new DateFormatter;
    $parser = new DateParser;
    $mode = PickerMode::from($case['mode']);

    /** @var array<string, int> $components */
    $components = $case['value'];
    $value = fixtureValue($mode, $components);

    expect($formatter->format($value, $case['format']))->toBe($case['formatted']);

    if ($case['parseable'] === true) {
        $parsed = $parser->parse($case['formatted'], $case['format'], $mode);

        expect($formatter->format($parsed, $case['format']))->toBe($case['formatted']);
    }
})->with(array_map(static fn (array $case): array => [$case], formatFixtures()));
