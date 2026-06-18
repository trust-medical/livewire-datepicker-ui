<?php

declare(strict_types=1);

use TrustMedical\LivewireDatepickerUi\Domain\Exceptions\InvalidConfigurationException;
use TrustMedical\LivewireDatepickerUi\Domain\Services\DateFormatter;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateTimeValue;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateValue;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\TimeValue;

beforeEach(function () {
    $this->formatter = new DateFormatter;
});

it('formats numeric date tokens', function () {
    $date = new DateValue(2026, 6, 5);

    expect($this->formatter->format($date, 'Y-m-d'))->toBe('2026-06-05')
        ->and($this->formatter->format($date, 'n/j/Y'))->toBe('6/5/2026')
        ->and($this->formatter->format($date, 'd.m.y'))->toBe('05.06.26');
});

it('formats textual date tokens with the English fallback locale', function () {
    $date = new DateValue(2026, 6, 18);

    expect($this->formatter->format($date, 'l, F j, Y'))->toBe('Thursday, June 18, 2026')
        ->and($this->formatter->format($date, 'D M j'))->toBe('Thu Jun 18')
        ->and($this->formatter->format($date, 'N w'))->toBe('4 4');
});

it('formats time tokens including 12-hour clock', function () {
    expect($this->formatter->format(new TimeValue(13, 5, 30), 'H:i:s'))->toBe('13:05:30')
        ->and($this->formatter->format(new TimeValue(13, 5), 'h:i A'))->toBe('01:05 PM')
        ->and($this->formatter->format(new TimeValue(0, 0), 'g:i a'))->toBe('12:00 am')
        ->and($this->formatter->format(new TimeValue(9, 0), 'G:i'))->toBe('9:00');
});

it('formats datetime with escaped literals', function () {
    $value = DateTimeValue::fromComponents(2026, 6, 18, 9, 30, 15);

    expect($this->formatter->format($value, 'Y-m-d\TH:i:s'))->toBe('2026-06-18T09:30:15')
        ->and($this->formatter->format($value, 'Y年n月j日 H時i分'))->toBe('2026年6月18日 09時30分');
});

it('throws when a date token is used on a time-only value', function () {
    expect(fn () => $this->formatter->format(new TimeValue(9, 0), 'Y-m-d'))
        ->toThrow(InvalidConfigurationException::class);
});

it('throws when a time token is used on a date-only value', function () {
    expect(fn () => $this->formatter->format(new DateValue(2026, 6, 18), 'H:i'))
        ->toThrow(InvalidConfigurationException::class);
});
