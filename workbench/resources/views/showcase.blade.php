<!DOCTYPE html>
<html lang="en" class="{{ request()->boolean('dark') ? 'dark' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Datepicker Showcase</title>
    <style>[x-cloak]{display:none !important}</style>
    {{-- Compiled Tailwind (pnpm workbench:css) so the default styling is visible. --}}
    <link rel="stylesheet" href="/assets/app.css">
    @livewireStyles
    {{-- Load the package's prebuilt global build before Livewire boots Alpine. --}}
    <script src="/assets/datepicker.js"></script>
</head>
<body>
    {{-- Clean, framed page used to capture the README screenshots
         (`make screenshots`). The picker is rendered with a preset value so the
         "selected" / "today" states are visible when the calendar opens. --}}
    @php($mode = in_array(request('mode'), ['date', 'time', 'datetime'], true) ? request('mode') : 'date')
    @php($locale = request('lang') === 'ja' ? 'ja' : 'en')

    <div class="flex min-h-screen items-start justify-center bg-zinc-50 px-6 pt-12 dark:bg-zinc-950">
        <div class="max-w-88">
            @if ($mode === 'time')
                <x-time-picker name="showcase" id="showcase" :locale="$locale" value="09:30:00" />
            @elseif ($mode === 'datetime')
                <x-date-time-picker name="showcase" id="showcase" :locale="$locale" value="2026-06-15T09:30:00" />
            @else
                <x-date-picker name="showcase" id="showcase" :locale="$locale" value="2026-06-15" />
            @endif
        </div>
    </div>

    @livewireScripts
</body>
</html>
