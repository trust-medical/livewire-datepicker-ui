import { describe, expect, it } from 'vitest'
import { createDatepickerComponent, type DatepickerComponent } from '@datepicker/ui/component'
import { FakeWire, makeConfig } from './helpers'

/** Mount with a real root element + hidden input so dispatched events are observable. */
function mountWithDom(
  component: DatepickerComponent,
  wire: unknown = undefined,
): { component: DatepickerComponent; root: HTMLElement; hidden: HTMLInputElement } {
  const root = document.createElement('div')
  const hidden = document.createElement('input')
  hidden.type = 'hidden'
  root.appendChild(hidden)
  document.body.appendChild(root)

  component.$nextTick = (cb: () => void) => cb()
  component.$watch = () => {}
  component.$refs = { hidden }
  component.$el = root
  if (wire !== undefined) {
    component.$wire = wire
  }
  component.init()
  return { component, root, hidden }
}

describe('datepicker change events', () => {
  it('fires native change/input and datepicker:change without wire:model', () => {
    const { component, root, hidden } = mountWithDom(createDatepickerComponent(makeConfig()))

    let nativeChange = 0
    let nativeInput = 0
    hidden.addEventListener('change', () => nativeChange++)
    hidden.addEventListener('input', () => nativeInput++)

    let detail: unknown = null
    root.addEventListener('datepicker:change', (event) => {
      detail = (event as CustomEvent).detail
    })

    component.selectDate({ year: 2026, month: 6, day: 18 })

    expect(nativeChange).toBe(1)
    expect(nativeInput).toBe(1)
    expect(detail).toEqual({ value: '2026-06-18', display: '2026-06-18' })
  })

  it('bubbles the native change event up to an ancestor element', () => {
    const { component, root } = mountWithDom(createDatepickerComponent(makeConfig()))
    let bubbled = 0
    root.addEventListener('change', () => bubbled++)

    component.selectDate({ year: 2026, month: 6, day: 18 })

    expect(bubbled).toBe(1)
  })

  it('fires regardless of whether a wire:model is bound', () => {
    const wire = new FakeWire({ d: null })
    const { component, root } = mountWithDom(
      createDatepickerComponent(makeConfig({ wire: { model: 'd', modifiers: ['live'] } })),
      wire,
    )

    let detail: unknown = null
    root.addEventListener('datepicker:change', (event) => {
      detail = (event as CustomEvent).detail
    })

    component.selectDate({ year: 2026, month: 6, day: 18 })

    expect(detail).toEqual({ value: '2026-06-18', display: '2026-06-18' })
  })

  it('emits a cleared value on clear()', () => {
    const { component, root } = mountWithDom(
      createDatepickerComponent(makeConfig({ value: '2026-06-01' })),
    )
    const details: unknown[] = []
    root.addEventListener('datepicker:change', (event) =>
      details.push((event as CustomEvent).detail),
    )

    component.clear()

    expect(details).toEqual([{ value: null, display: '' }])
  })
})
