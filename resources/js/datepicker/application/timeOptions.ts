import { compareTime, toIsoTime } from '../domain/civilDate'
import { isDateTimeDisabled, isTimeDisabled, type CompiledRule } from '../domain/disabledRule'
import { format } from '../domain/formatter'
import type { CivilDate, CivilTime, DatepickerConfig, TimeOption } from '../domain/types'

const MINUTES_PER_DAY = 24 * 60

/**
 * Builds the full list of selectable times at the configured minute step. In
 * datetime mode each option is checked against the full datetime range (so the
 * boundary day correctly disables out-of-range times); in time mode it is
 * checked against the time-of-day rules.
 */
export function buildTimeOptions(
  config: DatepickerConfig,
  rule: CompiledRule,
  selectedDate: CivilDate | null,
  selectedTime: CivilTime | null,
): TimeOption[] {
  const step = Math.max(1, config.minuteStep)
  const labelFormat = config.hourCycle === 12 ? 'h:i A' : 'H:i'
  const options: TimeOption[] = []

  for (let minutes = 0; minutes < MINUTES_PER_DAY; minutes += step) {
    const time: CivilTime = { hour: Math.floor(minutes / 60), minute: minutes % 60, second: 0 }

    const isDisabled =
      config.mode === 'datetime' && selectedDate !== null
        ? isDateTimeDisabled(rule, selectedDate, time)
        : isTimeDisabled(rule, time)

    options.push({
      value: toIsoTime(time),
      label: format({ date: null, time }, labelFormat, config.locale),
      isSelected: selectedTime !== null && compareTime(time, selectedTime) === 0,
      isDisabled,
    })
  }

  return options
}
