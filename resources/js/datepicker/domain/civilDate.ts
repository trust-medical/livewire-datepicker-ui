import type { CivilDate, CivilTime } from './types'

/**
 * Timezone-free civil date/time math. Mirrors the PHP DateValue/TimeValue value
 * objects so the client and server agree on every calculation. `Date` is only
 * ever used in UTC for day arithmetic, never to interpret a date-only string.
 */

function pad(value: number, length = 2): string {
  return String(Math.abs(value)).padStart(length, '0')
}

export function isLeapYear(year: number): boolean {
  return (year % 4 === 0 && year % 100 !== 0) || year % 400 === 0
}

export function daysInMonth(year: number, month: number): number {
  const lengths = [31, isLeapYear(year) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31]
  return lengths[month - 1] ?? 31
}

/** 0 = Sunday .. 6 = Saturday (matches Date.getDay and the PHP `w` token). */
export function weekday(date: CivilDate): number {
  let month = date.month
  let year = date.year

  if (month < 3) {
    month += 12
    year -= 1
  }

  const k = year % 100
  const j = Math.floor(year / 100)
  const h =
    (date.day +
      Math.floor((13 * (month + 1)) / 5) +
      k +
      Math.floor(k / 4) +
      Math.floor(j / 4) +
      5 * j) %
    7

  return (h + 6) % 7
}

export function isoWeekday(date: CivilDate): number {
  const day = weekday(date)
  return day === 0 ? 7 : day
}

export function isWeekend(date: CivilDate): boolean {
  const day = weekday(date)
  return day === 0 || day === 6
}

export function datesEqual(a: CivilDate, b: CivilDate): boolean {
  return a.year === b.year && a.month === b.month && a.day === b.day
}

export function compareDate(a: CivilDate, b: CivilDate): number {
  if (a.year !== b.year) return a.year - b.year
  if (a.month !== b.month) return a.month - b.month
  return a.day - b.day
}

export function addDays(date: CivilDate, days: number): CivilDate {
  // UTC has no DST, so the civil date can never shift unexpectedly.
  const d = new Date(Date.UTC(date.year, date.month - 1, date.day + days))
  return { year: d.getUTCFullYear(), month: d.getUTCMonth() + 1, day: d.getUTCDate() }
}

export function addMonths(date: CivilDate, months: number): CivilDate {
  const total = date.year * 12 + (date.month - 1) + months
  let year = Math.floor(total / 12)
  let month = (total % 12) + 1

  if (month < 1) {
    month += 12
    year -= 1
  }

  const day = Math.min(date.day, daysInMonth(year, month))
  return { year, month, day }
}

export function withDay(date: CivilDate, day: number): CivilDate {
  return { year: date.year, month: date.month, day }
}

export function toIsoDate(date: CivilDate): string {
  return `${pad(date.year, 4)}-${pad(date.month)}-${pad(date.day)}`
}

export function todayLocal(): CivilDate {
  const now = new Date()
  return { year: now.getFullYear(), month: now.getMonth() + 1, day: now.getDate() }
}

export function nowTimeLocal(): CivilTime {
  const now = new Date()
  return { hour: now.getHours(), minute: now.getMinutes(), second: now.getSeconds() }
}

export function totalSeconds(time: CivilTime): number {
  return time.hour * 3600 + time.minute * 60 + time.second
}

export function compareTime(a: CivilTime, b: CivilTime): number {
  return totalSeconds(a) - totalSeconds(b)
}

export function timesEqual(a: CivilTime, b: CivilTime): boolean {
  return totalSeconds(a) === totalSeconds(b)
}

export function hour12(time: CivilTime): number {
  const hour = time.hour % 12
  return hour === 0 ? 12 : hour
}

export function isPm(time: CivilTime): boolean {
  return time.hour >= 12
}

export function toIsoTime(time: CivilTime): string {
  return `${pad(time.hour)}:${pad(time.minute)}:${pad(time.second)}`
}
