<?php

declare(strict_types=1);

use TrustMedical\LivewireDatepickerUi\Application\UseCases\FormatValueUseCase;
use TrustMedical\LivewireDatepickerUi\Application\UseCases\ParseInputUseCase;
use TrustMedical\LivewireDatepickerUi\Application\UseCases\ValidateSelectionUseCase;
use TrustMedical\LivewireDatepickerUi\Domain\Enums\PickerMode;
use TrustMedical\LivewireDatepickerUi\Domain\Services\DisabledRule;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateRange;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateTimeValue;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateValue;

it('formats a value into both representations', function () {
    $useCase = new FormatValueUseCase;
    $result = $useCase->fromValue(
        DateTimeValue::fromComponents(2026, 6, 18, 9, 30, 0),
        'Y-m-d\TH:i:s',
        'Y-m-d H:i',
    );

    expect($result->value)->toBe('2026-06-18T09:30:00')
        ->and($result->display)->toBe('2026-06-18 09:30');
});

it('re-derives display + value from a stored string', function () {
    $useCase = new FormatValueUseCase;
    $result = $useCase->fromValueString('2026-06-18', 'Y-m-d', 'F j, Y', PickerMode::Date);

    expect($result)->not->toBeNull()
        ->and($result?->display)->toBe('June 18, 2026')
        ->and($result?->value)->toBe('2026-06-18');
});

it('returns null when a stored string cannot be parsed', function () {
    $useCase = new FormatValueUseCase;

    expect($useCase->fromValueString('', 'Y-m-d', 'Y-m-d', PickerMode::Date))->toBeNull()
        ->and($useCase->fromValueString('nope', 'Y-m-d', 'Y-m-d', PickerMode::Date))->toBeNull();
});

it('parses input into an explicit result', function () {
    $useCase = new ParseInputUseCase;

    $ok = $useCase->execute('2026-06-18', 'Y-m-d', PickerMode::Date);
    $empty = $useCase->execute('   ', 'Y-m-d', PickerMode::Date);
    $bad = $useCase->execute('xx', 'Y-m-d', PickerMode::Date);

    expect($ok->successful)->toBeTrue()
        ->and($ok->value)->toBeInstanceOf(DateValue::class)
        ->and($empty->failed())->toBeTrue()
        ->and($empty->error)->toBe('empty')
        ->and($bad->failed())->toBeTrue();
});

it('validates selections against the disabled rule', function () {
    $useCase = new ValidateSelectionUseCase;
    $rule = new DisabledRule(
        new DateRange(DateTimeValue::fromComponents(2026, 6, 10), DateTimeValue::fromComponents(2026, 6, 20)),
        disabledDates: [new DateValue(2026, 6, 15)],
        disabledWeekdays: [0, 6],
    );

    expect($useCase->execute(new DateValue(2026, 6, 17), $rule)->valid)->toBeTrue()
        ->and($useCase->execute(new DateValue(2026, 6, 5), $rule)->reasons)->toBe(['below_min'])
        ->and($useCase->execute(new DateValue(2026, 6, 15), $rule)->reasons)->toBe(['disabled_date'])
        ->and($useCase->execute(new DateValue(2026, 6, 13), $rule)->reasons)->toContain('disabled_weekday');
});
