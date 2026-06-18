<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Component;
use TrustMedical\LivewireDatepickerUi\Rules\ValidSelection;

/**
 * Example Livewire component using the picker with optimistic live binding and
 * server-side validation that mirrors the client constraints.
 */
class AppointmentForm extends Component
{
    public ?string $starts_at = null;

    public ?string $published_at = null;

    public function rules(): array
    {
        return [
            'starts_at' => [
                'required',
                new ValidSelection('datetime', [
                    'valueFormat' => 'Y-m-d\TH:i:s',
                    'min' => now()->toDateString(),
                    'disabledWeekdays' => [0, 6], // no weekends
                    'minuteStep' => 15,
                ]),
            ],
            'published_at' => [
                'nullable',
                new ValidSelection('date', ['valueFormat' => 'Y-m-d']),
            ],
        ];
    }

    public function save(): void
    {
        $this->validate();

        // ... persist the appointment ...

        session()->flash('status', 'Appointment booked.');
    }

    public function render()
    {
        return view('livewire.appointment-form');
    }
}
