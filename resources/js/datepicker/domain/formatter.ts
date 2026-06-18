import { hour12, isoWeekday, isPm, weekday } from './civilDate'
import { isDateToken, tokenize } from './tokens'
import type { CivilDate, CivilTime, LocaleData, PickerValue } from './types'

/**
 * Formats a value into a string using the supported PHP date tokens. Mirrors the
 * PHP `DateFormatter` so the value-format string the client writes is byte-for-byte
 * what the server parses.
 */
export function format(value: PickerValue, formatString: string, locale: LocaleData): string {
  let output = ''

  for (const segment of tokenize(formatString)) {
    if (segment.type === 'literal') {
      output += segment.value
      continue
    }

    output += renderToken(segment.value, value.date, value.time, locale)
  }

  return output
}

function pad(value: number, length = 2): string {
  return String(value).padStart(length, '0')
}

function renderToken(
  token: string,
  date: CivilDate | null,
  time: CivilTime | null,
  locale: LocaleData,
): string {
  if (isDateToken(token)) {
    if (date === null) {
      throw new Error(`Format token "${token}" requires a date but the value has none.`)
    }
    return renderDateToken(token, date, locale)
  }

  if (time === null) {
    throw new Error(`Format token "${token}" requires a time but the value has none.`)
  }
  return renderTimeToken(token, time, locale)
}

function renderDateToken(token: string, date: CivilDate, locale: LocaleData): string {
  switch (token) {
    case 'Y':
      return pad(date.year, 4)
    case 'y':
      return pad(date.year % 100)
    case 'm':
      return pad(date.month)
    case 'n':
      return String(date.month)
    case 'd':
      return pad(date.day)
    case 'j':
      return String(date.day)
    case 'N':
      return String(isoWeekday(date))
    case 'w':
      return String(weekday(date))
    case 'D':
      return locale.weekdaysShort[weekday(date)] ?? ''
    case 'l':
      return locale.weekdays[weekday(date)] ?? ''
    case 'M':
      return locale.monthsShort[date.month - 1] ?? ''
    case 'F':
      return locale.months[date.month - 1] ?? ''
    default:
      return token
  }
}

function renderTimeToken(token: string, time: CivilTime, locale: LocaleData): string {
  switch (token) {
    case 'H':
      return pad(time.hour)
    case 'G':
      return String(time.hour)
    case 'h':
      return pad(hour12(time))
    case 'g':
      return String(hour12(time))
    case 'i':
      return pad(time.minute)
    case 's':
      return pad(time.second)
    case 'A':
      return isPm(time) ? (locale.labels.pm_upper ?? 'PM') : (locale.labels.am_upper ?? 'AM')
    case 'a':
      return isPm(time) ? (locale.labels.pm_lower ?? 'pm') : (locale.labels.am_lower ?? 'am')
    default:
      return token
  }
}
