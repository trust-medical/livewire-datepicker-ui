<?php

declare(strict_types=1);

namespace TrustMedical\LivewireDatepickerUi\Application\UseCases;

use TrustMedical\LivewireDatepickerUi\Application\DTO\PickerConfig;
use TrustMedical\LivewireDatepickerUi\Domain\Enums\PickerMode;
use TrustMedical\LivewireDatepickerUi\Domain\Exceptions\InvalidDateFormatException;
use TrustMedical\LivewireDatepickerUi\Domain\Services\DateFormatter;
use TrustMedical\LivewireDatepickerUi\Domain\Services\DateParser;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateTimeValue;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateValue;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\TimeValue;
use TrustMedical\LivewireDatepickerUi\Support\ClassMerger;
use TrustMedical\LivewireDatepickerUi\Support\Locale;

/**
 * Normalizes raw component props + package config into a {@see PickerConfig}.
 *
 * This is the single place that decides defaults, resolves the layered class
 * map, and canonicalises every loose value (min/max/value/disabled rules) into
 * the strict shapes the view and the client runtime depend on.
 */
final class PickerConfigFactory
{
    /** @var list<string> */
    private const DATE_CANDIDATES = ['Y-m-d', 'Y/m/d', 'd-m-Y', 'd/m/Y', 'm/d/Y'];

    /** @var list<string> */
    private const TIME_CANDIDATES = ['H:i:s', 'H:i', 'h:i A', 'g:i a'];

    /** @var list<string> */
    private const MONTH_CANDIDATES = ['Y-m', 'Y/m', 'm/Y'];

    public function __construct(
        private readonly DateParser $parser = new DateParser,
        private readonly DateFormatter $formatter = new DateFormatter,
    ) {}

    /**
     * @param  array<string, mixed>  $props  Raw component props (null = use default).
     * @param  array<string, mixed>  $config  The package config array.
     */
    public function create(array $props, array $config, Locale $locale, string $id): PickerConfig
    {
        $mode = PickerMode::fromString($this->stringOr($props, 'mode', $this->stringOr($config, 'mode', 'date')) ?? 'date');
        $hourCycle = $this->intOr($props, 'hourCycle', $this->intOr($config, 'hour_cycle', 24)) === 12 ? 12 : 24;

        $formats = $this->resolveFormats($props, $config, $mode, $hourCycle);
        $displayFormat = $formats['display'];
        $valueFormat = $formats['value'];

        $firstDayOfWeek = $this->normalizeWeekday(
            $this->intOr($props, 'firstDayOfWeek', $locale->firstDayOfWeek),
        );

        $minuteStep = max(1, $this->intOr($props, 'minuteStep', $this->intOr($config, 'minute_step', 1)));

        $resolvedId = $this->stringOr($props, 'id', $id) ?? $id;
        $wireModel = $this->stringOr($props, 'wireModel', null);
        $name = $this->stringOr($props, 'name', $wireModel ?? $resolvedId);

        return new PickerConfig(
            id: $resolvedId,
            name: $name,
            mode: $mode,
            displayFormat: $displayFormat,
            valueFormat: $valueFormat,
            locale: $locale,
            firstDayOfWeek: $firstDayOfWeek,
            value: $this->normalizeValue($this->stringOr($props, 'value', null), $mode, $valueFormat, $locale),
            min: $this->normalizeBoundary($this->stringOr($props, 'min', null), $mode, $valueFormat, $locale, isMax: false),
            max: $this->normalizeBoundary($this->stringOr($props, 'max', null), $mode, $valueFormat, $locale, isMax: true),
            disabledDates: $this->normalizeDisabledDates($props['disabledDates'] ?? null, $locale),
            disabledWeekdays: $this->normalizeDisabledWeekdays($props['disabledWeekdays'] ?? null),
            disabledTimes: $this->normalizeDisabledTimes($props['disabledTimes'] ?? null, $locale),
            minuteStep: $minuteStep,
            hourCycle: $hourCycle,
            placeholder: $this->stringOr($props, 'placeholder', null),
            disabled: $this->boolOr($props, 'disabled', false),
            readonly: $this->boolOr($props, 'readonly', false),
            required: $this->boolOr($props, 'required', false),
            clearable: $this->boolOr($props, 'clearable', $this->boolOr($config, 'clearable', true)),
            todayButton: $this->boolOr($props, 'todayButton', $this->boolOr($config, 'today_button', true)),
            closeButton: $this->boolOr($props, 'closeButton', $this->boolOr($config, 'close_button', true)),
            inline: $this->boolOr($props, 'inline', false),
            placement: $this->stringOr($props, 'placement', $this->stringOr($config, 'placement', 'bottom-start')) ?? 'bottom-start',
            classes: $this->resolveClasses($props, $config),
            theme: $this->resolveTheme($props, $config),
            ariaLabel: $this->stringOr($props, 'ariaLabel', null),
            debounceMs: max(0, $this->intOr($props, 'debounceMs', $this->intOr($config, 'debounce_ms', 200))),
            wire: [
                'model' => $wireModel,
                'modifiers' => $this->normalizeModifiers($props['wireModifiers'] ?? null),
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $props
     * @param  array<string, mixed>  $config
     * @return array{display: string, value: string}
     */
    private function resolveFormats(array $props, array $config, PickerMode $mode, int $hourCycle): array
    {
        /** @var array<string, mixed> $formats */
        $formats = is_array($config['formats'] ?? null) ? $config['formats'] : [];
        /** @var array<string, mixed> $modeFormats */
        $modeFormats = is_array($formats[$mode->value] ?? null) ? $formats[$mode->value] : [];

        $defaultDisplay = $this->stringOr($modeFormats, 'display', $this->fallbackDisplay($mode)) ?? $this->fallbackDisplay($mode);

        if ($hourCycle === 12 && $mode->hasTime()) {
            $defaultDisplay = $this->stringOr($modeFormats, 'display_12', $defaultDisplay) ?? $defaultDisplay;
        }

        $defaultValue = $this->stringOr($modeFormats, 'value', $this->fallbackValue($mode)) ?? $this->fallbackValue($mode);

        return [
            'display' => $this->stringOr($props, 'displayFormat', $defaultDisplay) ?? $defaultDisplay,
            'value' => $this->stringOr($props, 'valueFormat', $defaultValue) ?? $defaultValue,
        ];
    }

    private function fallbackDisplay(PickerMode $mode): string
    {
        return match ($mode) {
            PickerMode::Date => 'Y-m-d',
            PickerMode::Time => 'H:i',
            PickerMode::DateTime => 'Y-m-d H:i',
            PickerMode::Month => 'Y-m',
        };
    }

    private function fallbackValue(PickerMode $mode): string
    {
        return match ($mode) {
            PickerMode::Date => 'Y-m-d',
            PickerMode::Time => 'H:i:s',
            PickerMode::DateTime => 'Y-m-d\TH:i:s',
            PickerMode::Month => 'Y-m',
        };
    }

    private function normalizeValue(?string $raw, PickerMode $mode, string $valueFormat, Locale $locale): ?string
    {
        $dateTime = $this->parseFlexible($raw, $mode, $valueFormat, $locale, isMax: false);

        return $dateTime !== null ? $this->formatForMode($dateTime, $mode, $valueFormat, $locale) : null;
    }

    private function normalizeBoundary(?string $raw, PickerMode $mode, string $valueFormat, Locale $locale, bool $isMax): ?string
    {
        $dateTime = $this->parseFlexible($raw, $mode, $valueFormat, $locale, $isMax);

        return $dateTime !== null ? $this->formatForMode($dateTime, $mode, $valueFormat, $locale) : null;
    }

    private function parseFlexible(?string $raw, PickerMode $mode, string $valueFormat, Locale $locale, bool $isMax): ?DateTimeValue
    {
        if ($raw === null) {
            return null;
        }

        $trimmed = trim($raw);
        if ($trimmed === '') {
            return null;
        }

        // 1. The configured value format first.
        $native = $this->tryParse($trimmed, $valueFormat, $mode, $locale);
        if ($native !== null) {
            return $this->toDateTime($native);
        }

        // 2. Mode-appropriate candidate formats.
        if ($mode === PickerMode::Month) {
            foreach (self::MONTH_CANDIDATES as $candidate) {
                $parsed = $this->tryParse($trimmed, $candidate, PickerMode::Month, $locale);
                if ($parsed instanceof DateValue) {
                    $time = $isMax ? new TimeValue(23, 59, 59) : new TimeValue(0, 0, 0);

                    return new DateTimeValue($parsed, $time);
                }
            }
        }

        if ($mode->hasDate()) {
            foreach (self::DATE_CANDIDATES as $candidate) {
                $parsed = $this->tryParse($trimmed, $candidate, PickerMode::Date, $locale);
                if ($parsed instanceof DateValue) {
                    $time = $isMax ? new TimeValue(23, 59, 59) : new TimeValue(0, 0, 0);

                    return new DateTimeValue($parsed, $time);
                }
            }
        }

        if ($mode === PickerMode::Time) {
            foreach (self::TIME_CANDIDATES as $candidate) {
                $parsed = $this->tryParse($trimmed, $candidate, PickerMode::Time, $locale);
                if ($parsed instanceof TimeValue) {
                    return DateTimeValue::fromTime($parsed);
                }
            }
        }

        return null;
    }

    private function tryParse(string $raw, string $format, PickerMode $mode, Locale $locale): DateValue|TimeValue|DateTimeValue|null
    {
        try {
            return $this->parser->parse($raw, $format, $mode, $locale);
        } catch (InvalidDateFormatException) {
            return null;
        }
    }

    private function formatForMode(DateTimeValue $dateTime, PickerMode $mode, string $valueFormat, Locale $locale): string
    {
        $value = match ($mode) {
            PickerMode::Date, PickerMode::Month => $dateTime->date,
            PickerMode::Time => $dateTime->time,
            PickerMode::DateTime => $dateTime,
        };

        return $this->formatter->format($value, $valueFormat, $locale);
    }

    private function toDateTime(DateValue|TimeValue|DateTimeValue $value): DateTimeValue
    {
        return match (true) {
            $value instanceof DateValue => DateTimeValue::fromDate($value),
            $value instanceof TimeValue => DateTimeValue::fromTime($value),
            default => $value,
        };
    }

    /**
     * @return list<string>
     */
    private function normalizeDisabledDates(mixed $raw, Locale $locale): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $dates = [];

        foreach ($raw as $entry) {
            if (! is_string($entry)) {
                continue;
            }

            foreach (self::DATE_CANDIDATES as $candidate) {
                $parsed = $this->tryParse(trim($entry), $candidate, PickerMode::Date, $locale);
                if ($parsed instanceof DateValue) {
                    $dates[] = $parsed->toIsoString();

                    continue 2;
                }
            }
        }

        return array_values(array_unique($dates));
    }

    /**
     * @return list<int>
     */
    private function normalizeDisabledWeekdays(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $weekdays = [];

        foreach ($raw as $entry) {
            if (! is_int($entry) && ! (is_string($entry) && is_numeric($entry))) {
                continue;
            }

            $weekday = (int) $entry;
            if ($weekday >= 0 && $weekday <= 6) {
                $weekdays[] = $weekday;
            }
        }

        return array_values(array_unique($weekdays));
    }

    /**
     * @return list<array{from: string, to: string}>
     */
    private function normalizeDisabledTimes(mixed $raw, Locale $locale): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $windows = [];

        foreach ($raw as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $from = $this->normalizeTimeString($entry['from'] ?? null, $locale);
            $to = $this->normalizeTimeString($entry['to'] ?? null, $locale);

            if ($from !== null && $to !== null) {
                $windows[] = ['from' => $from, 'to' => $to];
            }
        }

        return $windows;
    }

    private function normalizeTimeString(mixed $raw, Locale $locale): ?string
    {
        if (! is_string($raw)) {
            return null;
        }

        foreach (self::TIME_CANDIDATES as $candidate) {
            $parsed = $this->tryParse(trim($raw), $candidate, PickerMode::Time, $locale);
            if ($parsed instanceof TimeValue) {
                return $parsed->toIsoString();
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function normalizeModifiers(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        return array_values(array_filter(
            array_map(static fn ($modifier): string => is_string($modifier) ? $modifier : '', $raw),
            static fn (string $modifier): bool => $modifier !== '',
        ));
    }

    private function normalizeWeekday(int $weekday): int
    {
        return (($weekday % 7) + 7) % 7;
    }

    /**
     * @param  array<string, mixed>  $props
     * @param  array<string, mixed>  $config
     * @return array<string, string>
     */
    private function resolveClasses(array $props, array $config): array
    {
        /** @var array<string, string> $defaults */
        $defaults = $this->stringMap($config['classes'] ?? null);

        $theme = $this->resolveTheme($props, $config);
        $themeClasses = [];

        if ($theme !== null) {
            /** @var array<string, mixed> $themes */
            $themes = is_array($config['themes'] ?? null) ? $config['themes'] : [];
            $themeClasses = $this->stringMap($themes[$theme] ?? null);
        }

        $instance = $this->stringMap($props['classes'] ?? null);

        return ClassMerger::merge($defaults, $themeClasses, $instance);
    }

    /**
     * @param  array<string, mixed>  $props
     * @param  array<string, mixed>  $config
     */
    private function resolveTheme(array $props, array $config): ?string
    {
        return $this->stringOr($props, 'theme', $this->stringOr($config, 'default_theme', null));
    }

    /**
     * @return array<string, string>
     */
    private function stringMap(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $map = [];

        foreach ($raw as $key => $value) {
            if (is_string($key) && is_string($value)) {
                $map[$key] = $value;
            }
        }

        return $map;
    }

    /**
     * @param  array<string, mixed>  $array
     */
    private function stringOr(array $array, string $key, ?string $default): ?string
    {
        $value = $array[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : $default;
    }

    /**
     * @param  array<string, mixed>  $array
     */
    private function intOr(array $array, string $key, int $default): int
    {
        $value = $array[$key] ?? null;

        if (is_int($value)) {
            return $value;
        }

        return is_string($value) && is_numeric($value) ? (int) $value : $default;
    }

    /**
     * @param  array<string, mixed>  $array
     */
    private function boolOr(array $array, string $key, bool $default): bool
    {
        $value = $array[$key] ?? null;

        return is_bool($value) ? $value : $default;
    }
}
