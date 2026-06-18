<?php

declare(strict_types=1);

use TrustMedical\LivewireDatepickerUi\Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case binding
|--------------------------------------------------------------------------
|
| Feature tests boot a Testbench Laravel application via the package
| TestCase. Unit tests run as plain PHP (no framework) so the domain layer
| stays verifiable in isolation.
|
*/

pest()->extend(TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Shared helpers
|--------------------------------------------------------------------------
*/

/**
 * Load the format fixtures shared between the PHP and TypeScript suites.
 *
 * @return list<array<string, mixed>>
 */
function formatFixtures(): array
{
    $json = file_get_contents(__DIR__ . '/fixtures/format-cases.json');

    /** @var list<array<string, mixed>> $cases */
    $cases = json_decode($json ?: '[]', true, flags: JSON_THROW_ON_ERROR);

    return $cases;
}
