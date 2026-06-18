import { buildWeeks } from '../application/calendarGrid'
import { buildMonths, isMonthDisabled } from '../application/monthGrid'
import { buildTimeOptions } from '../application/timeOptions'
import {
  addDays,
  addMonths,
  daysInMonth,
  nowTimeLocal,
  todayLocal,
  toIsoDate,
} from '../domain/civilDate'
import { compileRule, isDateDisabled, type CompiledRule } from '../domain/disabledRule'
import { format } from '../domain/formatter'
import { parse } from '../domain/parser'
import type {
  CivilDate,
  CivilTime,
  DatepickerConfig,
  DayCell,
  MonthCell,
  PickerValue,
  SelectionStatus,
  TimeOption,
} from '../domain/types'
import { createBridge, type LivewireBridge } from '../infrastructure/livewire/bridge'
import { positionPanel } from '../infrastructure/popover'

/** Alpine magics the component leans on (all injected at runtime by Alpine). */
interface AlpineMagics {
  $refs?: Record<string, HTMLElement>
  $nextTick?: (callback: () => void) => void
  $watch?: (property: string, callback: (value: unknown) => void) => void
  $wire?: unknown
  $el?: HTMLElement
}

export interface DatepickerComponent extends AlpineMagics {
  // --- reactive state ---
  config: DatepickerConfig
  rule: CompiledRule
  today: CivilDate
  isOpen: boolean
  display: string
  status: SelectionStatus
  viewYear: number
  viewMonth: number
  focusedDate: CivilDate
  focusedMonth: number
  selectedValue: PickerValue | null
  committedValue: PickerValue | null
  generation: number
  bridge: LivewireBridge
  unwatch: (() => void) | null

  // --- getters ---
  readonly value: string | null
  readonly hasValue: boolean
  readonly weeks: DayCell[][]
  readonly monthCells: MonthCell[]
  readonly timeOptions: TimeOption[]

  // --- lifecycle ---
  init(): void
  destroy(): void

  // --- behaviour ---
  open(): void
  close(restoreFocus?: boolean): void
  toggle(): void
  previousMonth(): void
  nextMonth(): void
  previousYear(): void
  nextYear(): void
  selectDay(day: DayCell): void
  selectDate(date: CivilDate): void
  selectMonth(year: number, month: number): void
  selectTime(option: TimeOption): void
  goToday(): void
  clear(): void
  onType(): void
  onInputKeydown(event: KeyboardEvent): void
  onGridKeydown(event: KeyboardEvent): void
  onMonthGridKeydown(event: KeyboardEvent): void
  dayClass(day: DayCell): string
  monthCellClass(cell: MonthCell): string
  timeClass(option: TimeOption): string

  // --- internal helpers ---
  commit(): void
  emitChange(): void
  reconcile(model: string, sent: string | null): void
  syncFromServer(value: string | null): void
  onServerChange(value: unknown): void
  valueString(): string | null
  formatDisplay(value: PickerValue): string
  updateDisplay(): void
  syncViewToSelection(): void
  clampFocus(): void
  focusActive(): void
  announce(key: string): void
  selectedDate(): CivilDate | null
}

const EMPTY_TIME: CivilTime = { hour: 0, minute: 0, second: 0 }

export function createDatepickerComponent(config: DatepickerConfig): DatepickerComponent {
  const today = todayLocal()

  const component: DatepickerComponent = {
    config,
    rule: compileRule(config),
    today,
    isOpen: false,
    display: '',
    status: 'idle',
    viewYear: today.year,
    viewMonth: today.month,
    focusedDate: today,
    focusedMonth: today.month,
    selectedValue: null,
    committedValue: null,
    generation: 0,
    bridge: createBridge(null),
    unwatch: null,

    get value(): string | null {
      return this.valueString()
    },

    get hasValue(): boolean {
      return this.selectedValue !== null
    },

    get weeks(): DayCell[][] {
      return buildWeeks(
        this.viewYear,
        this.viewMonth,
        this.config.firstDayOfWeek,
        this.today,
        this.selectedDate(),
        this.focusedDate,
        this.rule,
      )
    },

    get monthCells(): MonthCell[] {
      return buildMonths(
        this.viewYear,
        this.config.locale,
        this.today,
        this.selectedDate(),
        this.focusedMonth,
        this.rule,
      )
    },

    get timeOptions(): TimeOption[] {
      const selected = this.selectedValue
      return buildTimeOptions(
        this.config,
        this.rule,
        this.selectedDate(),
        selected ? selected.time : null,
      )
    },

    init(): void {
      this.bridge = createBridge(this.$wire ?? null)

      if (this.config.value !== null && this.config.value !== '') {
        const seeded = parse(
          this.config.value,
          this.config.valueFormat,
          this.config.mode,
          this.config.locale,
        )
        if (seeded.ok) {
          this.selectedValue = seeded.value
          this.committedValue = seeded.value
          this.display = this.formatDisplay(seeded.value)
          this.status = 'committed'
        }
      }

      const model = this.config.wire.model
      if (this.bridge.isAvailable() && model !== null) {
        const current = this.bridge.get(model)
        if (typeof current === 'string' && current !== '' && current !== this.valueString()) {
          this.syncFromServer(current)
        }
        this.unwatch = this.bridge.watch(model, (next) => this.onServerChange(next))
      }

      this.syncViewToSelection()

      this.$watch?.('viewMonth', () => this.clampFocus())
      this.$watch?.('viewYear', () => this.clampFocus())

      if (this.config.inline) {
        this.isOpen = true
      }
    },

    destroy(): void {
      if (this.unwatch !== null) {
        this.unwatch()
        this.unwatch = null
      }
    },

    open(): void {
      if (this.config.disabled || this.config.readonly || this.config.inline || this.isOpen) {
        return
      }
      this.isOpen = true
      this.syncViewToSelection()
      this.$nextTick?.(() => {
        const panel = this.$refs?.panel
        const anchor = this.$refs?.input ?? this.$el
        if (panel && anchor) {
          positionPanel(panel, anchor, this.config.placement)
        }
      })
    },

    close(restoreFocus = false): void {
      if (this.config.inline) {
        return
      }
      this.isOpen = false
      this.updateDisplay()
      if (restoreFocus) {
        this.$refs?.input?.focus()
      }
    },

    toggle(): void {
      if (this.isOpen) {
        this.close()
      } else {
        this.open()
      }
    },

    previousMonth(): void {
      const next = addMonths({ year: this.viewYear, month: this.viewMonth, day: 1 }, -1)
      this.viewYear = next.year
      this.viewMonth = next.month
    },

    nextMonth(): void {
      const next = addMonths({ year: this.viewYear, month: this.viewMonth, day: 1 }, 1)
      this.viewYear = next.year
      this.viewMonth = next.month
    },

    previousYear(): void {
      this.viewYear -= 1
    },

    nextYear(): void {
      this.viewYear += 1
    },

    selectDay(day: DayCell): void {
      if (day.isDisabled) {
        return
      }
      this.selectDate({ year: day.year, month: day.month, day: day.day })
    },

    selectDate(date: CivilDate): void {
      if (isDateDisabled(this.rule, date)) {
        return
      }

      this.focusedDate = date
      this.viewYear = date.year
      this.viewMonth = date.month

      if (this.config.mode === 'date') {
        this.selectedValue = { date, time: null }
        this.updateDisplay()
        this.commit()
        this.close(true)
        return
      }

      const time = this.selectedValue?.time ?? EMPTY_TIME
      this.selectedValue = { date, time }
      this.updateDisplay()
      this.commit()
    },

    selectMonth(year: number, month: number): void {
      if (isMonthDisabled(this.rule, year, month)) {
        return
      }

      this.viewYear = year
      this.focusedMonth = month
      this.selectedValue = { date: { year, month, day: 1 }, time: null }
      this.updateDisplay()
      this.commit()
      this.close(true)
    },

    selectTime(option: TimeOption): void {
      if (option.isDisabled) {
        return
      }

      const parsed = parse(option.value, 'H:i:s', 'time', this.config.locale)
      if (!parsed.ok || parsed.value.time === null) {
        return
      }
      const time = parsed.value.time

      if (this.config.mode === 'time') {
        this.selectedValue = { date: null, time }
        this.updateDisplay()
        this.commit()
        this.close(true)
        return
      }

      const date = this.selectedValue?.date ?? this.today
      this.selectedValue = { date, time }
      this.updateDisplay()
      this.commit()
    },

    goToday(): void {
      this.viewYear = this.today.year
      this.viewMonth = this.today.month
      this.focusedDate = this.today

      if (this.config.mode === 'time') {
        const time = nowTimeLocal()
        this.selectedValue = { date: null, time }
        this.updateDisplay()
        this.commit()
        return
      }

      if (this.config.mode === 'month') {
        this.selectMonth(this.today.year, this.today.month)
        return
      }

      this.selectDate(this.today)
    },

    clear(): void {
      this.selectedValue = null
      this.display = ''
      this.commit()
    },

    onType(): void {
      const text = this.display.trim()

      if (text === '') {
        this.selectedValue = null
        this.commit()
        return
      }

      const parsed = parse(text, this.config.displayFormat, this.config.mode, this.config.locale)
      if (parsed.ok) {
        this.selectedValue = parsed.value
        this.syncViewToSelection()
        this.commit()
      }
    },

    onInputKeydown(event: KeyboardEvent): void {
      if (event.key === 'ArrowDown' || event.key === 'Enter') {
        event.preventDefault()
        this.open()
        this.$nextTick?.(() => this.focusActive())
        return
      }

      if (event.key === 'Escape' && this.isOpen) {
        event.preventDefault()
        this.close(true)
      }
    },

    onGridKeydown(event: KeyboardEvent): void {
      let handled = true

      switch (event.key) {
        case 'ArrowLeft':
          this.focusedDate = addDays(this.focusedDate, -1)
          break
        case 'ArrowRight':
          this.focusedDate = addDays(this.focusedDate, 1)
          break
        case 'ArrowUp':
          this.focusedDate = addDays(this.focusedDate, -7)
          break
        case 'ArrowDown':
          this.focusedDate = addDays(this.focusedDate, 7)
          break
        case 'Home':
          this.focusedDate = addDays(this.focusedDate, -((this.focusedDate.day - 1) % 7))
          break
        case 'End':
          this.focusedDate = addDays(this.focusedDate, 6 - ((this.focusedDate.day - 1) % 7))
          break
        case 'PageUp':
          this.focusedDate = addMonths(this.focusedDate, event.shiftKey ? -12 : -1)
          break
        case 'PageDown':
          this.focusedDate = addMonths(this.focusedDate, event.shiftKey ? 12 : 1)
          break
        case 'Enter':
        case ' ':
          this.selectDate(this.focusedDate)
          return
        case 'Escape':
          event.preventDefault()
          this.close(true)
          return
        default:
          handled = false
      }

      if (handled) {
        event.preventDefault()
        this.viewYear = this.focusedDate.year
        this.viewMonth = this.focusedDate.month
        this.$nextTick?.(() => this.focusActive())
      }
    },

    onMonthGridKeydown(event: KeyboardEvent): void {
      let handled = true

      switch (event.key) {
        case 'ArrowLeft':
          this.focusedMonth -= 1
          break
        case 'ArrowRight':
          this.focusedMonth += 1
          break
        case 'ArrowUp':
          this.focusedMonth -= 3
          break
        case 'ArrowDown':
          this.focusedMonth += 3
          break
        case 'PageUp':
          this.viewYear -= 1
          break
        case 'PageDown':
          this.viewYear += 1
          break
        case 'Enter':
        case ' ':
          this.selectMonth(this.viewYear, this.focusedMonth)
          return
        case 'Escape':
          event.preventDefault()
          this.close(true)
          return
        default:
          handled = false
      }

      if (handled) {
        event.preventDefault()
        // Wrap across the year boundary so the grid is fully keyboard-navigable.
        if (this.focusedMonth < 1) {
          this.viewYear -= 1
          this.focusedMonth += 12
        } else if (this.focusedMonth > 12) {
          this.viewYear += 1
          this.focusedMonth -= 12
        }
        this.$nextTick?.(() => this.focusActive())
      }
    },

    dayClass(day: DayCell): string {
      const classes = this.config.classes
      const parts = [classes.day ?? '']
      if (day.isOutsideMonth) parts.push(classes.day_outside ?? '')
      if (day.isWeekend) parts.push(classes.day_weekend ?? '')
      if (day.isToday) parts.push(classes.day_today ?? '')
      if (day.isSelected) parts.push(classes.day_selected ?? '')
      if (day.isDisabled) parts.push(classes.day_disabled ?? '')
      return parts.filter((part) => part !== '').join(' ')
    },

    monthCellClass(cell: MonthCell): string {
      const classes = this.config.classes
      const parts = [classes.month_cell ?? '']
      if (cell.isToday) parts.push(classes.month_cell_today ?? '')
      if (cell.isSelected) parts.push(classes.month_cell_selected ?? '')
      if (cell.isDisabled) parts.push(classes.month_cell_disabled ?? '')
      return parts.filter((part) => part !== '').join(' ')
    },

    timeClass(option: TimeOption): string {
      const classes = this.config.classes
      const parts = [classes.time_option ?? '']
      if (option.isSelected) parts.push(classes.time_option_selected ?? '')
      if (option.isDisabled) parts.push(classes.time_option_disabled ?? '')
      return parts.filter((part) => part !== '').join(' ')
    },

    commit(): void {
      const generation = ++this.generation
      this.status = 'pending'

      const model = this.config.wire.model
      const sent = this.valueString()

      // Notify the outside world that the value changed, regardless of whether a
      // wire:model is present. Fires once per commit (covers selectDate /
      // selectTime / selectMonth / clear / onType / goToday).
      this.emitChange()

      if (!this.bridge.isAvailable() || model === null) {
        this.committedValue = this.selectedValue
        this.status = 'committed'
        return
      }

      const live = this.config.wire.modifiers.includes('live')

      void this.bridge
        .set(model, sent, live)
        .catch(() => undefined)
        .then(() => {
          if (generation !== this.generation) {
            return
          }
          this.reconcile(model, sent)
        })
    },

    emitChange(): void {
      const value = this.value
      const display = this.display

      // Native input/change on the hidden field so plain (non-Livewire) forms and
      // native `change` listeners on a wrapping element see the change like any
      // input — programmatic value assignment alone does not fire these.
      const hidden = this.$refs?.hidden
      if (hidden) {
        hidden.dispatchEvent(new Event('input', { bubbles: true }))
        hidden.dispatchEvent(new Event('change', { bubbles: true }))
      }

      // A namespaced event from the root carrying the formatted value and the
      // display text, so consumers can react with x-on:datepicker:change /
      // @datepicker:change without relying on wire:model.
      this.$el?.dispatchEvent(
        new CustomEvent('datepicker:change', {
          detail: { value, display },
          bubbles: true,
        }),
      )
    },

    reconcile(model: string, sent: string | null): void {
      const authoritative = this.bridge.get(model)

      if (authoritative === undefined) {
        this.committedValue = this.selectedValue
        this.status = 'committed'
        return
      }

      const authString = authoritative === null ? null : String(authoritative)

      if (authString === sent) {
        if (this.bridge.hasErrorFor(model)) {
          this.status = 'invalid'
          this.announce('selectedDate')
        } else {
          this.committedValue = this.selectedValue
          this.status = 'committed'
        }
        return
      }

      this.syncFromServer(authString)
      this.status = 'rejected'
    },

    syncFromServer(value: string | null): void {
      if (value === null || value === '') {
        this.selectedValue = null
        this.committedValue = null
        this.display = ''
        return
      }

      const parsed = parse(value, this.config.valueFormat, this.config.mode, this.config.locale)
      if (parsed.ok) {
        this.selectedValue = parsed.value
        this.committedValue = parsed.value
        this.display = this.formatDisplay(parsed.value)
      }
    },

    onServerChange(value: unknown): void {
      if (this.status === 'pending') {
        return
      }
      const next = value === null ? null : String(value)
      if (next !== this.valueString()) {
        this.syncFromServer(next)
        this.status = 'committed'
      }
    },

    valueString(): string | null {
      if (this.selectedValue === null) {
        return null
      }
      try {
        return format(this.selectedValue, this.config.valueFormat, this.config.locale)
      } catch {
        return null
      }
    },

    formatDisplay(value: PickerValue): string {
      try {
        return format(value, this.config.displayFormat, this.config.locale)
      } catch {
        return ''
      }
    },

    updateDisplay(): void {
      this.display = this.selectedValue !== null ? this.formatDisplay(this.selectedValue) : ''
    },

    syncViewToSelection(): void {
      const base = this.selectedValue?.date ?? this.today
      this.viewYear = base.year
      this.viewMonth = base.month
      this.focusedDate = base
      this.focusedMonth = base.month
    },

    clampFocus(): void {
      if (this.focusedDate.month === this.viewMonth && this.focusedDate.year === this.viewYear) {
        return
      }
      const day = Math.min(this.focusedDate.day, daysInMonth(this.viewYear, this.viewMonth))
      this.focusedDate = { year: this.viewYear, month: this.viewMonth, day }
    },

    focusActive(): void {
      const refs = this.$refs
      if (!refs) {
        return
      }

      if (this.config.mode === 'month') {
        const cell = refs.monthGrid?.querySelector<HTMLElement>(
          `[data-month="${this.focusedMonth}"]`,
        )
        cell?.focus()
        return
      }

      if (this.config.mode !== 'time' && refs.grid) {
        const iso = toIsoDate(this.focusedDate)
        const cell = refs.grid.querySelector<HTMLElement>(`[data-date="${iso}"]`)
        cell?.focus()
        return
      }

      const option = refs.timeList?.querySelector<HTMLElement>('button')
      option?.focus()
    },

    announce(key: string): void {
      const live = this.$refs?.live
      if (live) {
        live.textContent = this.config.locale.labels[key] ?? ''
      }
    },

    selectedDate(): CivilDate | null {
      return this.selectedValue?.date ?? null
    },
  }

  return component
}
