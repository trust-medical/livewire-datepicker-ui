import '../../css/datepicker.css'
import { createDatepickerComponent } from './ui/component'
import type { DatepickerConfig } from './domain/types'

/** The minimal slice of the Alpine API the package relies on. */
export interface AlpineInstance {
  data(name: string, callback: (...args: unknown[]) => unknown): void
}

export const DATA_NAME = 'datepicker'

/** Register the `datepicker` Alpine component. Call once, on `alpine:init`. */
export function registerDatepicker(Alpine: AlpineInstance): void {
  Alpine.data(DATA_NAME, (config: unknown) => createDatepickerComponent(config as DatepickerConfig))
}

/** Alpine plugin entry: `Alpine.plugin(datepicker)`. */
export default function datepicker(Alpine: AlpineInstance): void {
  registerDatepicker(Alpine)
}

function globalAlpine(): AlpineInstance | undefined {
  if (typeof window === 'undefined') {
    return undefined
  }
  return (window as unknown as { Alpine?: AlpineInstance }).Alpine
}

// Auto-register for the global (IIFE) build loaded via @datepickerScripts: this
// runs alongside the Alpine that Livewire bundles. Harmless for ESM consumers
// who register manually (Alpine.data just gets redefined to the same factory).
if (typeof document !== 'undefined') {
  document.addEventListener('alpine:init', () => {
    const Alpine = globalAlpine()
    if (Alpine) {
      registerDatepicker(Alpine)
    }
  })

  const existing = globalAlpine()
  if (existing) {
    registerDatepicker(existing)
  }
}

export { createDatepickerComponent } from './ui/component'
export type { DatepickerComponent } from './ui/component'
export { format } from './domain/formatter'
export { parse } from './domain/parser'
export { ENGLISH_LOCALE } from './domain/locale'
export type * from './domain/types'
