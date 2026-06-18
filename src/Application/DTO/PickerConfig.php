<?php

declare(strict_types=1);

namespace TrustMedical\LivewireDatepickerUi\Application\DTO;

use TrustMedical\LivewireDatepickerUi\Domain\Enums\PickerMode;
use TrustMedical\LivewireDatepickerUi\Support\Locale;

/**
 * The fully normalized configuration for a single picker instance.
 *
 * Carries only primitives + the locale so it serialises cleanly to the JSON the
 * Alpine component reads, while also exposing the helpers the Blade view needs.
 */
final class PickerConfig
{
    /**
     * @param  list<string>  $disabledDates  Each an ISO `Y-m-d` string.
     * @param  list<int>  $disabledWeekdays  Each 0 (Sunday)..6 (Saturday).
     * @param  list<array{from: string, to: string}>  $disabledTimes  Each bound an `H:i:s` string.
     * @param  array<string, string>  $classes  The resolved class map.
     * @param  array{model: string|null, modifiers: list<string>}  $wire
     */
    public function __construct(
        public readonly string $id,
        public readonly ?string $name,
        public readonly PickerMode $mode,
        public readonly string $displayFormat,
        public readonly string $valueFormat,
        public readonly Locale $locale,
        public readonly int $firstDayOfWeek,
        public readonly ?string $value,
        public readonly ?string $min,
        public readonly ?string $max,
        public readonly array $disabledDates,
        public readonly array $disabledWeekdays,
        public readonly array $disabledTimes,
        public readonly int $minuteStep,
        public readonly int $hourCycle,
        public readonly ?string $placeholder,
        public readonly bool $disabled,
        public readonly bool $readonly,
        public readonly bool $required,
        public readonly bool $clearable,
        public readonly bool $todayButton,
        public readonly bool $closeButton,
        public readonly bool $inline,
        public readonly string $placement,
        public readonly array $classes,
        public readonly ?string $theme,
        public readonly ?string $ariaLabel,
        public readonly int $debounceMs,
        public readonly array $wire,
    ) {}

    public function wireModel(): ?string
    {
        return $this->wire['model'];
    }

    public function isWired(): bool
    {
        return $this->wire['model'] !== null;
    }

    public function isLive(): bool
    {
        return in_array('live', $this->wire['modifiers'], true);
    }

    public function isDeferred(): bool
    {
        return in_array('defer', $this->wire['modifiers'], true)
            || in_array('lazy', $this->wire['modifiers'], true);
    }

    public function class(string $key, string $default = ''): string
    {
        return $this->classes[$key] ?? $default;
    }

    /**
     * The JSON configuration consumed by the Alpine component. Keys are camelCase
     * to match the TypeScript `DatepickerConfig` type exactly.
     *
     * @return array<string, mixed>
     */
    public function toClientArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'mode' => $this->mode->value,
            'displayFormat' => $this->displayFormat,
            'valueFormat' => $this->valueFormat,
            'locale' => $this->locale->toArray(),
            'firstDayOfWeek' => $this->firstDayOfWeek,
            'value' => $this->value,
            'min' => $this->min,
            'max' => $this->max,
            'disabledDates' => $this->disabledDates,
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
            'wire' => $this->wire,
        ];
    }

    public function toClientJson(): string
    {
        return json_encode($this->toClientArray(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
