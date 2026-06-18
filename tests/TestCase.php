<?php

declare(strict_types=1);

namespace TrustMedical\LivewireDatepickerUi\Tests;

use Illuminate\Foundation\Application;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use TrustMedical\LivewireDatepickerUi\Infrastructure\Laravel\DatepickerServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * @param  Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            DatepickerServiceProvider::class,
        ];
    }
}
