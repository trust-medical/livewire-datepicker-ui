<?php

declare(strict_types=1);

namespace TrustMedical\LivewireDatepickerUi\Support;

/**
 * Merges layered class maps into a single resolved map.
 *
 * Resolution order is "last wins per key": package defaults are overridden by
 * the published config, then by a named theme, then by the per-instance
 * `classes` prop. Keys absent from a later layer keep their earlier value, so a
 * consumer only needs to override the specific slots they care about.
 */
final class ClassMerger
{
    /**
     * @param  array<string, string>  ...$maps  Ordered from lowest to highest precedence.
     * @return array<string, string>
     */
    public static function merge(array ...$maps): array
    {
        $resolved = [];

        foreach ($maps as $map) {
            foreach ($map as $key => $value) {
                $trimmed = trim($value);

                // An explicit empty string clears a slot; a missing key leaves it.
                $resolved[$key] = $trimmed;
            }
        }

        return $resolved;
    }

    /**
     * Append additional utility classes to a single resolved slot, de-duplicating
     * tokens while preserving order.
     */
    public static function append(string $base, string ...$additional): string
    {
        $tokens = preg_split('/\s+/', trim($base), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        foreach ($additional as $extra) {
            foreach (preg_split('/\s+/', trim($extra), -1, PREG_SPLIT_NO_EMPTY) ?: [] as $token) {
                if (! in_array($token, $tokens, true)) {
                    $tokens[] = $token;
                }
            }
        }

        return implode(' ', $tokens);
    }
}
