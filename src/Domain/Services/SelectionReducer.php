<?php

declare(strict_types=1);

namespace TrustMedical\LivewireDatepickerUi\Domain\Services;

use TrustMedical\LivewireDatepickerUi\Domain\Enums\SelectionStatus;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\DateTimeValue;
use TrustMedical\LivewireDatepickerUi\Domain\ValueObjects\SelectionState;

/**
 * Pure transitions for the optimistic-update state machine.
 *
 * Each method returns a new {@see SelectionState}; nothing mutates. This is the
 * canonical reference the TypeScript reducer is verified against.
 */
final class SelectionReducer
{
    /** The user picked a value: show it immediately, mark pending sync. */
    public function select(SelectionState $state, ?DateTimeValue $value): SelectionState
    {
        return new SelectionState(SelectionStatus::Pending, $value, $state->committed);
    }

    /** The user cleared the value: optimistically empty, mark pending sync. */
    public function clear(SelectionState $state): SelectionState
    {
        return new SelectionState(SelectionStatus::Pending, null, $state->committed);
    }

    /** A sync round-trip is starting for the current value. */
    public function beginCommit(SelectionState $state): SelectionState
    {
        return $state->withStatus(SelectionStatus::Pending);
    }

    /** The server accepted the value (authoritative value wins, snapshot updates). */
    public function confirm(SelectionState $state, ?DateTimeValue $serverValue): SelectionState
    {
        return new SelectionState(SelectionStatus::Committed, $serverValue, $serverValue);
    }

    /** The server changed the value: roll back to the last committed snapshot. */
    public function reject(SelectionState $state): SelectionState
    {
        return new SelectionState(SelectionStatus::Rejected, $state->committed, $state->committed);
    }

    /** Validation failed but the value is unchanged: keep it, flag invalid. */
    public function invalidate(SelectionState $state): SelectionState
    {
        return $state->withStatus(SelectionStatus::Invalid);
    }

    /** Reset to a clean idle state seeded with an initial value. */
    public function reset(?DateTimeValue $value): SelectionState
    {
        return SelectionState::idle($value);
    }
}
