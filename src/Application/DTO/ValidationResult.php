<?php

declare(strict_types=1);

namespace TrustMedical\LivewireDatepickerUi\Application\DTO;

/**
 * The result of validating a selection against the configured constraints.
 */
final class ValidationResult
{
    /**
     * @param  list<string>  $reasons  Machine-readable reason codes (e.g. "below_min").
     */
    private function __construct(
        public readonly bool $valid,
        public readonly array $reasons,
    ) {}

    public static function valid(): self
    {
        return new self(true, []);
    }

    /**
     * @param  list<string>  $reasons
     */
    public static function invalid(array $reasons): self
    {
        return new self(false, $reasons);
    }

    public function failed(): bool
    {
        return ! $this->valid;
    }
}
