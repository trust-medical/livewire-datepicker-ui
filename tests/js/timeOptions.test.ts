import { describe, expect, it } from 'vitest'
import { buildTimeOptions } from '@datepicker/application/timeOptions'
import { compileRule } from '@datepicker/domain/disabledRule'
import { makeConfig } from './helpers'

describe('timeOptions', () => {
  it('generates options at the configured minute step', () => {
    const config = makeConfig({ mode: 'time', valueFormat: 'H:i:s', minuteStep: 30 })
    const options = buildTimeOptions(config, compileRule(config), null, null)

    expect(options).toHaveLength((24 * 60) / 30)
    expect(options[0]?.value).toBe('00:00:00')
    expect(options[1]?.value).toBe('00:30:00')
  })

  it('formats labels per the hour cycle', () => {
    const cfg24 = makeConfig({ mode: 'time', valueFormat: 'H:i:s', minuteStep: 60, hourCycle: 24 })
    const cfg12 = makeConfig({ mode: 'time', valueFormat: 'H:i:s', minuteStep: 60, hourCycle: 12 })

    expect(buildTimeOptions(cfg24, compileRule(cfg24), null, null)[13]?.label).toBe('13:00')
    expect(buildTimeOptions(cfg12, compileRule(cfg12), null, null)[13]?.label).toBe('01:00 PM')
  })

  it('marks disabled and selected options', () => {
    const config = makeConfig({
      mode: 'time',
      valueFormat: 'H:i:s',
      minuteStep: 60,
      min: '09:00:00',
    })
    const options = buildTimeOptions(config, compileRule(config), null, {
      hour: 10,
      minute: 0,
      second: 0,
    })

    expect(options[8]?.isDisabled).toBe(true) // 08:00 before min
    expect(options[9]?.isDisabled).toBe(false) // 09:00
    expect(options[10]?.isSelected).toBe(true) // 10:00 selected
  })
})
