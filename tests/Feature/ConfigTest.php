<?php

declare(strict_types=1);

it('merges the package config', function () {
    expect(config('datepicker.mode'))->toBe('date')
        ->and(config('datepicker.minute_step'))->toBe(5)
        ->and(config('datepicker.classes.day_selected'))->toContain('aria-selected:bg-zinc-900')
        ->and(config('datepicker.classes.time_option_selected'))->toContain('data-[selected]:bg-zinc-900')
        ->and(config('datepicker.formats.datetime.value'))->toBe('Y-m-d\TH:i:s');
});

it('registers the package translations', function () {
    expect(trans('datepicker::datepicker.labels.today'))->toBe('Today')
        ->and(trans('datepicker::datepicker.weekdays_min'))->toBeArray();
});
