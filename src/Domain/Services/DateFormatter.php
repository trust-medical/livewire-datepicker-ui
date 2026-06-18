<?php

declare(strict_types=1);

namespace TrustMedical\LivewireDatepickerUi\Domain\Services;

use TrustMedical\LivewireDatepickerUi\Domain\Exceptions\InvalidConfigurationException;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateTimeValue;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateValue;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\TimeValue;
use TrustMedical\LivewireDatepickerUi\Support\DateFormatTokens;
use TrustMedical\LivewireDatepickerUi\Support\Locale;

/**
 * Formats a value into a string using the supported PHP date tokens.
 *
 * Operates purely on the integer components of the value objects, so a date-only
 * value is never routed through a timezone-aware `DateTime`. The token output is
 * mirrored exactly by the TypeScript formatter.
 */
final class DateFormatter
{
    public function format(
        DateValue|TimeValue|DateTimeValue $value,
        string $format,
        ?Locale $locale = null,
    ): string {
        $locale ??= Locale::english();
        [$date, $time] = $this->split($value);

        $output = '';

        foreach (DateFormatTokens::tokenize($format) as $segment) {
            if ($segment['type'] === 'literal') {
                $output .= $segment['value'];

                continue;
            }

            $output .= $this->renderToken($segment['value'], $date, $time, $locale);
        }

        return $output;
    }

    /** @return array{0: ?DateValue, 1: ?TimeValue} */
    private function split(DateValue|TimeValue|DateTimeValue $value): array
    {
        return match (true) {
            $value instanceof DateValue => [$value, null],
            $value instanceof TimeValue => [null, $value],
            default => [$value->date, $value->time],
        };
    }

    private function renderToken(string $token, ?DateValue $date, ?TimeValue $time, Locale $locale): string
    {
        if (DateFormatTokens::isDateToken($token)) {
            if ($date === null) {
                throw new InvalidConfigurationException(
                    "Format token \"{$token}\" requires a date but the value has none.",
                );
            }

            return $this->renderDateToken($token, $date, $locale);
        }

        if ($time === null) {
            throw new InvalidConfigurationException(
                "Format token \"{$token}\" requires a time but the value has none.",
            );
        }

        return $this->renderTimeToken($token, $time, $locale);
    }

    private function renderDateToken(string $token, DateValue $date, Locale $locale): string
    {
        return match ($token) {
            'Y' => sprintf('%04d', $date->year),
            'y' => str_pad((string) ($date->year % 100), 2, '0', STR_PAD_LEFT),
            'm' => sprintf('%02d', $date->month),
            'n' => (string) $date->month,
            'd' => sprintf('%02d', $date->day),
            'j' => (string) $date->day,
            'N' => (string) $date->isoDayOfWeek(),
            'w' => (string) $date->dayOfWeek(),
            'D' => $locale->weekdayShort($date->dayOfWeek()),
            'l' => $locale->weekdayName($date->dayOfWeek()),
            'M' => $locale->monthShort($date->month),
            'F' => $locale->monthName($date->month),
            default => $token,
        };
    }

    private function renderTimeToken(string $token, TimeValue $time, Locale $locale): string
    {
        return match ($token) {
            'H' => sprintf('%02d', $time->hour),
            'G' => (string) $time->hour,
            'h' => sprintf('%02d', $time->hour12()),
            'g' => (string) $time->hour12(),
            'i' => sprintf('%02d', $time->minute),
            's' => sprintf('%02d', $time->second),
            'A' => $time->isPm() ? $locale->label('pm_upper', 'PM') : $locale->label('am_upper', 'AM'),
            'a' => $time->isPm() ? $locale->label('pm_lower', 'pm') : $locale->label('am_lower', 'am'),
            default => $token,
        };
    }
}
