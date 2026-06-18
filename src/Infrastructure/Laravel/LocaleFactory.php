<?php

declare(strict_types=1);

namespace TrustMedical\LivewireDatepickerUi\Infrastructure\Laravel;

use Illuminate\Contracts\Translation\Translator;
use TrustMedical\LivewireDatepickerUi\Support\Locale;

/**
 * Builds a {@see Locale} from the package translation files, falling back to the
 * English defaults for any missing key.
 */
final class LocaleFactory
{
    public function __construct(private readonly Translator $translator) {}

    public function make(string $locale, int $firstDayOfWeek): Locale
    {
        $english = Locale::english();

        return new Locale(
            code: $locale,
            months: $this->list('datepicker::datepicker.months', $locale, $english->months),
            monthsShort: $this->list('datepicker::datepicker.months_short', $locale, $english->monthsShort),
            weekdays: $this->list('datepicker::datepicker.weekdays', $locale, $english->weekdays),
            weekdaysShort: $this->list('datepicker::datepicker.weekdays_short', $locale, $english->weekdaysShort),
            weekdaysMin: $this->list('datepicker::datepicker.weekdays_min', $locale, $english->weekdaysMin),
            firstDayOfWeek: (($firstDayOfWeek % 7) + 7) % 7,
            labels: $this->map('datepicker::datepicker.labels', $locale, $english->labels),
        );
    }

    /**
     * @param  list<string>  $fallback
     * @return list<string>
     */
    private function list(string $key, string $locale, array $fallback): array
    {
        $value = $this->translator->get($key, [], $locale);

        if (is_array($value) && array_is_list($value)) {
            return array_map(static fn ($item): string => is_scalar($item) ? (string) $item : '', $value);
        }

        return $fallback;
    }

    /**
     * @param  array<string, string>  $fallback
     * @return array<string, string>
     */
    private function map(string $key, string $locale, array $fallback): array
    {
        $value = $this->translator->get($key, [], $locale);

        if (! is_array($value)) {
            return $fallback;
        }

        $merged = $fallback;

        foreach ($value as $itemKey => $itemValue) {
            if (is_string($itemKey) && is_string($itemValue)) {
                $merged[$itemKey] = $itemValue;
            }
        }

        return $merged;
    }
}
