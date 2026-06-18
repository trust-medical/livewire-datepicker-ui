import { beforeEach, describe, expect, it, vi } from 'vitest'
import { createDatepickerComponent, type DatepickerComponent } from '@datepicker/ui/component'
import { FakeWire, makeConfig, tick } from './helpers'

function mount(component: DatepickerComponent, wire: unknown = undefined): DatepickerComponent {
  component.$nextTick = (cb: () => void) => cb()
  component.$watch = () => {}
  component.$refs = {}
  if (wire !== undefined) {
    component.$wire = wire
  }
  component.init()
  return component
}

describe('datepicker component', () => {
  it('optimistically commits a selection without Livewire', () => {
    const component = mount(createDatepickerComponent(makeConfig()))

    component.selectDate({ year: 2026, month: 6, day: 18 })

    expect(component.value).toBe('2026-06-18')
    expect(component.display).toBe('2026-06-18')
    expect(component.status).toBe('committed')
    expect(component.isOpen).toBe(false) // date mode closes on select
  })

  it('goes pending then committed on a successful live sync', async () => {
    const wire = new FakeWire({ d: null })
    const component = mount(
      createDatepickerComponent(makeConfig({ wire: { model: 'd', modifiers: ['live'] } })),
      wire,
    )

    component.selectDate({ year: 2026, month: 6, day: 18 })
    expect(component.status).toBe('pending')

    await tick()

    expect(component.status).toBe('committed')
    expect(component.value).toBe('2026-06-18')
    expect(wire.get('d')).toBe('2026-06-18')
  })

  it('rolls back to the server value when the server rejects the change', async () => {
    const wire = new FakeWire({ d: '2026-06-01' })
    wire.behavior = (name, _value, w) => {
      w.values[name] = '2026-06-01' // server refuses the new value
    }
    const component = mount(
      createDatepickerComponent(
        makeConfig({ value: '2026-06-01', wire: { model: 'd', modifiers: ['live'] } }),
      ),
      wire,
    )

    component.selectDate({ year: 2026, month: 6, day: 18 })
    expect(component.value).toBe('2026-06-18') // optimistic

    await tick()

    expect(component.status).toBe('rejected')
    expect(component.value).toBe('2026-06-01') // rolled back
    expect(component.display).toBe('2026-06-01')
  })

  it('keeps the value but flags invalid on a validation error', async () => {
    const wire = new FakeWire({ d: null })
    wire.behavior = (name) => {
      wire.$errors = { [name]: ['Required'] } // value kept, error raised
    }
    const component = mount(
      createDatepickerComponent(makeConfig({ wire: { model: 'd', modifiers: ['live'] } })),
      wire,
    )

    component.selectDate({ year: 2026, month: 6, day: 18 })
    await tick()

    expect(component.status).toBe('invalid')
    expect(component.value).toBe('2026-06-18') // not rolled back
  })

  it('clears optimistically', () => {
    const component = mount(createDatepickerComponent(makeConfig({ value: '2026-06-01' })))
    expect(component.hasValue).toBe(true)

    component.clear()

    expect(component.value).toBeNull()
    expect(component.display).toBe('')
    expect(component.hasValue).toBe(false)
  })

  it('reflects external server-driven changes when idle', async () => {
    const wire = new FakeWire({ d: null })
    const component = mount(
      createDatepickerComponent(makeConfig({ wire: { model: 'd', modifiers: ['live'] } })),
      wire,
    )

    wire.pushServerValue('d', '2026-12-25')
    await tick()

    expect(component.value).toBe('2026-12-25')
    expect(component.display).toBe('2026-12-25')
  })

  it('navigates the grid with the keyboard and selects on Enter', () => {
    const component = mount(createDatepickerComponent(makeConfig()))
    component.focusedDate = { year: 2026, month: 6, day: 18 }

    component.onGridKeydown(new KeyboardEvent('keydown', { key: 'ArrowRight' }))
    expect(component.focusedDate).toEqual({ year: 2026, month: 6, day: 19 })

    component.onGridKeydown(new KeyboardEvent('keydown', { key: 'ArrowDown' }))
    expect(component.focusedDate).toEqual({ year: 2026, month: 6, day: 26 })

    component.onGridKeydown(new KeyboardEvent('keydown', { key: 'Enter' }))
    expect(component.value).toBe('2026-06-26')
  })

  it('moves by month with PageUp/PageDown', () => {
    const component = mount(createDatepickerComponent(makeConfig()))
    component.focusedDate = { year: 2026, month: 6, day: 18 }

    component.onGridKeydown(new KeyboardEvent('keydown', { key: 'PageUp' }))
    expect(component.focusedDate.month).toBe(5)

    component.onGridKeydown(new KeyboardEvent('keydown', { key: 'PageDown', shiftKey: true }))
    expect(component.focusedDate.year).toBe(2027)
  })

  it('builds class strings from the configured slots', () => {
    const component = mount(createDatepickerComponent(makeConfig({ value: '2026-06-18' })))
    const weeks = component.weeks
    const selected = weeks.flat().find((day) => day.isSelected)

    expect(selected).toBeDefined()
    expect(component.dayClass(selected!)).toContain('is-selected')
    expect(component.dayClass(selected!)).toContain('day')
  })

  it('keeps multiple instances independent', () => {
    const a = mount(createDatepickerComponent(makeConfig({ id: 'a' })))
    const b = mount(createDatepickerComponent(makeConfig({ id: 'b' })))

    a.selectDate({ year: 2026, month: 1, day: 1 })
    b.selectDate({ year: 2026, month: 12, day: 31 })

    expect(a.value).toBe('2026-01-01')
    expect(b.value).toBe('2026-12-31')
  })

  it('unsubscribes the server watcher on destroy', () => {
    const wire = new FakeWire({ d: '2026-06-01' })
    const component = mount(
      createDatepickerComponent(makeConfig({ wire: { model: 'd', modifiers: ['live'] } })),
      wire,
    )

    component.destroy()
    wire.pushServerValue('d', '2026-09-09')

    expect(component.value).not.toBe('2026-09-09')
  })

  it('opens via the keyboard from the input', () => {
    const component = mount(createDatepickerComponent(makeConfig()))
    const focus = vi.fn()
    component.$refs = { grid: { querySelector: () => ({ focus }) } as unknown as HTMLElement }

    component.onInputKeydown(new KeyboardEvent('keydown', { key: 'ArrowDown' }))

    expect(component.isOpen).toBe(true)
    expect(focus).toHaveBeenCalled()
  })
})

beforeEach(() => {
  // happy-dom resets document between files via setup; nothing per-test needed.
})
