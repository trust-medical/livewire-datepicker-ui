import { describe, expect, it } from 'vitest'
import { createBridge } from '@datepicker/infrastructure/livewire/bridge'
import { FakeWire } from './helpers'

describe('LivewireBridge', () => {
  it('falls back to a no-op NullBridge when Livewire is absent', async () => {
    const bridge = createBridge(null)

    expect(bridge.isAvailable()).toBe(false)
    expect(bridge.get('x')).toBeUndefined()
    expect(bridge.hasErrorFor('x')).toBe(false)
    expect(bridge.watch('x', () => {})).toBeNull()
    await expect(bridge.set('x', '1', true)).resolves.toBeUndefined()
  })

  it('wraps a $wire-like object', async () => {
    const wire = new FakeWire({ starts_at: '2026-06-01' })
    const bridge = createBridge(wire)

    expect(bridge.isAvailable()).toBe(true)
    expect(bridge.get('starts_at')).toBe('2026-06-01')

    await bridge.set('starts_at', '2026-06-18', true)
    expect(bridge.get('starts_at')).toBe('2026-06-18')
    expect(wire.setCalls.at(-1)).toEqual({ name: 'starts_at', value: '2026-06-18', live: true })
  })

  it('detects validation errors and supports watching', () => {
    const wire = new FakeWire()
    wire.$errors = { starts_at: ['Required'] }
    const bridge = createBridge(wire)

    expect(bridge.hasErrorFor('starts_at')).toBe(true)
    expect(bridge.hasErrorFor('other')).toBe(false)

    let observed: unknown = null
    const stop = bridge.watch('starts_at', (value) => {
      observed = value
    })
    wire.pushServerValue('starts_at', '2026-07-01')
    expect(observed).toBe('2026-07-01')

    stop?.()
    wire.pushServerValue('starts_at', '2026-08-01')
    expect(observed).toBe('2026-07-01') // unsubscribed
  })
})
