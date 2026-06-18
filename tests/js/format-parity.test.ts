import { describe, expect, it } from 'vitest'
import rawFixtures from '../fixtures/format-cases.json'
import { format } from '@datepicker/domain/formatter'
import { parse } from '@datepicker/domain/parser'
import { ENGLISH_LOCALE } from '@datepicker/domain/locale'
import type { PickerMode, PickerValue } from '@datepicker/domain/types'

interface FixtureCase {
  name: string
  mode: PickerMode
  format: string
  value: Record<string, number>
  formatted: string
  parseable: boolean
}

const fixtures = rawFixtures as unknown as FixtureCase[]

function fixtureValue(testCase: FixtureCase): PickerValue {
  const v = testCase.value
  const date =
    testCase.mode === 'time'
      ? null
      : { year: v.year as number, month: v.month as number, day: v.day as number }
  const time =
    testCase.mode === 'date'
      ? null
      : { hour: v.hour as number, minute: v.minute as number, second: (v.second ?? 0) as number }
  return { date, time }
}

describe('format/parse parity with the shared PHP fixtures', () => {
  for (const testCase of fixtures) {
    it(`formats: ${testCase.name}`, () => {
      expect(format(fixtureValue(testCase), testCase.format, ENGLISH_LOCALE)).toBe(
        testCase.formatted,
      )
    })

    if (testCase.parseable) {
      it(`round-trips: ${testCase.name}`, () => {
        const result = parse(testCase.formatted, testCase.format, testCase.mode, ENGLISH_LOCALE)
        expect(result.ok).toBe(true)
        if (result.ok) {
          expect(format(result.value, testCase.format, ENGLISH_LOCALE)).toBe(testCase.formatted)
        }
      })
    }
  }

  it('returns an explicit failure for unparseable input', () => {
    const result = parse('not-a-date', 'Y-m-d', 'date', ENGLISH_LOCALE)
    expect(result.ok).toBe(false)
  })

  it('rejects out-of-range components', () => {
    expect(parse('2026-13-01', 'Y-m-d', 'date', ENGLISH_LOCALE).ok).toBe(false)
    expect(parse('2026-02-30', 'Y-m-d', 'date', ENGLISH_LOCALE).ok).toBe(false)
  })
})
