<!DOCTYPE html>
<html lang="en" class="{{ request()->boolean('dark') ? 'dark' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Datepicker Workbench</title>
    <style>[x-cloak]{display:none !important}</style>
    {{-- Compiled Tailwind (pnpm workbench:css) so the default styling is visible. --}}
    <link rel="stylesheet" href="/assets/app.css">
    @livewireStyles
    {{-- Load the package's prebuilt global build before Livewire boots Alpine. --}}
    <script src="/assets/datepicker.js"></script>
</head>
<body class="overflow-y-scroll">
    {{ $slot }}
    @livewireScripts
</body>
</html>
