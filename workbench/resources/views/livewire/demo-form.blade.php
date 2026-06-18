<div style="max-width: 32rem; margin: 2rem auto; font-family: sans-serif;">
    <h1>Datepicker E2E</h1>

    <section data-testid="birthday-section" style="margin-bottom:1.5rem">
        <label>Birthday (date)</label>
        <x-date-picker wire:model.live="birthday" id="birthday" />
        <p>server: <span data-testid="birthday-value">{{ $birthday }}</span></p>
    </section>

    <section data-testid="starts-section" style="margin-bottom:1.5rem">
        <label>Starts at (datetime)</label>
        <x-date-time-picker wire:model.live="starts_at" id="starts" />
        <p>server: <span data-testid="starts-value">{{ $starts_at }}</span></p>
    </section>

    <section data-testid="opens-section" style="margin-bottom:1.5rem">
        <label>Opens at (time)</label>
        <x-time-picker wire:model.live="opens_at" id="opens" />
        <p>server: <span data-testid="opens-value">{{ $opens_at }}</span></p>
    </section>

    <section data-testid="reject-section" style="margin-bottom:1.5rem">
        <label>Reject demo (server overrides value)</label>
        <x-date-picker wire:model.live="reject_date" id="reject" />
        <p>server: <span data-testid="reject-value">{{ $reject_date }}</span></p>
    </section>

    <form wire:submit="save" data-testid="validate-form" style="margin-bottom:1.5rem">
        <label>Published at (required)</label>
        <x-date-picker wire:model.live="published_at" id="published" />
        <span data-testid="published-value">{{ $published_at }}</span>
        <button type="submit">Save</button>
        @error('published_at')
            <span data-testid="published-error">{{ $message }}</span>
        @enderror
        @if ($saved)
            <span data-testid="saved">saved</span>
        @endif
    </form>
</div>
