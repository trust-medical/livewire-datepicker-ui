<?php

declare(strict_types=1);

use TrustMedical\LivewireDatepickerUi\Domain\Enums\SelectionStatus;
use TrustMedical\LivewireDatepickerUi\Domain\Services\SelectionReducer;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateTimeValue;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\SelectionState;

beforeEach(function () {
    $this->reducer = new SelectionReducer;
    $this->committed = DateTimeValue::fromComponents(2026, 6, 1);
    $this->state = SelectionState::idle($this->committed);
});

it('marks a selection pending while keeping the committed snapshot', function () {
    $picked = DateTimeValue::fromComponents(2026, 6, 18);
    $next = $this->reducer->select($this->state, $picked);

    expect($next->status)->toBe(SelectionStatus::Pending)
        ->and($next->value?->equals($picked))->toBeTrue()
        ->and($next->committed?->equals($this->committed))->toBeTrue();
});

it('confirms with the authoritative server value', function () {
    $picked = DateTimeValue::fromComponents(2026, 6, 18);
    $pending = $this->reducer->select($this->state, $picked);
    $committed = $this->reducer->confirm($pending, $picked);

    expect($committed->status)->toBe(SelectionStatus::Committed)
        ->and($committed->value?->equals($picked))->toBeTrue()
        ->and($committed->committed?->equals($picked))->toBeTrue();
});

it('rolls back to the committed snapshot on rejection', function () {
    $picked = DateTimeValue::fromComponents(2026, 6, 18);
    $pending = $this->reducer->select($this->state, $picked);
    $rejected = $this->reducer->reject($pending);

    expect($rejected->status)->toBe(SelectionStatus::Rejected)
        ->and($rejected->value?->equals($this->committed))->toBeTrue()
        ->and($rejected->committed?->equals($this->committed))->toBeTrue();
});

it('keeps the optimistic value but flags it invalid on validation failure', function () {
    $picked = DateTimeValue::fromComponents(2026, 6, 18);
    $pending = $this->reducer->select($this->state, $picked);
    $invalid = $this->reducer->invalidate($pending);

    expect($invalid->status)->toBe(SelectionStatus::Invalid)
        ->and($invalid->value?->equals($picked))->toBeTrue();
});

it('clears optimistically', function () {
    $cleared = $this->reducer->clear($this->state);

    expect($cleared->status)->toBe(SelectionStatus::Pending)
        ->and($cleared->value)->toBeNull()
        ->and($cleared->committed?->equals($this->committed))->toBeTrue();
});
