/**
 * Thin abstraction over the Livewire `$wire` object so optimistic updates and
 * rollback work across Livewire 3 and 4 (and degrade gracefully to a no-op when
 * Livewire is absent). Only stable surface — get/set/errors — is used; no
 * version-specific internals.
 */
export interface LivewireBridge {
  isAvailable(): boolean
  get(model: string): unknown
  set(model: string, value: unknown, live: boolean): Promise<void>
  hasErrorFor(model: string): boolean
  /** Subscribe to external server-driven changes; returns an unsubscribe fn if supported. */
  watch(model: string, callback: (value: unknown) => void): (() => void) | null
}

interface WireLike {
  get?: (name: string) => unknown
  set?: (name: string, value: unknown, live?: boolean) => unknown
  $get?: (name: string) => unknown
  $set?: (name: string, value: unknown, live?: boolean) => unknown
  $watch?: (name: string, callback: (value: unknown) => void) => unknown
  $errors?: unknown
  errors?: unknown
}

class LiveBridge implements LivewireBridge {
  constructor(private readonly wire: WireLike) {}

  isAvailable(): boolean {
    return true
  }

  get(model: string): unknown {
    const getter = this.wire.get ?? this.wire.$get
    return typeof getter === 'function' ? getter.call(this.wire, model) : undefined
  }

  async set(model: string, value: unknown, live: boolean): Promise<void> {
    const setter = this.wire.set ?? this.wire.$set
    if (typeof setter !== 'function') {
      return
    }
    await Promise.resolve(setter.call(this.wire, model, value, live))
  }

  watch(model: string, callback: (value: unknown) => void): (() => void) | null {
    if (typeof this.wire.$watch !== 'function') {
      return null
    }
    const stop = this.wire.$watch(model, callback)
    return typeof stop === 'function' ? (stop as () => void) : null
  }

  hasErrorFor(model: string): boolean {
    const bag = this.wire.$errors ?? this.wire.errors
    if (bag === null || typeof bag !== 'object') {
      return false
    }

    const record = bag as Record<string, unknown>
    if (model in record) {
      return true
    }

    // Some Livewire versions expose an `errors` callable or nested bag.
    const nested = record.errors
    if (
      nested !== null &&
      typeof nested === 'object' &&
      model in (nested as Record<string, unknown>)
    ) {
      return true
    }

    return false
  }
}

class NullBridge implements LivewireBridge {
  isAvailable(): boolean {
    return false
  }

  get(): unknown {
    return undefined
  }

  async set(): Promise<void> {
    // No Livewire: the hidden input carries the value for plain form submits.
  }

  watch(): (() => void) | null {
    return null
  }

  hasErrorFor(): boolean {
    return false
  }
}

export function createBridge(wire: unknown): LivewireBridge {
  if (wire !== null && typeof wire === 'object') {
    const candidate = wire as WireLike
    if (typeof candidate.set === 'function' || typeof candidate.$set === 'function') {
      return new LiveBridge(candidate)
    }
  }

  return new NullBridge()
}
