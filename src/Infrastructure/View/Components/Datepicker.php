<?php

declare(strict_types=1);

namespace TrustMedical\LivewireDatepickerUi\Infrastructure\View\Components;

use DateTimeInterface;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use Illuminate\View\ComponentAttributeBag;
use TrustMedical\LivewireDatepickerUi\Application\DTO\FormatResult;
use TrustMedical\LivewireDatepickerUi\Application\DTO\PickerConfig;
use TrustMedical\LivewireDatepickerUi\Application\UseCases\FormatValueUseCase;
use TrustMedical\LivewireDatepickerUi\Application\UseCases\PickerConfigFactory;
use TrustMedical\LivewireDatepickerUi\Infrastructure\Laravel\LocaleFactory;
use TrustMedical\LivewireDatepickerUi\Support\Locale;

/**
 * The single class component backing every public tag.
 *
 * `<x-datepicker>` binds to this class directly; `<x-date-picker>`,
 * `<x-time-picker>` and `<x-date-time-picker>` are thin anonymous components
 * that forward to it with a pinned `mode`. The view is `datepicker::field`
 * (publishable).
 */
final class Datepicker extends Component
{
    /**
     * @param  array<int, mixed>|null  $disabledDates
     * @param  array<int, mixed>|null  $disabledWeekdays
     * @param  array<int, mixed>|null  $disabledTimes
     * @param  array<string, mixed>|null  $classes
     */
    public function __construct(
        public ?string $mode = null,
        public ?string $displayFormat = null,
        public ?string $valueFormat = null,
        public ?string $locale = null,
        public int|string|null $firstDayOfWeek = null,
        public mixed $value = null,
        public mixed $min = null,
        public mixed $max = null,
        public ?array $disabledDates = null,
        public ?array $disabledWeekdays = null,
        public ?array $disabledTimes = null,
        public int|string|null $minuteStep = null,
        public int|string|null $hourCycle = null,
        public ?string $placeholder = null,
        public bool $disabled = false,
        public bool $readonly = false,
        public bool $required = false,
        public ?bool $clearable = null,
        public ?bool $todayButton = null,
        public ?bool $closeButton = null,
        public bool $inline = false,
        public ?string $placement = null,
        public ?array $classes = null,
        public ?string $theme = null,
        public ?string $ariaLabel = null,
        public int|string|null $debounceMs = null,
        public ?string $id = null,
        public ?string $name = null,
    ) {}

    /**
     * Build everything the view needs. Called from the Blade template with the
     * attribute bag so `wire:model` can be detected without depending on a
     * Livewire-version-specific macro.
     *
     * @return array{config: PickerConfig, display: string, weekdays: list<string>, years: list<int>, fieldId: string}
     */
    public function picker(ComponentAttributeBag $attributes): array
    {
        /** @var array<string, mixed> $config */
        $config = (array) config('datepicker', []);

        [$wireModel, $wireModifiers] = $this->resolveWire($attributes);

        $fieldId = $this->id ?? 'dp-' . Str::random(8);
        $locale = $this->resolveLocale($config);

        $props = [
            'mode' => $this->mode,
            'displayFormat' => $this->displayFormat,
            'valueFormat' => $this->valueFormat,
            'firstDayOfWeek' => $this->firstDayOfWeek,
            'value' => $this->stringifyValue($this->value),
            'min' => $this->stringifyValue($this->min),
            'max' => $this->stringifyValue($this->max),
            'disabledDates' => $this->stringifyList($this->disabledDates),
            'disabledWeekdays' => $this->disabledWeekdays,
            'disabledTimes' => $this->disabledTimes,
            'minuteStep' => $this->minuteStep,
            'hourCycle' => $this->hourCycle,
            'placeholder' => $this->placeholder,
            'disabled' => $this->disabled,
            'readonly' => $this->readonly,
            'required' => $this->required,
            'clearable' => $this->clearable,
            'todayButton' => $this->todayButton,
            'closeButton' => $this->closeButton,
            'inline' => $this->inline,
            'placement' => $this->placement,
            'classes' => $this->classes,
            'theme' => $this->theme,
            'ariaLabel' => $this->ariaLabel,
            'debounceMs' => $this->debounceMs,
            'id' => $fieldId,
            'name' => $this->name,
            'wireModel' => $wireModel,
            'wireModifiers' => $wireModifiers,
        ];

        $pickerConfig = (new PickerConfigFactory)->create($props, $config, $locale, $fieldId);

        $display = '';

        if ($pickerConfig->value !== null) {
            $formatted = (new FormatValueUseCase)->fromValueString(
                $pickerConfig->value,
                $pickerConfig->valueFormat,
                $pickerConfig->displayFormat,
                $pickerConfig->mode,
                $locale,
            );

            if ($formatted instanceof FormatResult) {
                $display = $formatted->display;
            }
        }

        return [
            'config' => $pickerConfig,
            'display' => $display,
            'weekdays' => $this->orderedWeekdays($locale, $pickerConfig->firstDayOfWeek),
            'years' => $this->yearRange($pickerConfig),
            'fieldId' => $fieldId,
        ];
    }

    /**
     * Server-rendered year options. Rendering them up front (rather than via an
     * Alpine `x-for`) avoids a select binding race and keeps the calendar's view
     * year stable.
     *
     * @return list<int>
     */
    private function yearRange(PickerConfig $config): array
    {
        $base = (int) Carbon::now()->format('Y');
        $start = $base - 100;
        $end = $base + 10;

        foreach ([$config->value, $config->min, $config->max] as $candidate) {
            if (is_string($candidate) && preg_match('/(\d{4})/', $candidate, $matches) === 1) {
                $year = (int) $matches[1];
                $start = min($start, $year);
                $end = max($end, $year);
            }
        }

        return range($start, $end);
    }

    public function render(): View
    {
        return view('datepicker::field');
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function resolveLocale(array $config): Locale
    {
        $code = $this->locale;

        if ($code === null) {
            $configured = $config['locale'] ?? 'auto';
            $code = is_string($configured) && $configured !== 'auto' ? $configured : app()->getLocale();
        }

        $firstDay = is_numeric($config['first_day_of_week'] ?? null) ? (int) $config['first_day_of_week'] : 0;

        /** @var Translator $translator */
        $translator = app('translator');

        return (new LocaleFactory($translator))->make($code, $firstDay);
    }

    /**
     * Detect `wire:model[.modifier...]` straight from the raw attributes so the
     * package works on Livewire 3 and 4 without touching internal APIs.
     *
     * @return array{0: string|null, 1: list<string>}
     */
    private function resolveWire(ComponentAttributeBag $attributes): array
    {
        foreach ($attributes->getAttributes() as $key => $rawValue) {
            if ($key !== 'wire:model' && ! str_starts_with($key, 'wire:model.')) {
                continue;
            }

            $model = is_string($rawValue) ? $rawValue : null;
            $modifierString = ltrim(substr($key, strlen('wire:model')), '.');
            $modifiers = $modifierString === '' ? [] : array_values(array_filter(explode('.', $modifierString)));

            return [$model, $modifiers];
        }

        return [null, []];
    }

    private function stringifyValue(mixed $value): ?string
    {
        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value)->toIso8601String();
        }

        if (is_string($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return null;
    }

    /**
     * @return list<string>|null
     */
    private function stringifyList(mixed $value): ?array
    {
        if (! is_array($value)) {
            return null;
        }

        $items = [];

        foreach ($value as $entry) {
            $stringified = $this->stringifyValue($entry);
            if ($stringified !== null) {
                $items[] = $stringified;
            }
        }

        return $items;
    }

    /**
     * @return list<string>
     */
    private function orderedWeekdays(Locale $locale, int $firstDayOfWeek): array
    {
        $labels = [];

        for ($offset = 0; $offset < 7; $offset++) {
            $labels[] = $locale->weekdayMin(($firstDayOfWeek + $offset) % 7);
        }

        return $labels;
    }
}
