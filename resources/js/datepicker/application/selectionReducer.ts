import type { PickerValue, SelectionStatus } from '../domain/types'

export interface SelectionState {
  status: SelectionStatus
  value: PickerValue | null
  committed: PickerValue | null
}

/**
 * Pure transitions for the optimistic-update state machine. Mirrors the PHP
 * `SelectionReducer` exactly so the documented behaviour is identical on both
 * sides.
 */
export function idle(value: PickerValue | null = null): SelectionState {
  return { status: 'idle', value, committed: value }
}

export function select(state: SelectionState, value: PickerValue | null): SelectionState {
  return { status: 'pending', value, committed: state.committed }
}

export function clear(state: SelectionState): SelectionState {
  return { status: 'pending', value: null, committed: state.committed }
}

export function beginCommit(state: SelectionState): SelectionState {
  return { ...state, status: 'pending' }
}

/** The server accepted the value: it becomes the new committed snapshot. */
export function confirm(_state: SelectionState, serverValue: PickerValue | null): SelectionState {
  return { status: 'committed', value: serverValue, committed: serverValue }
}

/** The server changed the value: roll back to the last committed snapshot. */
export function reject(state: SelectionState): SelectionState {
  return { status: 'rejected', value: state.committed, committed: state.committed }
}

/** Validation failed but the value is unchanged: keep it, flag invalid. */
export function invalidate(state: SelectionState): SelectionState {
  return { ...state, status: 'invalid' }
}
