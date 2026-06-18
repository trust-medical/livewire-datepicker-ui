<?php

declare(strict_types=1);

use Illuminate\View\ComponentAttributeBag;
use TrustMedical\LivewireDatepickerUi\Application\DTO\PickerConfig;
use TrustMedical\LivewireDatepickerUi\Infrastructure\View\Components\Datepicker;

/**
 * @param  array<string, mixed>  $attributes
 */
function buildConfig(Datepicker $component, array $attributes = []): PickerConfig
{
    return $component->picker(new ComponentAttributeBag($attributes))['config'];
}

it('normalizes props through the integrated component path', function () {
    $config = buildConfig(
        new Datepicker(mode: 'datetime', min: '2026-06-10', max: '2026-06-20', disabledWeekdays: [0, 6]),
        ['wire:model.live' => 'starts_at'],
    );

    expect($config->mode->value)->toBe('datetime')
        ->and($config->wireModel())->toBe('starts_at')
        ->and($config->isLive())->toBeTrue()
        ->and($config->disabledWeekdays)->toBe([0, 6])
        ->and($config->min)->not->toBeNull()
        ->and($config->max)->not->toBeNull();
});

it('detects every wire:model modifier version-agnostically', function () {
    expect(buildConfig(new Datepicker, ['wire:model' => 'a'])->wire['modifiers'])->toBe([])
        ->and(buildConfig(new Datepicker, ['wire:model.live' => 'a'])->isLive())->toBeTrue()
        ->and(buildConfig(new Datepicker, ['wire:model.defer' => 'a'])->isDeferred())->toBeTrue()
        ->and(buildConfig(new Datepicker, ['wire:model.blur' => 'a'])->wire['modifiers'])->toBe(['blur'])
        ->and(buildConfig(new Datepicker)->isWired())->toBeFalse();
});

it('uses the application locale for names', function () {
    app()->setLocale('ja');

    $config = buildConfig(new Datepicker(mode: 'date'));

    expect($config->locale->monthName(6))->toBe('6月')
        ->and($config->locale->weekdayMin(0))->toBe('日');
});

it('falls back the field name to the wire model then the id', function () {
    expect(buildConfig(new Datepicker, ['wire:model' => 'published_at'])->name)->toBe('published_at')
        ->and(buildConfig(new Datepicker(name: 'explicit'))->name)->toBe('explicit');
});
