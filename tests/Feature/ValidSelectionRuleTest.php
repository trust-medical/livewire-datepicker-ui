<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Validator;
use TrustMedical\LivewireDatepickerUi\Rules\ValidSelection;

function passes(string $value, string $mode, array $options): bool
{
    return Validator::make(
        ['field' => $value],
        ['field' => [new ValidSelection($mode, $options)]],
    )->passes();
}

it('passes a valid in-range date', function () {
    expect(passes('2026-06-18', 'date', [
        'valueFormat' => 'Y-m-d',
        'min' => '2026-06-10',
        'max' => '2026-06-20',
    ]))->toBeTrue();
});

it('fails a date outside the range', function () {
    expect(passes('2026-06-05', 'date', [
        'valueFormat' => 'Y-m-d',
        'min' => '2026-06-10',
    ]))->toBeFalse();
});

it('fails a disabled weekday', function () {
    // 2026-06-13 is a Saturday.
    expect(passes('2026-06-13', 'date', [
        'valueFormat' => 'Y-m-d',
        'disabledWeekdays' => [0, 6],
    ]))->toBeFalse();
});

it('fails an unparseable value', function () {
    expect(passes('not-a-date', 'date', ['valueFormat' => 'Y-m-d']))->toBeFalse();
});

it('ignores empty values (left to the required rule)', function () {
    expect(passes('', 'date', ['valueFormat' => 'Y-m-d']))->toBeTrue();
});
