/** A timezone-free calendar date. */
export interface CivilDate {
  year: number
  month: number // 1-12
  day: number // 1-31
}

/** A timezone-free wall-clock time. */
export interface CivilTime {
  hour: number // 0-23
  minute: number // 0-59
  second: number // 0-59
}

/** The canonical value carried through the picker: a date and/or a time. */
export interface PickerValue {
  date: CivilDate | null
  time: CivilTime | null
}

export type PickerMode = 'date' | 'time' | 'datetime'

export type SelectionStatus = 'idle' | 'pending' | 'committed' | 'rejected' | 'invalid'

/** Result of a parse attempt — explicit success/failure, never a thrown error. */
export type ParseResult<T> = { ok: true; value: T } | { ok: false; error: string }

export interface LocaleData {
  code: string
  months: string[]
  monthsShort: string[]
  weekdays: string[]
  weekdaysShort: string[]
  weekdaysMin: string[]
  firstDayOfWeek: number
  labels: Record<string, string>
}

export interface DisabledTimeWindow {
  from: string // H:i:s
  to: string // H:i:s
}

export interface WireBinding {
  model: string | null
  modifiers: string[]
}

/** The JSON configuration emitted by the PHP component (camelCase, 1:1 with PickerConfig::toClientArray). */
export interface DatepickerConfig {
  id: string
  name: string | null
  mode: PickerMode
  displayFormat: string
  valueFormat: string
  locale: LocaleData
  firstDayOfWeek: number
  value: string | null
  min: string | null
  max: string | null
  disabledDates: string[]
  disabledWeekdays: number[]
  disabledTimes: DisabledTimeWindow[]
  minuteStep: number
  hourCycle: 12 | 24
  placeholder: string | null
  disabled: boolean
  readonly: boolean
  required: boolean
  clearable: boolean
  todayButton: boolean
  closeButton: boolean
  inline: boolean
  placement: string
  classes: Record<string, string>
  theme: string | null
  ariaLabel: string | null
  debounceMs: number
  wire: WireBinding
}

/** One rendered calendar cell. */
export interface DayCell {
  date: string // YYYY-MM-DD
  day: number
  month: number
  year: number
  weekday: number
  isToday: boolean
  isOutsideMonth: boolean
  isDisabled: boolean
  isSelected: boolean
  isWeekend: boolean
  isFocused: boolean
}

/** One option in the time list. */
export interface TimeOption {
  value: string // H:i:s
  label: string
  isSelected: boolean
  isDisabled: boolean
}
