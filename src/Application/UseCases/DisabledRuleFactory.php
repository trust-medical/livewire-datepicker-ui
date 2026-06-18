<?php

declare(strict_types=1);

namespace TrustMedical\LivewireDatepickerUi\Application\UseCases;

use TrustMedical\LivewireDatepickerUi\Application\DTO\PickerConfig;
use TrustMedical\LivewireDatepickerUi\Domain\Enums\PickerMode;
use TrustMedical\LivewireDatepickerUi\Domain\Exceptions\InvalidDateFormatException;
use TrustMedical\LivewireDatepickerUi\Domain\Services\DateParser;
use TrustMedical\LivewireDatepickerUi\Domain\Services\DisabledRule;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateRange;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateTimeValue;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateValue;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\TimeValue;

/**
 * Rebuilds a {@see DisabledRule} from a normalized {@see PickerConfig} so the
 * server can render the initial grid (and validate) using the exact same
 * constraints the client enforces.
 */
final class DisabledRuleFactory
{
    public function __construct(
        private readonly DateParser $parser = new DateParser,
    ) {}

    public function fromConfig(PickerConfig $config): DisabledRule
    {
        if ($config->mode === PickerMode::Time) {
            return new DisabledRule(
                range: DateRange::unbounded(),
                disabledTimes: $this->disabledTimes($config),
                minuteStep: $config->minuteStep,
                minTime: $this->parseTime($config->min),
                maxTime: $this->parseTime($config->max),
            );
        }

        return new DisabledRule(
            range: new DateRange(
                $this->parseDateTime($config->min, $config->mode),
                $this->parseDateTime($config->max, $config->mode),
            ),
            disabledDates: $this->disabledDates($config),
            disabledWeekdays: $config->disabledWeekdays,
            disabledTimes: $this->disabledTimes($config),
            minuteStep: $config->minuteStep,
        );
    }

    /**
     * @return list<DateValue>
     */
    private function disabledDates(PickerConfig $config): array
    {
        $dates = [];

        foreach ($config->disabledDates as $iso) {
            $date = $this->parseIsoDate($iso);
            if ($date !== null) {
                $dates[] = $date;
            }
        }

        return $dates;
    }

    /**
     * @return list<array{from: TimeValue, to: TimeValue}>
     */
    private function disabledTimes(PickerConfig $config): array
    {
        $windows = [];

        foreach ($config->disabledTimes as $window) {
            $from = $this->parseTime($window['from']);
            $to = $this->parseTime($window['to']);

            if ($from !== null && $to !== null) {
                $windows[] = ['from' => $from, 'to' => $to];
            }
        }

        return $windows;
    }

    private function parseDateTime(?string $value, PickerMode $mode): ?DateTimeValue
    {
        if ($value === null) {
            return null;
        }

        // The normalized boundary is already a value-format string for the mode.
        $candidates = match ($mode) {
            PickerMode::DateTime => ['Y-m-d\TH:i:s', 'Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d'],
            PickerMode::Month => ['Y-m'],
            default => ['Y-m-d'],
        };

        foreach ($candidates as $format) {
            $parseMode = match ($format) {
                'Y-m' => PickerMode::Month,
                'Y-m-d' => PickerMode::Date,
                default => PickerMode::DateTime,
            };

            try {
                $parsed = $this->parser->parse($value, $format, $parseMode);

                return $parsed instanceof DateValue
                    ? DateTimeValue::fromDate($parsed)
                    : ($parsed instanceof DateTimeValue ? $parsed : null);
            } catch (InvalidDateFormatException) {
                continue;
            }
        }

        return null;
    }

    private function parseIsoDate(string $value): ?DateValue
    {
        try {
            $parsed = $this->parser->parse($value, 'Y-m-d', PickerMode::Date);

            return $parsed instanceof DateValue ? $parsed : null;
        } catch (InvalidDateFormatException) {
            return null;
        }
    }

    private function parseTime(?string $value): ?TimeValue
    {
        if ($value === null) {
            return null;
        }

        foreach (['H:i:s', 'H:i'] as $format) {
            try {
                $parsed = $this->parser->parse($value, $format, PickerMode::Time);

                if ($parsed instanceof TimeValue) {
                    return $parsed;
                }
            } catch (InvalidDateFormatException) {
                continue;
            }
        }

        return null;
    }
}
