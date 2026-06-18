<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Workbench\App\Livewire\DemoForm;

// Serve the prebuilt global build directly so E2E does not depend on a publish step.
Route::get('/assets/datepicker.js', function () {
    $path = dirname(__DIR__, 2) . '/dist/datepicker.iife.js';
    abort_unless(is_file($path), 404, 'Run `pnpm build` before the E2E suite.');

    return response()->file($path, ['Content-Type' => 'application/javascript']);
});

// Serve the compiled Tailwind stylesheet so `make serve` / `make screenshots`
// show the real default styling. Built by `pnpm workbench:css`. Missing file is
// not an error: the behaviour-only E2E suite runs fine unstyled, so we return an
// empty sheet instead of a 404 to keep the console clean.
Route::get('/assets/app.css', function () {
    $path = dirname(__DIR__) . '/build/app.css';
    $css = is_file($path) ? (string) file_get_contents($path) : '';

    return response($css, 200, ['Content-Type' => 'text/css']);
});

Route::get('/', DemoForm::class);

Route::view('/plain', 'workbench::plain');

// Clean, single-picker page used by `make screenshots` to capture README images.
Route::view('/showcase', 'workbench::showcase');
