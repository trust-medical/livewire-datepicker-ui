<?php

declare(strict_types=1);

namespace TrustMedical\LivewireDatepickerUi\Support;

/**
 * Single source of truth for the supported PHP-style date tokens.
 *
 * The token semantics are mirrored exactly by the TypeScript formatter/parser
 * (`resources/js/datepicker/domain/formatter.ts`). Any change here must be
 * reflected there and in `tests/fixtures/format-cases.json`.
 *
 * Supported tokens:
 *   Date:  Y y m n d j N w D l M F
 *   Time:  H G h g i s A a
 */
final class DateFormatTokens
{
    /** @var list<string> */
    public const DATE_TOKENS = ['Y', 'y', 'm', 'n', 'd', 'j', 'N', 'w', 'D', 'l', 'M', 'F'];

    /** @var list<string> */
    public const TIME_TOKENS = ['H', 'G', 'h', 'g', 'i', 's', 'A', 'a'];

    /** @return list<string> */
    public static function all(): array
    {
        return [...self::DATE_TOKENS, ...self::TIME_TOKENS];
    }

    public static function isToken(string $char): bool
    {
        return in_array($char, self::all(), true);
    }

    public static function isDateToken(string $char): bool
    {
        return in_array($char, self::DATE_TOKENS, true);
    }

    public static function isTimeToken(string $char): bool
    {
        return in_array($char, self::TIME_TOKENS, true);
    }

    /**
     * Split a format string into literal/token segments.
     *
     * A backslash escapes the next character into a literal (e.g. `\T` => "T"),
     * which is how ISO-like value formats such as `Y-m-d\TH:i:s` are expressed.
     * Multibyte literals (e.g. Japanese 年月日) are preserved.
     *
     * @return list<array{type: 'literal'|'token', value: string}>
     */
    public static function tokenize(string $format): array
    {
        /** @var list<string> $chars */
        $chars = preg_split('//u', $format, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $segments = [];
        $count = count($chars);

        for ($i = 0; $i < $count; $i++) {
            $char = $chars[$i];

            if ($char === '\\') {
                $next = $chars[$i + 1] ?? null;
                if ($next !== null) {
                    $segments[] = ['type' => 'literal', 'value' => $next];
                    $i++;
                }

                continue;
            }

            $segments[] = [
                'type' => self::isToken($char) ? 'token' : 'literal',
                'value' => $char,
            ];
        }

        return $segments;
    }
}
