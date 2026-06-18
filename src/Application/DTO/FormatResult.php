<?php

declare(strict_types=1);

namespace TrustMedical\LivewireDatepickerUi\Application\DTO;

/**
 * The two string representations of a single value: the submit value
 * (`value-format`, bound to Livewire) and the human display value
 * (`display-format`, shown in the input).
 */
final class FormatResult
{
    public function __construct(
        public readonly string $value,
        public readonly string $display,
    ) {}

    /** @return array{value: string, display: string} */
    public function toArray(): array
    {
        return ['value' => $this->value, 'display' => $this->display];
    }
}
