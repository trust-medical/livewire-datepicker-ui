import { describe, expect, it } from 'vitest'
import {
  addDays,
  addMonths,
  daysInMonth,
  isLeapYear,
  isWeekend,
  isoWeekday,
  toIsoDate,
  weekday,
} from '@datepicker/domain/civilDate'

describe('civilDate', () => {
  it('computes the weekday as 0=Sunday..6=Saturday (matching PHP)', () => {
    expect(weekday({ year: 2026, month: 6, day: 18 })).toBe(4) // Thursday
    expect(weekday({ year: 2000, month: 1, day: 1 })).toBe(6) // Saturday
    expect(weekday({ year: 2024, month: 2, day: 29 })).toBe(4)
    expect(isoWeekday({ year: 2026, month: 6, day: 14 })).toBe(7) // Sunday
  })

  it('detects leap years and month lengths', () => {
    expect(isLeapYear(2024)).toBe(true)
    expect(isLeapYear(1900)).toBe(false)
    expect(isLeapYear(2000)).toBe(true)
    expect(daysInMonth(2024, 2)).toBe(29)
    expect(daysInMonth(2023, 2)).toBe(28)
  })

  it('adds days without timezone drift', () => {
    expect(toIsoDate(addDays({ year: 2026, month: 12, day: 31 }, 1))).toBe('2027-01-01')
    expect(toIsoDate(addDays({ year: 2026, month: 3, day: 1 }, -1))).toBe('2026-02-28')
    expect(toIsoDate(addDays({ year: 2024, month: 2, day: 28 }, 1))).toBe('2024-02-29')
  })

  it('adds months clamping the day', () => {
    expect(toIsoDate(addMonths({ year: 2026, month: 1, day: 31 }, 1))).toBe('2026-02-28')
    expect(toIsoDate(addMonths({ year: 2026, month: 12, day: 15 }, 1))).toBe('2027-01-15')
    expect(toIsoDate(addMonths({ year: 2026, month: 1, day: 15 }, -2))).toBe('2025-11-15')
  })

  it('flags weekends', () => {
    expect(isWeekend({ year: 2026, month: 6, day: 13 })).toBe(true) // Saturday
    expect(isWeekend({ year: 2026, month: 6, day: 15 })).toBe(false) // Monday
  })
})
