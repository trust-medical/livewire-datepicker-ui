import type { CompiledRule } from '../domain/disabledRule'
import type { CivilDate, LocaleData, MonthCell } from '../domain/types'

function monthIndex(year: number, month: number): number {
  return year * 12 + (month - 1)
}

/**
 * Whether a month is out of range (before min / after max) at month granularity.
 * Day-level rules (disabled weekdays / dates) do not apply in month mode.
 */
export function isMonthDisabled(rule: CompiledRule, year: number, month: number): boolean {
  const index = monthIndex(year, month)

  const minDate = rule.min?.date ?? null
  if (minDate !== null && index < monthIndex(minDate.year, minDate.month)) {
    return true
  }

  const maxDate = rule.max?.date ?? null
  if (maxDate !== null && index > monthIndex(maxDate.year, maxDate.month)) {
    return true
  }

  return false
}

/**
 * Builds the 12 month cells for the given year (the month-mode grid). The
 * day-grid counterpart is `buildWeeks`; here only the min/max range constrains
 * selection, compared at month granularity.
 */
export function buildMonths(
  year: number,
  locale: LocaleData,
  today: CivilDate,
  selected: CivilDate | null,
  focusedMonth: number,
  rule: CompiledRule,
): MonthCell[] {
  const cells: MonthCell[] = []

  for (let month = 1; month <= 12; month++) {
    cells.push({
      year,
      month,
      label: locale.monthsShort[month - 1] ?? String(month),
      isToday: today.year === year && today.month === month,
      isSelected: selected !== null && selected.year === year && selected.month === month,
      isDisabled: isMonthDisabled(rule, year, month),
      isFocused: focusedMonth === month,
    })
  }

  return cells
}
