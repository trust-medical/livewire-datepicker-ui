<x-workbench::components.layout>
    <div style="max-width: 32rem; margin: 2rem auto; font-family: sans-serif;">
        <h1>Plain Blade (no Livewire binding)</h1>

        {{-- Works with the Alpine that Livewire ships, even without a wire:model. --}}
        <form method="get" data-testid="plain-form">
            <x-date-picker name="plain_date" id="plain" />
            <button type="submit">Submit</button>
        </form>
    </div>
</x-workbench::components.layout>
