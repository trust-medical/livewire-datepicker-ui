<?php

declare(strict_types=1);

namespace Workbench\App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Exercises every binding scenario the Playwright suite checks:
 * successful live sync, server-side rollback, and validation errors.
 */
class DemoForm extends Component
{
    public ?string $birthday = null;

    public ?string $starts_at = null;

    public ?string $opens_at = null;

    public ?string $reject_date = null;

    public ?string $published_at = null;

    public bool $saved = false;

    /** Simulate the server overriding the submitted value so the client rolls back. */
    public function updatedRejectDate(): void
    {
        $this->reject_date = '2000-01-01';
    }

    public function save(): void
    {
        $this->saved = false;
        $this->validate(['published_at' => ['required']]);
        $this->saved = true;
    }

    public function render(): View
    {
        return view('workbench::livewire.demo-form')->layout('workbench::components.layout');
    }
}
