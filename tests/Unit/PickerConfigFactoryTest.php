<?php

declare(strict_types=1);

use TrustMedical\LivewireDatepickerUi\Application\UseCases\PickerConfigFactory;
use TrustMedical\LivewireDatepickerUi\Support\Locale;

/**
 * @return array<string, mixed>
 */
function factoryConfig(): array
{
    return [
        'mode' => 'date',
        'formats' => [
            'date' => ['display' => 'Y-m-d', 'value' => 'Y-m-d'],
            'time' => ['display' => 'H:i', 'display_12' => 'h:i A', 'value' => 'H:i:s'],
            'datetime' => ['display' => 'Y-m-d H:i', 'display_12' => 'Y-m-d h:i A', 'value' => 'Y-m-d\TH:i:s'],
        ],
        'minute_step' => 5,
        'hour_cycle' => 24,
        'placement' => 'bottom-start',
        'clearable' => true,
        'today_button' => true,
        'close_button' => true,
        'debounce_ms' => 200,
        'classes' => ['input' => 'border', 'day' => 'p-1', 'day_selected' => 'bg-blue'],
        'themes' => ['minimal' => ['input' => 'border-0']],
    ];
}

beforeEach(function () {
    $this->factory = new PickerConfigFactory;
    $this->locale = Locale::english();
});

it('applies mode defaults and normalizes date boundaries', function () {
    $config = $this->factory->create([
        'mode' => 'date',
        'min' => '2026-06-10',
        'max' => '2026-06-20',
    ], factoryConfig(), $this->locale, 'dp1');

    expect($config->mode->value)->toBe('date')
        ->and($config->displayFormat)->toBe('Y-m-d')
        ->and($config->valueFormat)->toBe('Y-m-d')
        ->and($config->min)->toBe('2026-06-10')
        ->and($config->max)->toBe('2026-06-20')
        ->and($config->minuteStep)->toBe(5);
});

it('applies month defaults and normalizes month boundaries', function () {
    $config = $this->factory->create([
        'mode' => 'month',
        'value' => '2026-06',
        'min' => '2026-01',
        'max' => '2026-12-31', // a full date clamps to its month
    ], factoryConfig(), $this->locale, 'dpM');

    expect($config->mode->value)->toBe('month')
        ->and($config->displayFormat)->toBe('Y-m')
        ->and($config->valueFormat)->toBe('Y-m')
        ->and($config->value)->toBe('2026-06')
        ->and($config->min)->toBe('2026-01')
        ->and($config->max)->toBe('2026-12');
});

it('uses the 12-hour display format when the hour cycle is 12', function () {
    $config = $this->factory->create([
        'mode' => 'datetime',
        'hourCycle' => 12,
    ], factoryConfig(), $this->locale, 'dp2');

    expect($config->displayFormat)->toBe('Y-m-d h:i A')
        ->and($config->valueFormat)->toBe('Y-m-d\TH:i:s')
        ->and($config->hourCycle)->toBe(12);
});

it('normalizes time boundaries and disabled time windows', function () {
    $config = $this->factory->create([
        'mode' => 'time',
        'min' => '09:00',
        'max' => '17:00',
        'disabledTimes' => [['from' => '12:00', 'to' => '13:00']],
    ], factoryConfig(), $this->locale, 'dp3');

    expect($config->min)->toBe('09:00:00')
        ->and($config->max)->toBe('17:00:00')
        ->and($config->disabledTimes)->toBe([['from' => '12:00:00', 'to' => '13:00:00']]);
});

it('normalizes disabled dates and weekdays', function () {
    $config = $this->factory->create([
        'disabledDates' => ['2026-06-15', 'garbage', '06/20/2026'],
        'disabledWeekdays' => [0, 6, 9, 'x'],
    ], factoryConfig(), $this->locale, 'dp4');

    expect($config->disabledDates)->toBe(['2026-06-15', '2026-06-20'])
        ->and($config->disabledWeekdays)->toBe([0, 6]);
});

it('resolves the layered class map (default -> theme -> instance)', function () {
    $config = $this->factory->create([
        'theme' => 'minimal',
        'classes' => ['day_selected' => 'bg-red'],
    ], factoryConfig(), $this->locale, 'dp5');

    expect($config->class('input'))->toBe('border-0')       // theme override
        ->and($config->class('day_selected'))->toBe('bg-red') // instance override
        ->and($config->class('day'))->toBe('p-1');            // default preserved
});

it('derives wire binding and name', function () {
    $config = $this->factory->create([
        'wireModel' => 'starts_at',
        'wireModifiers' => ['live'],
    ], factoryConfig(), $this->locale, 'dp6');

    expect($config->wireModel())->toBe('starts_at')
        ->and($config->isWired())->toBeTrue()
        ->and($config->isLive())->toBeTrue()
        ->and($config->isDeferred())->toBeFalse()
        ->and($config->name)->toBe('starts_at');
});

it('produces JSON that preserves unicode and slashes', function () {
    $config = $this->factory->create(['mode' => 'datetime'], factoryConfig(), $this->locale, 'dp7');
    $json = $config->toClientJson();

    /** @var array<string, mixed> $decoded */
    $decoded = json_decode($json, true);

    expect($decoded)->toBeArray()
        ->and($decoded['valueFormat'])->toBe('Y-m-d\TH:i:s')
        ->and($json)->not->toContain('http:\/\/'); // slashes stay unescaped
});
