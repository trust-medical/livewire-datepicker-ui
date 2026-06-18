/**
 * Tokeniser for the supported PHP-style date tokens. Mirrors the PHP
 * `DateFormatTokens` class exactly so both sides agree on format parsing.
 *
 *   Date:  Y y m n d j N w D l M F
 *   Time:  H G h g i s A a
 */
export const DATE_TOKENS = ['Y', 'y', 'm', 'n', 'd', 'j', 'N', 'w', 'D', 'l', 'M', 'F'] as const
export const TIME_TOKENS = ['H', 'G', 'h', 'g', 'i', 's', 'A', 'a'] as const

const DATE_SET = new Set<string>(DATE_TOKENS)
const TIME_SET = new Set<string>(TIME_TOKENS)

export function isDateToken(char: string): boolean {
  return DATE_SET.has(char)
}

export function isTimeToken(char: string): boolean {
  return TIME_SET.has(char)
}

export function isToken(char: string): boolean {
  return DATE_SET.has(char) || TIME_SET.has(char)
}

export interface Segment {
  type: 'literal' | 'token'
  value: string
}

/**
 * Split a format string into literal/token segments. A backslash escapes the
 * next character into a literal; multibyte literals are preserved.
 */
export function tokenize(format: string): Segment[] {
  const chars = Array.from(format)
  const segments: Segment[] = []

  for (let i = 0; i < chars.length; i++) {
    const char = chars[i] as string

    if (char === '\\') {
      const next = chars[i + 1]
      if (next !== undefined) {
        segments.push({ type: 'literal', value: next })
        i++
      }
      continue
    }

    segments.push({ type: isToken(char) ? 'token' : 'literal', value: char })
  }

  return segments
}
