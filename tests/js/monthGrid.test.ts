import { describe, expect, it } from 'vitest'
import { buildMonths, isMonthDisabled } from '@datepicker/application/monthGrid'
import { compileRule } from '@datepicker/domain/disabledRule'
import { ENGLISH_LOCALE } from '@datepicker/domain/locale'
import { makeConfig } from './helpers'

const today = { year: 2026, month: 6, day: 18 }

const monthRule = (overrides = {}) =>
  compileRule(makeConfig({ mode: 'month', valueFormat: 'Y-m', ...overrides }))

describe('buildMonths', () => {
  it('builds 12 labelled cells with today / selected / focused flags', () => {
    const cells = buildMonths(
      2026,
      ENGLISH_LOCALE,
      today,
      { year: 2026, month: 3, day: 1 },
      6,
      monthRule(),
    )

    expect(cells).toHaveLength(12)
    expect(cells[0]!.label).toBe('Jan')
    expect(cells[5]!.isToday).toBe(true) // June
    expect(cells[2]!.isSelected).toBe(true) // March
    expect(cells[5]!.isFocused).toBe(true)
    expect(cells.every((cell) => !cell.isDisabled)).toBe(true)
  })

  it('disables months outside the min/max range at month granularity', () => {
    const cells = buildMonths(
      2026,
      ENGLISH_LOCALE,
      today,
      null,
      6,
      monthRule({ min: '2026-04', max: '2026-09' }),
    )

    expect(cells[2]!.isDisabled).toBe(true) // March < April
    expect(cells[3]!.isDisabled).toBe(false) // April == min
    expect(cells[8]!.isDisabled).toBe(false) // September == max
    expect(cells[9]!.isDisabled).toBe(true) // October > September
  })

  it('compares the range across year boundaries', () => {
    const rule = monthRule({ min: '2026-04' })
    expect(isMonthDisabled(rule, 2025, 12)).toBe(true)
    expect(isMonthDisabled(rule, 2026, 4)).toBe(false)
  })
})
