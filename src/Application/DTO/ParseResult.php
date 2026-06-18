<?php

declare(strict_types=1);

namespace TrustMedical\LivewireDatepickerUi\Application\DTO;

use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateTimeValue;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateValue;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\TimeValue;

/**
 * The outcome of parsing user/stored input — an explicit success/failure result
 * rather than a thrown exception, so callers can branch without a try/catch.
 */
final class ParseResult
{
    private function __construct(
        public readonly bool $successful,
        public readonly DateValue|TimeValue|DateTimeValue|null $value,
        public readonly ?string $error,
    ) {}

    public static function success(DateValue|TimeValue|DateTimeValue $value): self
    {
        return new self(true, $value, null);
    }

    public static function failure(string $error): self
    {
        return new self(false, null, $error);
    }

    public function failed(): bool
    {
        return ! $this->successful;
    }
}
