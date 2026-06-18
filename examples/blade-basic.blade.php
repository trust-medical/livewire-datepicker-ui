{{-- Plain Blade form (no Livewire). Requires Alpine to be loaded on the page. --}}
<form method="post" action="/profile">
    @csrf

    <label for="dob">Date of birth</label>
    {{-- Submits `dob` in the value-format (Y-m-d by default). --}}
    <x-date-picker name="dob" display-format="F j, Y" required />

    <label for="reminder">Reminder time</label>
    <x-time-picker name="reminder" :minute-step="15" hour-cycle="12" />

    <button type="submit">Save</button>
</form>
