<?php

declare(strict_types=1);

namespace TrustMedical\LivewireDatepickerUi\Infrastructure\Laravel;

/**
 * Renders the markup injected by the @datepickerScripts / @datepickerStyles
 * Blade directives, pointing at the published assets in public/.
 */
final class BladeDirectives
{
    public static function scripts(): string
    {
        $url = self::asset('datepicker.iife.js');

        return '<script src="' . e($url) . '" defer></script>';
    }

    public static function styles(): string
    {
        $url = self::asset('datepicker.css');

        return '<link rel="stylesheet" href="' . e($url) . '">';
    }

    private static function asset(string $file): string
    {
        /** @var array<string, mixed> $assets */
        $assets = (array) config('datepicker.assets', []);
        $base = is_string($assets['path'] ?? null) ? trim($assets['path'], '/') : 'vendor/datepicker';

        $publicPath = public_path($base . '/' . $file);
        $version = is_file($publicPath) ? (string) filemtime($publicPath) : null;

        $url = asset($base . '/' . $file);

        return $version !== null ? $url . '?id=' . $version : $url;
    }
}
