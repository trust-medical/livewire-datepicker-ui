import { describe, expect, it } from 'vitest'
import {
  compileRule,
  isDateDisabled,
  isDateTimeDisabled,
  isTimeDisabled,
} from '@datepicker/domain/disabledRule'
import { makeConfig } from './helpers'

describe('disabledRule', () => {
  it('disables dates outside the min/max range', () => {
    const rule = compileRule(makeConfig({ min: '2026-06-10', max: '2026-06-20' }))

    expect(isDateDisabled(rule, { year: 2026, month: 6, day: 9 })).toBe(true)
    expect(isDateDisabled(rule, { year: 2026, month: 6, day: 10 })).toBe(false)
    expect(isDateDisabled(rule, { year: 2026, month: 6, day: 20 })).toBe(false)
    expect(isDateDisabled(rule, { year: 2026, month: 6, day: 21 })).toBe(true)
  })

  it('disables explicit dates and weekdays', () => {
    const rule = compileRule(
      makeConfig({ disabledDates: ['2026-06-18'], disabledWeekdays: [0, 6] }),
    )

    expect(isDateDisabled(rule, { year: 2026, month: 6, day: 18 })).toBe(true) // explicit
    expect(isDateDisabled(rule, { year: 2026, month: 6, day: 13 })).toBe(true) // Saturday
    expect(isDateDisabled(rule, { year: 2026, month: 6, day: 15 })).toBe(false) // Monday
  })

  it('enforces minute step and time windows in time mode', () => {
    const rule = compileRule(
      makeConfig({
        mode: 'time',
        valueFormat: 'H:i:s',
        minuteStep: 15,
        min: '09:00:00',
        max: '17:00:00',
        disabledTimes: [{ from: '12:00:00', to: '13:00:00' }],
      }),
    )

    expect(isTimeDisabled(rule, { hour: 9, minute: 0, second: 0 })).toBe(false)
    expect(isTimeDisabled(rule, { hour: 9, minute: 7, second: 0 })).toBe(true) // off step
    expect(isTimeDisabled(rule, { hour: 8, minute: 45, second: 0 })).toBe(true) // before min
    expect(isTimeDisabled(rule, { hour: 12, minute: 30, second: 0 })).toBe(true) // window
  })

  it('combines date and time bounds for datetime values', () => {
    const rule = compileRule(
      makeConfig({
        mode: 'datetime',
        valueFormat: 'Y-m-d\\TH:i:s',
        min: '2026-06-18T09:00:00',
        max: '2026-06-18T17:00:00',
      }),
    )

    expect(
      isDateTimeDisabled(
        rule,
        { year: 2026, month: 6, day: 18 },
        { hour: 8, minute: 0, second: 0 },
      ),
    ).toBe(true)
    expect(
      isDateTimeDisabled(
        rule,
        { year: 2026, month: 6, day: 18 },
        { hour: 12, minute: 0, second: 0 },
      ),
    ).toBe(false)
    expect(
      isDateTimeDisabled(
        rule,
        { year: 2026, month: 6, day: 19 },
        { hour: 12, minute: 0, second: 0 },
      ),
    ).toBe(true)
  })
})
