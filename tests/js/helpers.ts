import { ENGLISH_LOCALE } from '@datepicker/domain/locale'
import type { DatepickerConfig } from '@datepicker/domain/types'

export function makeConfig(overrides: Partial<DatepickerConfig> = {}): DatepickerConfig {
  return {
    id: 'dp',
    name: 'field',
    mode: 'date',
    displayFormat: 'Y-m-d',
    valueFormat: 'Y-m-d',
    locale: ENGLISH_LOCALE,
    firstDayOfWeek: 0,
    value: null,
    min: null,
    max: null,
    disabledDates: [],
    disabledWeekdays: [],
    disabledTimes: [],
    minuteStep: 5,
    hourCycle: 24,
    placeholder: null,
    disabled: false,
    readonly: false,
    required: false,
    clearable: true,
    todayButton: true,
    closeButton: true,
    inline: false,
    placement: 'bottom-start',
    classes: {
      day: 'day',
      day_selected: 'is-selected',
      day_today: 'is-today',
      day_outside: 'is-outside',
      day_disabled: 'is-disabled',
      time_option: 'opt',
      time_option_selected: 'opt-selected',
      time_option_disabled: 'opt-disabled',
    },
    theme: null,
    ariaLabel: null,
    debounceMs: 0,
    wire: { model: null, modifiers: [] },
    ...overrides,
  }
}

/** Flush all pending microtasks (for awaited bridge commits). */
export function tick(): Promise<void> {
  return new Promise((resolve) => setTimeout(resolve, 0))
}

/**
 * A minimal stand-in for Livewire's `$wire`, with a hook to simulate the
 * server's response (accept / change the value / raise a validation error).
 */
export class FakeWire {
  values: Record<string, unknown>
  $errors: Record<string, unknown> = {}
  setCalls: Array<{ name: string; value: unknown; live: boolean }> = []
  behavior: ((name: string, value: unknown, wire: FakeWire) => void) | null = null
  private watchers: Record<string, Array<(value: unknown) => void>> = {}

  constructor(values: Record<string, unknown> = {}) {
    this.values = { ...values }
  }

  get(name: string): unknown {
    return this.values[name]
  }

  set(name: string, value: unknown, live: boolean): Promise<void> {
    this.setCalls.push({ name, value, live })
    this.values[name] = value
    return Promise.resolve().then(() => {
      if (this.behavior) {
        this.behavior(name, value, this)
      }
    })
  }

  $watch(name: string, callback: (value: unknown) => void): () => void {
    ;(this.watchers[name] ??= []).push(callback)
    return () => {
      this.watchers[name] = (this.watchers[name] ?? []).filter((cb) => cb !== callback)
    }
  }

  /** Simulate an external server-driven change to a bound property. */
  pushServerValue(name: string, value: unknown): void {
    this.values[name] = value
    for (const cb of this.watchers[name] ?? []) {
      cb(value)
    }
  }
}
