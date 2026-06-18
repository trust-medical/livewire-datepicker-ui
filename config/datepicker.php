<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default mode
    |--------------------------------------------------------------------------
    |
    | The picker mode used by <x-datepicker> when no `mode` prop is given.
    | One of: "date", "time", "datetime", "month".
    |
    */

    'mode' => 'date',

    /*
    |--------------------------------------------------------------------------
    | Formats
    |--------------------------------------------------------------------------
    |
    | Per-mode default formats. `display` is shown in the input, `value` is the
    | string bound to Livewire / submitted with the form. `display_12` is used
    | instead of `display` when `hour_cycle` resolves to 12. Tokens follow PHP's
    | date() syntax (Y y m n d j N w D l M F H G h g i s A a); escape literals
    | with a backslash (e.g. Y-m-d\TH:i:s).
    |
    */

    'formats' => [
        'date' => [
            'display' => 'Y-m-d',
            'value' => 'Y-m-d',
        ],
        'time' => [
            'display' => 'H:i',
            'display_12' => 'h:i A',
            'value' => 'H:i:s',
        ],
        'datetime' => [
            'display' => 'Y-m-d H:i',
            'display_12' => 'Y-m-d h:i A',
            'value' => 'Y-m-d\TH:i:s',
        ],
        'month' => [
            'display' => 'Y-m',
            'value' => 'Y-m',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Locale
    |--------------------------------------------------------------------------
    |
    | "auto" follows the application locale (app()->getLocale()); otherwise set
    | a specific locale code. Names + UI labels are pulled from the package
    | translations (publishable under lang/vendor/datepicker).
    |
    */

    'locale' => 'auto',

    /*
    |--------------------------------------------------------------------------
    | Calendar behaviour
    |--------------------------------------------------------------------------
    |
    | 0 = Sunday .. 6 = Saturday.
    |
    */

    'first_day_of_week' => 0,
    'minute_step' => 5,
    'hour_cycle' => 24,

    /*
    |--------------------------------------------------------------------------
    | UI defaults
    |--------------------------------------------------------------------------
    */

    'placement' => 'bottom-start',
    'clearable' => true,
    'today_button' => true,
    'close_button' => true,
    'debounce_ms' => 200,

    /*
    |--------------------------------------------------------------------------
    | Asset publishing
    |--------------------------------------------------------------------------
    |
    | Where `vendor:publish --tag=datepicker-assets` copies the prebuilt JS/CSS,
    | relative to public/. The @datepickerScripts / @datepickerStyles directives
    | resolve their URLs from here.
    |
    */

    'assets' => [
        'path' => 'vendor/datepicker',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default theme
    |--------------------------------------------------------------------------
    |
    | A named key from `themes` applied to every instance, or null for the
    | built-in default class map. Override per instance with the `theme` prop.
    |
    */

    'default_theme' => null,

    /*
    |--------------------------------------------------------------------------
    | Class map
    |--------------------------------------------------------------------------
    |
    | Tailwind-first, minimal and practical. Override any slot here globally,
    | per theme below, or per instance via the `classes` prop. State slots
    | (day_selected, day_today, ...) are applied by the runtime; the matching
    | data-* attribute (data-selected, data-today, ...) is also set so you can
    | style with arbitrary variants instead, e.g. data-[selected]:bg-zinc-900.
    |
    | Defaults target Tailwind CSS v4 and use the zinc palette (shadcn-inspired),
    | with interactive controls sized to a 44x44 touch target (WCAG 2.5.5). The
    | text input keeps deliberately minimal styling (height, border, rounded,
    | padding); customise it by passing classes via the component's `class`
    | attribute, or by overriding the `input` slot (config / theme / `classes`).
    |
    */

    'classes' => [
        'root' => 'dp-root relative inline-block w-full',

        'input_wrapper' => 'relative flex items-center',
        // Deliberately minimal, neutral defaults (height, border, rounded,
        // padding) so the field is usable out of the box yet easy to restyle.
        // pl-3 + pr-20 keep typed text clear of the clear (right-11) + trigger
        // (right-0) icons. Customise it by passing classes through the component's
        // `class` attribute (they merge onto this slot), or override the `input`
        // slot via config / theme / the `classes` prop. data-[invalid] /
        // data-[pending] are still emitted, so you can target them too
        // (e.g. data-[invalid]:border-red-500).
        'input' => 'h-11 w-full rounded-md border border-zinc-300 pl-3 pr-20 text-sm text-zinc-900 placeholder:text-zinc-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-zinc-400/40 dark:border-zinc-700 dark:text-zinc-50 dark:placeholder:text-zinc-500',
        'trigger' => 'absolute inset-y-0 right-0 inline-flex w-11 items-center justify-center rounded-md text-zinc-500 transition-colors hover:text-zinc-900 disabled:cursor-not-allowed disabled:opacity-50 dark:text-zinc-400 dark:hover:text-zinc-50',
        'clear' => 'absolute inset-y-0 right-11 inline-flex w-11 items-center justify-center rounded-md text-zinc-400 transition-colors hover:text-zinc-900 dark:text-zinc-500 dark:hover:text-zinc-50',

        // `fixed` (viewport-positioned) is intentional: it keeps the panel out of
        // document flow so opening it never grows the page / toggles the scrollbar
        // (which caused a flicker). positionPanel() sets the exact top/left/maxHeight.
        'popover' => 'dp-popover fixed z-50 w-auto rounded-md border border-zinc-200 bg-white p-3 text-zinc-900 shadow-md outline-none dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-50',
        'popover_inline' => 'dp-popover w-auto rounded-md border border-zinc-200 bg-white p-3 text-zinc-900 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-50',

        'header' => 'mb-2 flex items-center justify-between gap-1',
        'nav_button' => 'inline-flex size-11 items-center justify-center rounded-md text-zinc-500 transition-colors hover:bg-zinc-100 hover:text-zinc-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-zinc-400/40 disabled:pointer-events-none disabled:opacity-40 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-50',
        'title' => 'flex flex-1 items-center justify-center gap-1',
        'month_select' => 'min-h-11 rounded-md border-0 bg-transparent px-2 text-sm font-medium text-zinc-900 transition-colors hover:bg-zinc-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-zinc-400/40 dark:text-zinc-50 dark:hover:bg-zinc-800',
        'year_select' => 'min-h-11 rounded-md border-0 bg-transparent px-2 text-sm font-medium text-zinc-900 transition-colors hover:bg-zinc-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-zinc-400/40 dark:text-zinc-50 dark:hover:bg-zinc-800',

        'grid' => 'w-full border-collapse',
        'weekday_row' => 'grid grid-cols-7 justify-items-center',
        'weekday' => 'flex h-8 w-11 items-center justify-center text-[0.8rem] font-normal text-zinc-500 dark:text-zinc-400',
        'week' => 'mt-1 grid grid-cols-7 justify-items-center',
        // 44x44 touch target (size-11). day_today uses a ring (no background) so
        // it composes with day_selected's fill instead of fighting it.
        'day' => 'flex size-11 items-center justify-center rounded-md text-sm font-normal text-zinc-700 transition-colors hover:bg-zinc-100 hover:text-zinc-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-zinc-400/50 dark:text-zinc-200 dark:hover:bg-zinc-800 dark:hover:text-zinc-50',
        // The inverted fill/text are applied through the `aria-selected` attribute
        // variant (the day button always carries aria-selected="true|false"). An
        // attribute selector raises specificity above the base `day` colours, so
        // the selected colours win without `!important` and regardless of
        // stylesheet order; the hover variants keep them winning on hover too.
        'day_selected' => 'aria-selected:bg-zinc-900 aria-selected:text-zinc-50 aria-selected:hover:bg-zinc-900 aria-selected:hover:text-zinc-50 dark:aria-selected:bg-zinc-50 dark:aria-selected:text-zinc-900 dark:aria-selected:hover:bg-zinc-50 dark:aria-selected:hover:text-zinc-900',
        'day_today' => 'font-semibold ring-1 ring-inset ring-zinc-300 dark:ring-zinc-700',
        'day_outside' => 'text-zinc-400 opacity-60 dark:text-zinc-600',
        'day_disabled' => 'pointer-events-none text-zinc-300 line-through opacity-50 dark:text-zinc-700',
        'day_weekend' => '',

        // Month mode (year + month only). The 12 month cells reuse the day slots'
        // look; the selected/today/disabled colours are driven by the same
        // attribute variants so they win without `!important` (see day_selected).
        'month_grid' => 'grid grid-cols-3 gap-1',
        'month_cell' => 'flex h-11 items-center justify-center rounded-md text-sm font-normal text-zinc-700 transition-colors hover:bg-zinc-100 hover:text-zinc-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-zinc-400/50 dark:text-zinc-200 dark:hover:bg-zinc-800 dark:hover:text-zinc-50',
        'month_cell_selected' => 'aria-selected:bg-zinc-900 aria-selected:text-zinc-50 aria-selected:hover:bg-zinc-900 aria-selected:hover:text-zinc-50 dark:aria-selected:bg-zinc-50 dark:aria-selected:text-zinc-900 dark:aria-selected:hover:bg-zinc-50 dark:aria-selected:hover:text-zinc-900',
        'month_cell_today' => 'font-semibold ring-1 ring-inset ring-zinc-300 dark:ring-zinc-700',
        'month_cell_disabled' => 'pointer-events-none text-zinc-300 line-through opacity-50 dark:text-zinc-700',

        'time_list' => 'mt-2 max-h-56 space-y-0.5 overflow-y-auto pr-1',
        'time_option' => 'flex min-h-11 w-full cursor-pointer items-center rounded-md px-3 text-left text-sm text-zinc-700 transition-colors hover:bg-zinc-100 hover:text-zinc-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-zinc-400/50 dark:text-zinc-200 dark:hover:bg-zinc-800 dark:hover:text-zinc-50',
        // The time button carries `data-selected` (aria-selected lives on the
        // parent <li role="option">), so the inverted colours are driven by the
        // data-[selected] attribute variant — same specificity win, no `!`.
        'time_option_selected' => 'data-[selected]:bg-zinc-900 data-[selected]:text-zinc-50 data-[selected]:hover:bg-zinc-900 data-[selected]:hover:text-zinc-50 dark:data-[selected]:bg-zinc-50 dark:data-[selected]:text-zinc-900 dark:data-[selected]:hover:bg-zinc-50 dark:data-[selected]:hover:text-zinc-900',
        'time_option_disabled' => 'pointer-events-none text-zinc-300 line-through opacity-50 dark:text-zinc-700',

        'footer' => 'mt-3 flex items-center justify-between gap-2 border-t border-zinc-100 pt-3 dark:border-zinc-800',
        'footer_button' => 'inline-flex min-h-11 items-center rounded-md px-3 text-sm font-medium text-zinc-900 transition-colors hover:bg-zinc-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-zinc-400/40 dark:text-zinc-50 dark:hover:bg-zinc-800',

        'live_region' => 'sr-only',
    ],

    /*
    |--------------------------------------------------------------------------
    | Themes
    |--------------------------------------------------------------------------
    |
    | Named partial class maps. Keys you list here override the default class
    | map for that slot; unlisted slots fall through to the default. Select a
    | theme with `default_theme` above or the `theme` prop.
    |
    */

    'themes' => [

        'minimal' => [
            'input' => 'h-11 w-full border-0 border-b border-zinc-300 bg-transparent pl-1 pr-20 text-sm text-zinc-900 transition-colors focus-visible:border-zinc-900 focus-visible:outline-none focus-visible:ring-0 dark:border-zinc-700 dark:text-zinc-50 dark:focus-visible:border-zinc-100',
            'popover' => 'dp-popover z-50 mt-2 w-auto rounded-none border border-zinc-200 bg-white p-3 shadow-xs dark:border-zinc-800 dark:bg-zinc-950',
            'day_selected' => 'aria-selected:bg-zinc-900 aria-selected:text-zinc-50 aria-selected:hover:bg-zinc-900 aria-selected:hover:text-zinc-50 dark:aria-selected:bg-zinc-50 dark:aria-selected:text-zinc-900 dark:aria-selected:hover:bg-zinc-50 dark:aria-selected:hover:text-zinc-900',
            'day_today' => 'font-semibold underline',
        ],

    ],

];
