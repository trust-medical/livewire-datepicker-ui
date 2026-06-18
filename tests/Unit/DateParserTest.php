<?php

declare(strict_types=1);

use TrustMedical\LivewireDatepickerUi\Domain\Enums\PickerMode;
use TrustMedical\LivewireDatepickerUi\Domain\Exceptions\InvalidDateFormatException;
use TrustMedical\LivewireDatepickerUi\Domain\Services\DateParser;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateTimeValue;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateValue;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\TimeValue;

beforeEach(function () {
    $this->parser = new DateParser;
});

it('parses iso dates', function () {
    $value = $this->parser->parse('2026-06-18', 'Y-m-d', PickerMode::Date);

    expect($value)->toBeInstanceOf(DateValue::class)
        ->and($value->toIsoString())->toBe('2026-06-18');
});

it('parses non-padded numeric dates', function () {
    $value = $this->parser->parse('6/5/2026', 'n/j/Y', PickerMode::Date);

    expect($value->toIsoString())->toBe('2026-06-05');
});

it('parses two-digit years into the 2000 century', function () {
    $value = $this->parser->parse('18/06/26', 'd/m/y', PickerMode::Date);

    expect($value->toIsoString())->toBe('2026-06-18');
});

it('parses textual month names', function () {
    expect($this->parser->parse('June 18, 2026', 'F j, Y', PickerMode::Date)->toIsoString())->toBe('2026-06-18')
        ->and($this->parser->parse('Dec 1 2026', 'M j Y', PickerMode::Date)->toIsoString())->toBe('2026-12-01');
});

it('parses 24-hour and 12-hour times', function () {
    $h24 = $this->parser->parse('13:05', 'H:i', PickerMode::Time);
    $pm = $this->parser->parse('01:05 PM', 'h:i A', PickerMode::Time);
    $midnight = $this->parser->parse('12:00 am', 'g:i a', PickerMode::Time);
    $noon = $this->parser->parse('12:30 pm', 'g:i a', PickerMode::Time);

    expect($h24)->toBeInstanceOf(TimeValue::class)
        ->and($h24->hour)->toBe(13)->and($h24->minute)->toBe(5)
        ->and($pm->hour)->toBe(13)
        ->and($midnight->hour)->toBe(0)
        ->and($noon->hour)->toBe(12);
});

it('parses datetime with escaped literals', function () {
    $value = $this->parser->parse('2026-06-18T09:30:15', 'Y-m-d\TH:i:s', PickerMode::DateTime);

    expect($value)->toBeInstanceOf(DateTimeValue::class)
        ->and($value->date->toIsoString())->toBe('2026-06-18')
        ->and($value->time->toIsoString())->toBe('09:30:15');
});

it('parses multibyte literal formats', function () {
    $value = $this->parser->parse('2026年6月18日', 'Y年n月j日', PickerMode::Date);

    expect($value->toIsoString())->toBe('2026-06-18');
});

it('throws on non-matching input', function () {
    expect(fn () => $this->parser->parse('not-a-date', 'Y-m-d', PickerMode::Date))
        ->toThrow(InvalidDateFormatException::class);
});

it('throws on out-of-range components', function () {
    expect(fn () => $this->parser->parse('2026-13-01', 'Y-m-d', PickerMode::Date))
        ->toThrow(InvalidDateFormatException::class)
        ->and(fn () => $this->parser->parse('2026-02-30', 'Y-m-d', PickerMode::Date))
        ->toThrow(InvalidDateFormatException::class);
});

it('throws when required components are missing for the mode', function () {
    expect(fn () => $this->parser->parse('2026', 'Y', PickerMode::Date))
        ->toThrow(InvalidDateFormatException::class);
});
