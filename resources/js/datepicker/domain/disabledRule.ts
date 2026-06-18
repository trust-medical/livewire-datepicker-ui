import { compareDate, compareTime, toIsoDate, weekday } from './civilDate'
import { parse } from './parser'
import type {
  CivilDate,
  CivilTime,
  DatepickerConfig,
  LocaleData,
  PickerMode,
  PickerValue,
} from './types'

interface TimeWindow {
  from: CivilTime
  to: CivilTime
}

/** Pre-compiled constraints, mirroring the PHP `DisabledRule`. */
export interface CompiledRule {
  mode: PickerMode
  min: PickerValue | null
  max: PickerValue | null
  disabledDates: Set<string>
  disabledWeekdays: Set<number>
  disabledTimes: TimeWindow[]
  minuteStep: number
}

export function compileRule(config: DatepickerConfig): CompiledRule {
  return {
    mode: config.mode,
    min: parseBoundary(config.min, config),
    max: parseBoundary(config.max, config),
    disabledDates: new Set(config.disabledDates),
    disabledWeekdays: new Set(config.disabledWeekdays),
    disabledTimes: config.disabledTimes
      .map((window) => ({
        from: parseTime(window.from, config.locale),
        to: parseTime(window.to, config.locale),
      }))
      .filter((window): window is TimeWindow => window.from !== null && window.to !== null),
    minuteStep: config.minuteStep,
  }
}

function parseBoundary(value: string | null, config: DatepickerConfig): PickerValue | null {
  if (value === null || value.trim() === '') {
    return null
  }
  const result = parse(value, config.valueFormat, config.mode, config.locale)
  return result.ok ? result.value : null
}

function parseTime(value: string, locale: LocaleData): CivilTime | null {
  const result = parse(value, 'H:i:s', 'time', locale)
  return result.ok ? result.value.time : null
}

export function isDateDisabled(rule: CompiledRule, date: CivilDate): boolean {
  const minDate = rule.min?.date ?? null
  if (minDate !== null && compareDate(date, minDate) < 0) {
    return true
  }

  const maxDate = rule.max?.date ?? null
  if (maxDate !== null && compareDate(date, maxDate) > 0) {
    return true
  }

  return matchesDayRules(rule, date)
}

export function isTimeDisabled(rule: CompiledRule, time: CivilTime): boolean {
  if (rule.mode === 'time') {
    const minTime = rule.min?.time ?? null
    if (minTime !== null && compareTime(time, minTime) < 0) {
      return true
    }

    const maxTime = rule.max?.time ?? null
    if (maxTime !== null && compareTime(time, maxTime) > 0) {
      return true
    }
  }

  if (rule.minuteStep > 1 && time.minute % rule.minuteStep !== 0) {
    return true
  }

  return rule.disabledTimes.some(
    (window) => compareTime(time, window.from) >= 0 && compareTime(time, window.to) <= 0,
  )
}

export function isDateTimeDisabled(rule: CompiledRule, date: CivilDate, time: CivilTime): boolean {
  if (rule.min?.date && rule.min.time) {
    if (compareDateTime(date, time, rule.min.date, rule.min.time) < 0) {
      return true
    }
  } else if (rule.min?.date && compareDate(date, rule.min.date) < 0) {
    return true
  }

  if (rule.max?.date && rule.max.time) {
    if (compareDateTime(date, time, rule.max.date, rule.max.time) > 0) {
      return true
    }
  } else if (rule.max?.date && compareDate(date, rule.max.date) > 0) {
    return true
  }

  if (matchesDayRules(rule, date)) {
    return true
  }

  if (rule.minuteStep > 1 && time.minute % rule.minuteStep !== 0) {
    return true
  }

  return rule.disabledTimes.some(
    (window) => compareTime(time, window.from) >= 0 && compareTime(time, window.to) <= 0,
  )
}

function matchesDayRules(rule: CompiledRule, date: CivilDate): boolean {
  return rule.disabledWeekdays.has(weekday(date)) || rule.disabledDates.has(toIsoDate(date))
}

function compareDateTime(
  dateA: CivilDate,
  timeA: CivilTime,
  dateB: CivilDate,
  timeB: CivilTime,
): number {
  const byDate = compareDate(dateA, dateB)
  return byDate !== 0 ? byDate : compareTime(timeA, timeB)
}
