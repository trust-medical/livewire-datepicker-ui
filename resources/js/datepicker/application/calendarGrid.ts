import { addDays, datesEqual, isWeekend, toIsoDate, weekday } from '../domain/civilDate'
import { isDateDisabled, type CompiledRule } from '../domain/disabledRule'
import type { CivilDate, DayCell } from '../domain/types'

const TOTAL_CELLS = 42

/**
 * Builds the 6×7 month grid (always six weeks for a stable popover height).
 * Mirrors the PHP `CalendarGridBuilder`.
 */
export function buildWeeks(
  year: number,
  month: number,
  firstDayOfWeek: number,
  today: CivilDate,
  selected: CivilDate | null,
  focused: CivilDate | null,
  rule: CompiledRule,
): DayCell[][] {
  const normalizedFirstDay = ((firstDayOfWeek % 7) + 7) % 7
  const firstOfMonth: CivilDate = { year, month, day: 1 }
  const leadingDays = (weekday(firstOfMonth) - normalizedFirstDay + 7) % 7
  const gridStart = addDays(firstOfMonth, -leadingDays)

  const weeks: DayCell[][] = []
  let week: DayCell[] = []

  for (let offset = 0; offset < TOTAL_CELLS; offset++) {
    const date = addDays(gridStart, offset)

    week.push({
      date: toIsoDate(date),
      day: date.day,
      month: date.month,
      year: date.year,
      weekday: weekday(date),
      isToday: datesEqual(date, today),
      isOutsideMonth: date.month !== month,
      isDisabled: isDateDisabled(rule, date),
      isSelected: selected !== null && datesEqual(date, selected),
      isWeekend: isWeekend(date),
      isFocused: focused !== null && datesEqual(date, focused),
    })

    if (week.length === 7) {
      weeks.push(week)
      week = []
    }
  }

  return weeks
}

/** The weekday header order (0..6) starting at the configured first day. */
export function weekdayOrder(firstDayOfWeek: number): number[] {
  const normalized = ((firstDayOfWeek % 7) + 7) % 7
  return Array.from({ length: 7 }, (_, offset) => (normalized + offset) % 7)
}
