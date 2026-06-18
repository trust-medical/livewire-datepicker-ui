<div>
    {{-- Optimistic live binding: the UI updates instantly, syncs to Livewire in
         the background, and rolls back automatically if the server rejects it. --}}
    <form wire:submit="save">
        <label>Starts at (weekends disabled, 15-minute steps)</label>
        <x-date-time-picker
            wire:model.live="starts_at"
            display-format="Y-m-d H:i"
            value-format="Y-m-d\TH:i:s"
            :min="now()->format('Y-m-d')"
            :disabled-weekdays="[0, 6]"
            :minute-step="15"
            clearable
        />
        @error('starts_at') <p class="text-red-600">{{ $message }}</p> @enderror

        <label>Publish date (optional)</label>
        <x-date-picker wire:model.blur="published_at" clearable />
        @error('published_at') <p class="text-red-600">{{ $message }}</p> @enderror

        <button type="submit">Book</button>
    </form>

    @if (session('status'))
        <p>{{ session('status') }}</p>
    @endif
</div>
