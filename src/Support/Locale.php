<?php

declare(strict_types=1);

namespace TrustMedical\LivewireDatepickerUi\Support;

/**
 * Immutable bag of localized names and UI labels used during server-side
 * rendering and serialized to the client for runtime formatting.
 */
final class Locale
{
    /**
     * @param  list<string>  $months  Full month names, index 0..11 (January..December).
     * @param  list<string>  $monthsShort  Short month names, index 0..11.
     * @param  list<string>  $weekdays  Full weekday names, index 0..6 (Sunday..Saturday).
     * @param  list<string>  $weekdaysShort  Short weekday names, index 0..6.
     * @param  list<string>  $weekdaysMin  Minimal weekday names, index 0..6.
     * @param  array<string, string>  $labels  UI labels (today, clear, close, ...).
     */
    public function __construct(
        public readonly string $code,
        public readonly array $months,
        public readonly array $monthsShort,
        public readonly array $weekdays,
        public readonly array $weekdaysShort,
        public readonly array $weekdaysMin,
        public readonly int $firstDayOfWeek,
        public readonly array $labels,
    ) {}

    /** @param int $month 1..12 */
    public function monthName(int $month): string
    {
        return $this->months[$month - 1] ?? '';
    }

    /** @param int $month 1..12 */
    public function monthShort(int $month): string
    {
        return $this->monthsShort[$month - 1] ?? $this->monthName($month);
    }

    /** @param int $weekday 0..6 (Sunday..Saturday) */
    public function weekdayName(int $weekday): string
    {
        return $this->weekdays[$weekday] ?? '';
    }

    /** @param int $weekday 0..6 (Sunday..Saturday) */
    public function weekdayShort(int $weekday): string
    {
        return $this->weekdaysShort[$weekday] ?? $this->weekdayName($weekday);
    }

    /** @param int $weekday 0..6 (Sunday..Saturday) */
    public function weekdayMin(int $weekday): string
    {
        return $this->weekdaysMin[$weekday] ?? $this->weekdayShort($weekday);
    }

    public function label(string $key, string $default = ''): string
    {
        return $this->labels[$key] ?? $default;
    }

    /**
     * @return array{
     *     code: string,
     *     months: list<string>,
     *     monthsShort: list<string>,
     *     weekdays: list<string>,
     *     weekdaysShort: list<string>,
     *     weekdaysMin: list<string>,
     *     firstDayOfWeek: int,
     *     labels: array<string, string>
     * }
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'months' => $this->months,
            'monthsShort' => $this->monthsShort,
            'weekdays' => $this->weekdays,
            'weekdaysShort' => $this->weekdaysShort,
            'weekdaysMin' => $this->weekdaysMin,
            'firstDayOfWeek' => $this->firstDayOfWeek,
            'labels' => $this->labels,
        ];
    }

    /**
     * English fallback locale. Used when no translations are available so
     * formatting never produces empty textual tokens.
     */
    public static function english(): self
    {
        return new self(
            code: 'en',
            months: [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December',
            ],
            monthsShort: [
                'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
            ],
            weekdays: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
            weekdaysShort: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
            weekdaysMin: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
            firstDayOfWeek: 0,
            labels: [
                'today' => 'Today',
                'clear' => 'Clear',
                'close' => 'Close',
                'previousMonth' => 'Previous month',
                'nextMonth' => 'Next month',
                'previousYear' => 'Previous year',
                'nextYear' => 'Next year',
                'chooseDate' => 'Choose date',
                'chooseTime' => 'Choose time',
                'selectedDate' => 'Selected date',
                'monthSelect' => 'Select month',
                'yearSelect' => 'Select year',
            ],
        );
    }
}
