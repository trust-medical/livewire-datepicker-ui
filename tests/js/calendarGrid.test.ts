import { describe, expect, it } from 'vitest'
import { buildWeeks, weekdayOrder } from '@datepicker/application/calendarGrid'
import { compileRule } from '@datepicker/domain/disabledRule'
import { makeConfig } from './helpers'

const today = { year: 2026, month: 6, day: 18 }

describe('calendarGrid', () => {
  it('always builds a stable six-week grid', () => {
    const rule = compileRule(makeConfig())
    const weeks = buildWeeks(2026, 6, 0, today, null, null, rule)

    expect(weeks).toHaveLength(6)
    expect(weeks.every((week) => week.length === 7)).toBe(true)
  })

  it('aligns to a Sunday first day of week', () => {
    const rule = compileRule(makeConfig())
    const weeks = buildWeeks(2026, 6, 0, today, null, null, rule)

    // June 2026 starts on a Monday; Sunday-first grid starts 2026-05-31.
    expect(weeks[0]?.[0]?.date).toBe('2026-05-31')
    expect(weeks[0]?.[0]?.isOutsideMonth).toBe(true)
    expect(weekdayOrder(0)).toEqual([0, 1, 2, 3, 4, 5, 6])
  })

  it('aligns to a Monday first day of week', () => {
    const rule = compileRule(makeConfig())
    const weeks = buildWeeks(2026, 6, 1, today, null, null, rule)

    expect(weeks[0]?.[0]?.date).toBe('2026-06-01')
    expect(weekdayOrder(1)).toEqual([1, 2, 3, 4, 5, 6, 0])
  })

  it('flags today, selected, disabled and focused cells', () => {
    const rule = compileRule(makeConfig({ min: '2026-06-10' }))
    const weeks = buildWeeks(2026, 6, 0, today, today, today, rule)
    const cells = weeks.flat()
    const byDate = Object.fromEntries(cells.map((cell) => [cell.date, cell]))

    expect(byDate['2026-06-18']?.isToday).toBe(true)
    expect(byDate['2026-06-18']?.isSelected).toBe(true)
    expect(byDate['2026-06-18']?.isFocused).toBe(true)
    expect(byDate['2026-06-05']?.isDisabled).toBe(true) // before min
    expect(byDate['2026-06-18']?.isDisabled).toBe(false)
  })
})
