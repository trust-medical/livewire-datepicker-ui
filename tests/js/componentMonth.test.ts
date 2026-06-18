import { describe, expect, it } from 'vitest'
import { createDatepickerComponent, type DatepickerComponent } from '@datepicker/ui/component'
import { makeConfig } from './helpers'

function mount(component: DatepickerComponent): DatepickerComponent {
  component.$nextTick = (cb: () => void) => cb()
  component.$watch = () => {}
  component.$refs = {}
  component.init()
  return component
}

const monthConfig = (overrides = {}) =>
  makeConfig({ mode: 'month', displayFormat: 'Y-m', valueFormat: 'Y-m', ...overrides })

describe('datepicker month mode', () => {
  it('selects a month, fixes the day to the 1st, and commits + closes', () => {
    const component = mount(createDatepickerComponent(monthConfig()))

    component.selectMonth(2026, 6)

    expect(component.value).toBe('2026-06')
    expect(component.display).toBe('2026-06')
    expect(component.selectedValue).toEqual({ date: { year: 2026, month: 6, day: 1 }, time: null })
    expect(component.status).toBe('committed')
    expect(component.isOpen).toBe(false)
  })

  it('seeds the selection + view from an initial Y-m value', () => {
    const component = mount(createDatepickerComponent(monthConfig({ value: '2026-03' })))

    expect(component.value).toBe('2026-03')
    expect(component.viewYear).toBe(2026)
    expect(component.monthCells.find((cell) => cell.isSelected)?.month).toBe(3)
  })

  it('navigates by year', () => {
    const component = mount(createDatepickerComponent(monthConfig()))
    const year = component.viewYear

    component.nextYear()
    expect(component.viewYear).toBe(year + 1)

    component.previousYear()
    component.previousYear()
    expect(component.viewYear).toBe(year - 1)
  })

  it('does not select a month outside the range', () => {
    const component = mount(createDatepickerComponent(monthConfig({ min: '2026-05' })))

    component.selectMonth(2026, 3) // before min
    expect(component.value).toBeNull()
  })

  it('navigates the month grid with the keyboard and selects on Enter', () => {
    const component = mount(createDatepickerComponent(monthConfig()))
    component.viewYear = 2026
    component.focusedMonth = 6

    component.onMonthGridKeydown(new KeyboardEvent('keydown', { key: 'ArrowRight' }))
    expect(component.focusedMonth).toBe(7)

    component.onMonthGridKeydown(new KeyboardEvent('keydown', { key: 'ArrowDown' }))
    expect(component.focusedMonth).toBe(10)

    component.onMonthGridKeydown(new KeyboardEvent('keydown', { key: 'Enter' }))
    expect(component.value).toBe('2026-10')
  })

  it('wraps keyboard focus across the year boundary', () => {
    const component = mount(createDatepickerComponent(monthConfig()))
    component.viewYear = 2026
    component.focusedMonth = 1

    component.onMonthGridKeydown(new KeyboardEvent('keydown', { key: 'ArrowLeft' }))

    expect(component.focusedMonth).toBe(12)
    expect(component.viewYear).toBe(2025)
  })
})
