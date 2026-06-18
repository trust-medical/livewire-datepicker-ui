<?php

declare(strict_types=1);

namespace TrustMedical\LivewireDatepickerUi\Domain\Enums;

/**
 * Lifecycle of a selection as it travels from the UI to the server.
 *
 * Mirrors the TypeScript `SelectionStatus` union so both sides agree on the
 * optimistic-update state machine.
 */
enum SelectionStatus: string
{
    case Idle = 'idle';
    case Pending = 'pending';
    case Committed = 'committed';
    case Rejected = 'rejected';
    case Invalid = 'invalid';
}
