import { daysInMonth } from './civilDate'
import { tokenize } from './tokens'
import type {
  CivilDate,
  CivilTime,
  LocaleData,
  ParseResult,
  PickerMode,
  PickerValue,
} from './types'

/**
 * Parses a string into a {@link PickerValue} using the supported PHP date
 * tokens. Mirrors the PHP `DateParser`: an anchored, case-insensitive,
 * unicode-aware regex is built from the format, then validated component by
 * component. Returns an explicit result instead of throwing.
 */
export function parse(
  input: string,
  formatString: string,
  mode: PickerMode,
  locale: LocaleData,
): ParseResult<PickerValue> {
  let pattern = ''
  const order: string[] = []

  for (const segment of tokenize(formatString)) {
    if (segment.type === 'literal') {
      pattern += escapeRegExp(segment.value)
      continue
    }
    pattern += tokenPattern(segment.value, locale)
    order.push(segment.value)
  }

  let regex: RegExp
  try {
    regex = new RegExp(`^${pattern}$`, 'iu')
  } catch {
    return { ok: false, error: 'invalid_format' }
  }

  const match = regex.exec(input.trim())
  if (match === null) {
    return { ok: false, error: 'no_match' }
  }

  const captures: Record<string, string> = {}
  order.forEach((token, index) => {
    captures[token] = match[index + 1] ?? ''
  })

  return build(captures, mode, locale)
}

function escapeRegExp(value: string): string {
  return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
}

function tokenPattern(token: string, locale: LocaleData): string {
  switch (token) {
    case 'Y':
      return '(\\d{4})'
    case 'y':
    case 'm':
    case 'd':
    case 'H':
    case 'h':
    case 'i':
    case 's':
      return '(\\d{2})'
    case 'n':
    case 'j':
    case 'G':
    case 'g':
      return '(\\d{1,2})'
    case 'N':
      return '([1-7])'
    case 'w':
      return '([0-6])'
    case 'A':
    case 'a':
      return '([AaPp][Mm])'
    case 'D':
      return namesAlternation(locale.weekdaysShort)
    case 'l':
      return namesAlternation(locale.weekdays)
    case 'M':
      return namesAlternation(locale.monthsShort)
    case 'F':
      return namesAlternation(locale.months)
    default:
      return escapeRegExp(token)
  }
}

function namesAlternation(names: string[]): string {
  return `(${names.map(escapeRegExp).join('|')})`
}

function build(
  captures: Record<string, string>,
  mode: PickerMode,
  locale: LocaleData,
): ParseResult<PickerValue> {
  let date: CivilDate | null = null
  let time: CivilTime | null = null

  if (mode !== 'time') {
    // Month formats (e.g. "Y-m") carry no day token, so default the day to the
    // 1st — a month value is represented as a date on day 1.
    const built = buildDate(captures, locale, mode === 'month')
    if (built === null) {
      return { ok: false, error: 'invalid_date' }
    }
    date = built
  }

  if (mode === 'time' || mode === 'datetime') {
    const built = buildTime(captures)
    if (built === null) {
      return { ok: false, error: 'invalid_time' }
    }
    time = built
  }

  return { ok: true, value: { date, time } }
}

function buildDate(
  captures: Record<string, string>,
  locale: LocaleData,
  defaultDay = false,
): CivilDate | null {
  const year = resolveYear(captures)
  const month = resolveMonth(captures, locale)
  const day = resolveDay(captures) ?? (defaultDay ? 1 : null)

  if (year === null || month === null || day === null) {
    return null
  }

  if (month < 1 || month > 12 || day < 1 || day > daysInMonth(year, month)) {
    return null
  }

  return { year, month, day }
}

function buildTime(captures: Record<string, string>): CivilTime | null {
  const hour = resolveHour(captures)
  const minute = captures.i ? toInt(captures.i) : null
  const second = captures.s ? toInt(captures.s) : 0

  if (hour === null || minute === null) {
    return null
  }

  if (hour < 0 || hour > 23 || minute < 0 || minute > 59 || second < 0 || second > 59) {
    return null
  }

  return { hour, minute, second }
}

function resolveYear(captures: Record<string, string>): number | null {
  if (captures.Y) return toInt(captures.Y)
  if (captures.y) return 2000 + toInt(captures.y)
  return null
}

function resolveMonth(captures: Record<string, string>, locale: LocaleData): number | null {
  if (captures.m) return toInt(captures.m)
  if (captures.n) return toInt(captures.n)

  for (const token of ['F', 'M'] as const) {
    const value = captures[token]
    if (value) {
      const month = monthFromName(value, locale)
      if (month !== null) return month
    }
  }

  return null
}

function resolveDay(captures: Record<string, string>): number | null {
  if (captures.d) return toInt(captures.d)
  if (captures.j) return toInt(captures.j)
  return null
}

function resolveHour(captures: Record<string, string>): number | null {
  if (captures.H) return toInt(captures.H)
  if (captures.G) return toInt(captures.G)

  const raw = captures.h || captures.g
  if (raw) {
    const hour12Value = toInt(raw) % 12
    const meridiem = resolveMeridiem(captures)
    if (meridiem === 'PM') return hour12Value + 12
    if (meridiem === 'AM') return hour12Value
    return toInt(raw)
  }

  return null
}

function resolveMeridiem(captures: Record<string, string>): 'AM' | 'PM' | null {
  const raw = captures.A || captures.a
  if (!raw) return null
  return raw[0]?.toUpperCase() === 'P' ? 'PM' : 'AM'
}

function monthFromName(name: string, locale: LocaleData): number | null {
  const needle = name.trim().toLowerCase()

  const full = locale.months.findIndex((month) => month.toLowerCase() === needle)
  if (full !== -1) return full + 1

  const short = locale.monthsShort.findIndex((month) => month.toLowerCase() === needle)
  if (short !== -1) return short + 1

  return null
}

function toInt(value: string): number {
  return parseInt(value, 10)
}
