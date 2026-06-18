/** The minimal slice of the Alpine API the package relies on. */
export declare interface AlpineInstance {
    data(name: string, callback: (...args: unknown[]) => unknown): void;
}

/** Alpine magics the component leans on (all injected at runtime by Alpine). */
declare interface AlpineMagics {
    $refs?: Record<string, HTMLElement>;
    $nextTick?: (callback: () => void) => void;
    $watch?: (property: string, callback: (value: unknown) => void) => void;
    $wire?: unknown;
    $el?: HTMLElement;
}

/** A timezone-free calendar date. */
export declare interface CivilDate {
    year: number;
    month: number;
    day: number;
}

/** A timezone-free wall-clock time. */
export declare interface CivilTime {
    hour: number;
    minute: number;
    second: number;
}

/** Pre-compiled constraints, mirroring the PHP `DisabledRule`. */
declare interface CompiledRule {
    mode: PickerMode;
    min: PickerValue | null;
    max: PickerValue | null;
    disabledDates: Set<string>;
    disabledWeekdays: Set<number>;
    disabledTimes: TimeWindow[];
    minuteStep: number;
}

export declare function createDatepickerComponent(config: DatepickerConfig): DatepickerComponent;

export declare const DATA_NAME = "datepicker";

/** Alpine plugin entry: `Alpine.plugin(datepicker)`. */
declare function datepicker(Alpine: AlpineInstance): void;
export default datepicker;

export declare interface DatepickerComponent extends AlpineMagics {
    config: DatepickerConfig;
    rule: CompiledRule;
    today: CivilDate;
    isOpen: boolean;
    display: string;
    status: SelectionStatus;
    viewYear: number;
    viewMonth: number;
    focusedDate: CivilDate;
    focusedMonth: number;
    selectedValue: PickerValue | null;
    committedValue: PickerValue | null;
    generation: number;
    bridge: LivewireBridge;
    unwatch: (() => void) | null;
    readonly value: string | null;
    readonly hasValue: boolean;
    readonly weeks: DayCell[][];
    readonly monthCells: MonthCell[];
    readonly timeOptions: TimeOption[];
    init(): void;
    destroy(): void;
    open(): void;
    close(restoreFocus?: boolean): void;
    toggle(): void;
    previousMonth(): void;
    nextMonth(): void;
    previousYear(): void;
    nextYear(): void;
    selectDay(day: DayCell): void;
    selectDate(date: CivilDate): void;
    selectMonth(year: number, month: number): void;
    selectTime(option: TimeOption): void;
    goToday(): void;
    clear(): void;
    onType(): void;
    onInputKeydown(event: KeyboardEvent): void;
    onGridKeydown(event: KeyboardEvent): void;
    onMonthGridKeydown(event: KeyboardEvent): void;
    dayClass(day: DayCell): string;
    monthCellClass(cell: MonthCell): string;
    timeClass(option: TimeOption): string;
    commit(): void;
    emitChange(): void;
    reconcile(model: string, sent: string | null): void;
    syncFromServer(value: string | null): void;
    onServerChange(value: unknown): void;
    valueString(): string | null;
    formatDisplay(value: PickerValue): string;
    updateDisplay(): void;
    syncViewToSelection(): void;
    clampFocus(): void;
    focusActive(): void;
    announce(key: string): void;
    selectedDate(): CivilDate | null;
}

/** The JSON configuration emitted by the PHP component (camelCase, 1:1 with PickerConfig::toClientArray). */
export declare interface DatepickerConfig {
    id: string;
    name: string | null;
    mode: PickerMode;
    displayFormat: string;
    valueFormat: string;
    locale: LocaleData;
    firstDayOfWeek: number;
    value: string | null;
    min: string | null;
    max: string | null;
    disabledDates: string[];
    disabledWeekdays: number[];
    disabledTimes: DisabledTimeWindow[];
    minuteStep: number;
    hourCycle: 12 | 24;
    placeholder: string | null;
    disabled: boolean;
    readonly: boolean;
    required: boolean;
    clearable: boolean;
    todayButton: boolean;
    closeButton: boolean;
    inline: boolean;
    placement: string;
    classes: Record<string, string>;
    theme: string | null;
    ariaLabel: string | null;
    debounceMs: number;
    wire: WireBinding;
}

/** One rendered calendar cell. */
export declare interface DayCell {
    date: string;
    day: number;
    month: number;
    year: number;
    weekday: number;
    isToday: boolean;
    isOutsideMonth: boolean;
    isDisabled: boolean;
    isSelected: boolean;
    isWeekend: boolean;
    isFocused: boolean;
}

export declare interface DisabledTimeWindow {
    from: string;
    to: string;
}

/** English fallback locale — mirrors PHP `Locale::english()`. */
export declare const ENGLISH_LOCALE: LocaleData;

/**
 * Formats a value into a string using the supported PHP date tokens. Mirrors the
 * PHP `DateFormatter` so the value-format string the client writes is byte-for-byte
 * what the server parses.
 */
export declare function format(value: PickerValue, formatString: string, locale: LocaleData): string;

/**
 * Thin abstraction over the Livewire `$wire` object so optimistic updates and
 * rollback work across Livewire 3 and 4 (and degrade gracefully to a no-op when
 * Livewire is absent). Only stable surface — get/set/errors — is used; no
 * version-specific internals.
 */
declare interface LivewireBridge {
    isAvailable(): boolean;
    get(model: string): unknown;
    set(model: string, value: unknown, live: boolean): Promise<void>;
    hasErrorFor(model: string): boolean;
    /** Subscribe to external server-driven changes; returns an unsubscribe fn if supported. */
    watch(model: string, callback: (value: unknown) => void): (() => void) | null;
}

export declare interface LocaleData {
    code: string;
    months: string[];
    monthsShort: string[];
    weekdays: string[];
    weekdaysShort: string[];
    weekdaysMin: string[];
    firstDayOfWeek: number;
    labels: Record<string, string>;
}

/** One rendered cell in the month-mode grid (year + month only). */
export declare interface MonthCell {
    year: number;
    month: number;
    label: string;
    isToday: boolean;
    isSelected: boolean;
    isDisabled: boolean;
    isFocused: boolean;
}

/**
 * Parses a string into a {@link PickerValue} using the supported PHP date
 * tokens. Mirrors the PHP `DateParser`: an anchored, case-insensitive,
 * unicode-aware regex is built from the format, then validated component by
 * component. Returns an explicit result instead of throwing.
 */
export declare function parse(input: string, formatString: string, mode: PickerMode, locale: LocaleData): ParseResult<PickerValue>;

/** Result of a parse attempt — explicit success/failure, never a thrown error. */
export declare type ParseResult<T> = {
    ok: true;
    value: T;
} | {
    ok: false;
    error: string;
};

export declare type PickerMode = 'date' | 'time' | 'datetime' | 'month';

/** The canonical value carried through the picker: a date and/or a time. */
export declare interface PickerValue {
    date: CivilDate | null;
    time: CivilTime | null;
}

/** Register the `datepicker` Alpine component. Call once, on `alpine:init`. */
export declare function registerDatepicker(Alpine: AlpineInstance): void;

export declare type SelectionStatus = 'idle' | 'pending' | 'committed' | 'rejected' | 'invalid';

/** One option in the time list. */
export declare interface TimeOption {
    value: string;
    label: string;
    isSelected: boolean;
    isDisabled: boolean;
}

declare interface TimeWindow {
    from: CivilTime;
    to: CivilTime;
}

export declare interface WireBinding {
    model: string | null;
    modifiers: string[];
}

export { }
