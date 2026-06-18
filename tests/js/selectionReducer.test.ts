import { describe, expect, it } from 'vitest'
import {
  clear,
  confirm,
  idle,
  invalidate,
  reject,
  select,
} from '@datepicker/application/selectionReducer'
import type { PickerValue } from '@datepicker/domain/types'

const committed: PickerValue = { date: { year: 2026, month: 6, day: 1 }, time: null }
const picked: PickerValue = { date: { year: 2026, month: 6, day: 18 }, time: null }

describe('selectionReducer (parity with PHP)', () => {
  it('marks a selection pending while keeping the committed snapshot', () => {
    const next = select(idle(committed), picked)
    expect(next.status).toBe('pending')
    expect(next.value).toEqual(picked)
    expect(next.committed).toEqual(committed)
  })

  it('confirms with the server value', () => {
    const next = confirm(select(idle(committed), picked), picked)
    expect(next.status).toBe('committed')
    expect(next.value).toEqual(picked)
    expect(next.committed).toEqual(picked)
  })

  it('rolls back to the committed snapshot on rejection', () => {
    const next = reject(select(idle(committed), picked))
    expect(next.status).toBe('rejected')
    expect(next.value).toEqual(committed)
    expect(next.committed).toEqual(committed)
  })

  it('keeps the value but flags invalid on validation failure', () => {
    const next = invalidate(select(idle(committed), picked))
    expect(next.status).toBe('invalid')
    expect(next.value).toEqual(picked)
  })

  it('clears optimistically', () => {
    const next = clear(idle(committed))
    expect(next.status).toBe('pending')
    expect(next.value).toBeNull()
    expect(next.committed).toEqual(committed)
  })
})
