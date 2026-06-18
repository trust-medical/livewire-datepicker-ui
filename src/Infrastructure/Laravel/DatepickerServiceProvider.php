<?php

declare(strict_types=1);

namespace TrustMedical\LivewireDatepickerUi\Infrastructure\Laravel;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use TrustMedical\LivewireDatepickerUi\Infrastructure\View\Components\Datepicker;

final class DatepickerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom($this->packagePath('config/datepicker.php'), 'datepicker');
    }

    public function boot(): void
    {
        $this->loadViewsFrom($this->packagePath('resources/views'), 'datepicker');
        $this->loadTranslationsFrom($this->packagePath('resources/lang'), 'datepicker');

        $this->registerComponents();
        $this->registerDirectives();
        $this->registerPublishing();
    }

    private function registerComponents(): void
    {
        // <x-datepicker> binds to the class component.
        Blade::component(Datepicker::class, 'datepicker');

        // <x-date-picker> / <x-time-picker> / <x-date-time-picker> are anonymous
        // components that forward to <x-datepicker> with a pinned mode.
        Blade::anonymousComponentPath($this->packagePath('resources/views/components'));
    }

    private function registerDirectives(): void
    {
        Blade::directive(
            'datepickerScripts',
            static fn (): string => '<?php echo \\TrustMedical\\LivewireDatepickerUi\\Infrastructure\\Laravel\\BladeDirectives::scripts(); ?>',
        );

        Blade::directive(
            'datepickerStyles',
            static fn (): string => '<?php echo \\TrustMedical\\LivewireDatepickerUi\\Infrastructure\\Laravel\\BladeDirectives::styles(); ?>',
        );
    }

    private function registerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            $this->packagePath('config/datepicker.php') => config_path('datepicker.php'),
        ], 'datepicker-config');

        $this->publishes([
            $this->packagePath('resources/views') => resource_path('views/vendor/datepicker'),
        ], 'datepicker-views');

        $this->publishes([
            $this->packagePath('resources/lang') => $this->langPublishPath(),
        ], 'datepicker-lang');

        $this->publishes([
            $this->packagePath('dist') => public_path($this->assetsPath()),
        ], 'datepicker-assets');
    }

    private function assetsPath(): string
    {
        /** @var array<string, mixed> $assets */
        $assets = (array) config('datepicker.assets', []);

        return is_string($assets['path'] ?? null) ? trim($assets['path'], '/') : 'vendor/datepicker';
    }

    private function langPublishPath(): string
    {
        return function_exists('lang_path')
            ? lang_path('vendor/datepicker')
            : resource_path('lang/vendor/datepicker');
    }

    private function packagePath(string $path): string
    {
        return dirname(__DIR__, 3) . '/' . ltrim($path, '/');
    }
}
