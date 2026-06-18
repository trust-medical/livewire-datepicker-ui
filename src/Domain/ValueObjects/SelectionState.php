<?php

declare(strict_types=1);

namespace TrustMedical\LivewireDatepickerUi\Domain\ValueObjects;

use TrustMedical\LivewireDatepickerUi\Domain\Enums\SelectionStatus;
use TrustMedical\LivewireDatepickerUi\Domain\Services\SelectionReducer;

/**
 * The optimistic-update state machine value.
 *
 * `value` is what the UI currently shows; `committed` is the last server-confirmed
 * snapshot a rejection rolls back to. Transitions live in
 * {@see SelectionReducer}.
 */
final class SelectionState
{
    public function __construct(
        public readonly SelectionStatus $status,
        public readonly ?DateTimeValue $value,
        public readonly ?DateTimeValue $committed,
    ) {}

    public static function idle(?DateTimeValue $value = null): self
    {
        return new self(SelectionStatus::Idle, $value, $value);
    }

    public function withStatus(SelectionStatus $status): self
    {
        return new self($status, $this->value, $this->committed);
    }

    public function withValue(?DateTimeValue $value): self
    {
        return new self($this->status, $value, $this->committed);
    }

    public function withCommitted(?DateTimeValue $committed): self
    {
        return new self($this->status, $this->value, $committed);
    }

    public function hasValue(): bool
    {
        return $this->value !== null;
    }

    public function valueEquals(?DateTimeValue $other): bool
    {
        if ($this->value === null || $other === null) {
            return $this->value === $other;
        }

        return $this->value->equals($other);
    }

    /**
     * @return array{status: string, value: ?array<string, mixed>, committed: ?array<string, mixed>}
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status->value,
            'value' => $this->value?->toArray(),
            'committed' => $this->committed?->toArray(),
        ];
    }
}
