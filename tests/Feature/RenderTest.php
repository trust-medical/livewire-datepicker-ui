<?php

declare(strict_types=1);

it('renders the date picker skeleton', function () {
    $this->blade('<x-date-picker name="birthday" />')
        ->assertSee('data-datepicker', false)
        ->assertSee('data-mode="date"', false)
        ->assertSee('role="combobox"', false)
        ->assertSee('role="dialog"', false)
        ->assertSee('role="grid"', false)
        ->assertSee('name="birthday"', false)
        ->assertSee('datepicker(', false); // x-data factory call
});

it('renders weekday headers from the locale', function () {
    $this->blade('<x-date-picker />')
        ->assertSee('>Su<', false)
        ->assertSee('>Mo<', false)
        ->assertSee('>Sa<', false);
});

it('omits the calendar grid in time mode and shows a listbox', function () {
    $this->blade('<x-time-picker name="opens_at" />')
        ->assertSee('data-mode="time"', false)
        ->assertSee('role="listbox"', false)
        ->assertDontSee('role="grid"', false);
});

it('renders both grid and listbox in datetime mode', function () {
    $this->blade('<x-date-time-picker name="starts_at" />')
        ->assertSee('data-mode="datetime"', false)
        ->assertSee('role="grid"', false)
        ->assertSee('role="listbox"', false);
});

it('renders a month grid (no day grid or time list) in month mode', function () {
    $this->blade('<x-month-picker name="invoice_month" />')
        ->assertSee('data-mode="month"', false)
        ->assertSee('role="grid"', false)
        ->assertSee('x-ref="monthGrid"', false)
        ->assertSee('selectMonth(', false)
        ->assertSee('onMonthGridKeydown(', false)
        ->assertDontSee('role="listbox"', false)
        ->assertDontSee('selectDay(', false);
});

it('seeds a month value as Y-m in the hidden input', function () {
    $this->blade('<x-month-picker name="m" value="2026-06-18" />')
        ->assertSee('data-mode="month"', false)
        ->assertSee('value="2026-06"', false);
});

it('reflects custom classes from the prop', function () {
    $this->blade('<x-date-picker :classes="[\'day_selected\' => \'bg-emerald-500\']" />')
        ->assertSee('bg-emerald-500', false);
});

it('applies the component class attribute to the input, merged with the input slot', function () {
    $this->blade('<x-date-picker class="probe-xyz" />')
        ->assertSee('pr-20', false)       // the default input slot is applied …
        ->assertSee('probe-xyz', false);  // … merged with the consumer class
});

it('marks the input required, disabled and readonly', function () {
    $this->blade('<x-date-picker required disabled />')
        ->assertSee('required', false)
        ->assertSee('disabled', false);
});

it('escapes the configured value in the hidden input', function () {
    $this->blade('<x-date-picker name="d" value="2026-06-18" />')
        ->assertSee('value="2026-06-18"', false);
});
