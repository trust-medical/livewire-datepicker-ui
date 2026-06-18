<?php

declare(strict_types=1);

namespace TrustMedical\LivewireDatepickerUi\Domain\Services;

use InvalidArgumentException;
use TrustMedical\LivewireDatepickerUi\Domain\Enums\PickerMode;
use TrustMedical\LivewireDatepickerUi\Domain\Exceptions\InvalidDateFormatException;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateTimeValue;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateValue;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\TimeValue;
use TrustMedical\LivewireDatepickerUi\Support\DateFormatTokens;
use TrustMedical\LivewireDatepickerUi\Support\Locale;

/**
 * Parses a string into a value object using the supported PHP date tokens.
 *
 * Builds an anchored regular expression from the format, extracts the component
 * captures, then constructs the value object for the requested mode. Any
 * mismatch or out-of-range component raises {@see InvalidDateFormatException}
 * (never a silent fallback). Mirrors the TypeScript parser.
 */
final class DateParser
{
    public function parse(
        string $input,
        string $format,
        PickerMode $mode,
        ?Locale $locale = null,
    ): DateValue|TimeValue|DateTimeValue {
        $locale ??= Locale::english();

        $pattern = '';
        /** @var list<string> $order */
        $order = [];

        foreach (DateFormatTokens::tokenize($format) as $segment) {
            if ($segment['type'] === 'literal') {
                $pattern .= preg_quote($segment['value'], '/');

                continue;
            }

            $pattern .= $this->tokenPattern($segment['value'], $locale);
            $order[] = $segment['value'];
        }

        if (preg_match('/^' . $pattern . '$/iu', trim($input), $matches) !== 1) {
            throw InvalidDateFormatException::forInput($input, $format);
        }

        /** @var array<string, string> $captures */
        $captures = [];
        foreach ($order as $index => $token) {
            $captures[$token] = $matches[$index + 1] ?? '';
        }

        return $this->build($captures, $mode, $locale, $input, $format);
    }

    private function tokenPattern(string $token, Locale $locale): string
    {
        return match ($token) {
            'Y' => '(\d{4})',
            'y' => '(\d{2})',
            'm' => '(\d{2})',
            'n' => '(\d{1,2})',
            'd' => '(\d{2})',
            'j' => '(\d{1,2})',
            'H' => '(\d{2})',
            'G' => '(\d{1,2})',
            'h' => '(\d{2})',
            'g' => '(\d{1,2})',
            'i' => '(\d{2})',
            's' => '(\d{2})',
            'N' => '([1-7])',
            'w' => '([0-6])',
            'A', 'a' => '([AaPp][Mm])',
            'D' => $this->namesAlternation($locale->weekdaysShort),
            'l' => $this->namesAlternation($locale->weekdays),
            'M' => $this->namesAlternation($locale->monthsShort),
            'F' => $this->namesAlternation($locale->months),
            default => preg_quote($token, '/'),
        };
    }

    /** @param list<string> $names */
    private function namesAlternation(array $names): string
    {
        $quoted = array_map(static fn (string $name): string => preg_quote($name, '/'), $names);

        return '(' . implode('|', $quoted) . ')';
    }

    /**
     * @param  array<string, string>  $captures
     */
    private function build(
        array $captures,
        PickerMode $mode,
        Locale $locale,
        string $input,
        string $format,
    ): DateValue|TimeValue|DateTimeValue {
        try {
            $date = $mode->hasDate() ? $this->buildDate($captures, $locale, $input, $format) : null;
            $time = $mode->hasTime() ? $this->buildTime($captures, $input, $format) : null;
        } catch (InvalidArgumentException) {
            throw InvalidDateFormatException::forInput($input, $format);
        }

        return match ($mode) {
            PickerMode::Date => $date ?? throw InvalidDateFormatException::forInput($input, $format),
            PickerMode::Time => $time ?? throw InvalidDateFormatException::forInput($input, $format),
            PickerMode::DateTime => new DateTimeValue(
                $date ?? throw InvalidDateFormatException::forInput($input, $format),
                $time ?? throw InvalidDateFormatException::forInput($input, $format),
            ),
        };
    }

    /** @param array<string, string> $captures */
    private function buildDate(array $captures, Locale $locale, string $input, string $format): DateValue
    {
        $year = $this->resolveYear($captures);
        $month = $this->resolveMonth($captures, $locale);
        $day = $this->resolveDay($captures);

        if ($year === null || $month === null || $day === null) {
            throw InvalidDateFormatException::forInput($input, $format);
        }

        return new DateValue($year, $month, $day);
    }

    /** @param array<string, string> $captures */
    private function buildTime(array $captures, string $input, string $format): TimeValue
    {
        $minute = isset($captures['i']) && $captures['i'] !== '' ? (int) $captures['i'] : null;
        $second = isset($captures['s']) && $captures['s'] !== '' ? (int) $captures['s'] : 0;
        $hour = $this->resolveHour($captures);

        if ($hour === null || $minute === null) {
            throw InvalidDateFormatException::forInput($input, $format);
        }

        return new TimeValue($hour, $minute, $second);
    }

    /** @param array<string, string> $captures */
    private function resolveYear(array $captures): ?int
    {
        if (isset($captures['Y']) && $captures['Y'] !== '') {
            return (int) $captures['Y'];
        }

        if (isset($captures['y']) && $captures['y'] !== '') {
            // Pivot two-digit years into the 2000-2099 century.
            return 2000 + (int) $captures['y'];
        }

        return null;
    }

    /** @param array<string, string> $captures */
    private function resolveMonth(array $captures, Locale $locale): ?int
    {
        foreach (['m', 'n'] as $token) {
            if (isset($captures[$token]) && $captures[$token] !== '') {
                return (int) $captures[$token];
            }
        }

        foreach (['F', 'M'] as $token) {
            if (isset($captures[$token]) && $captures[$token] !== '') {
                $month = $this->monthFromName($captures[$token], $locale);

                if ($month !== null) {
                    return $month;
                }
            }
        }

        return null;
    }

    /** @param array<string, string> $captures */
    private function resolveDay(array $captures): ?int
    {
        foreach (['d', 'j'] as $token) {
            if (isset($captures[$token]) && $captures[$token] !== '') {
                return (int) $captures[$token];
            }
        }

        return null;
    }

    /** @param array<string, string> $captures */
    private function resolveHour(array $captures): ?int
    {
        foreach (['H', 'G'] as $token) {
            if (isset($captures[$token]) && $captures[$token] !== '') {
                return (int) $captures[$token];
            }
        }

        foreach (['h', 'g'] as $token) {
            if (isset($captures[$token]) && $captures[$token] !== '') {
                $hour12 = (int) $captures[$token] % 12;
                $meridiem = $this->resolveMeridiem($captures);

                if ($meridiem === 'PM') {
                    return $hour12 + 12;
                }

                if ($meridiem === 'AM') {
                    return $hour12;
                }

                // No meridiem token: take the 12-hour value as-is.
                return (int) $captures[$token];
            }
        }

        return null;
    }

    /** @param array<string, string> $captures */
    private function resolveMeridiem(array $captures): ?string
    {
        $raw = $captures['A'] ?? $captures['a'] ?? '';

        if ($raw === '') {
            return null;
        }

        return strtoupper($raw[0]) === 'P' ? 'PM' : 'AM';
    }

    private function monthFromName(string $name, Locale $locale): ?int
    {
        $needle = mb_strtolower(trim($name));

        foreach ($locale->months as $index => $month) {
            if (mb_strtolower($month) === $needle) {
                return $index + 1;
            }
        }

        foreach ($locale->monthsShort as $index => $month) {
            if (mb_strtolower($month) === $needle) {
                return $index + 1;
            }
        }

        return null;
    }
}
