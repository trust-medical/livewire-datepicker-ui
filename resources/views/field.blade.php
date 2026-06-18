@php
    /** @var \Illuminate\View\ComponentAttributeBag $attributes */
    $dp = $picker($attributes);
    /** @var \TrustMedical\LivewireDatepickerUi\Application\DTO\PickerConfig $config */
    $config = $dp['config'];
    $classes = $config->classes;
    $labels = $config->locale->labels;
    $fieldId = $dp['fieldId'];
    $inputId = $fieldId.'-input';
    $panelId = $fieldId.'-panel';
    $isMonth = $config->mode->value === 'month';
    $hasCalendar = $config->mode->value !== 'time';
    $hasTime = $config->mode->hasTime();
    // The consumer's `class` styles the (otherwise unstyled) input, so merge it
    // onto the input slot here and strip it from the root attribute bag below.
    $inputClass = trim(($classes['input'] ?? '').' '.($attributes->get('class') ?? ''));
@endphp

<div
    {{ $attributes->whereDoesntStartWith('wire:model')->except('class')->class([$classes['root'] ?? '']) }}
    wire:ignore
    data-datepicker
    data-mode="{{ $config->mode }}"
    x-data="datepicker(@js($config->toClientArray()))"
    x-on:keydown.escape.stop="close()"
    @unless ($config->inline)
        {{-- Close on clicks outside the whole widget. Scoping this to the root
             (not the panel) means the click that opens the picker — on the input
             or trigger, both inside the root — is never treated as "outside",
             which is what caused the first click to open then immediately close. --}}
        x-on:click.outside="isOpen && close()"
    @endunless
>
    @unless ($config->inline)
        <div class="{{ $classes['input_wrapper'] ?? '' }}">
            <input
                id="{{ $inputId }}"
                type="text"
                role="combobox"
                aria-haspopup="dialog"
                aria-autocomplete="none"
                aria-controls="{{ $panelId }}"
                :aria-expanded="isOpen ? 'true' : 'false'"
                aria-label="{{ $config->ariaLabel ?? $labels['chooseDate'] ?? '' }}"
                autocomplete="off"
                x-ref="input"
                x-model="display"
                x-on:click="open()"
                x-on:input.debounce.{{ $config->debounceMs }}ms="onType()"
                x-on:keydown="onInputKeydown($event)"
                :data-invalid="status === 'invalid' ? true : null"
                :data-pending="status === 'pending' ? true : null"
                placeholder="{{ $config->placeholder }}"
                value="{{ $dp['display'] }}"
                @disabled($config->disabled)
                @readonly($config->readonly)
                @required($config->required)
                class="{{ $inputClass }}"
            >

            @if ($config->clearable)
                <button
                    type="button"
                    x-show="hasValue && !{{ $config->disabled ? 'true' : 'false' }}"
                    x-cloak
                    x-on:click="clear()"
                    aria-label="{{ $labels['clear'] ?? 'Clear' }}"
                    tabindex="-1"
                    class="{{ $classes['clear'] ?? '' }}"
                >&times;</button>
            @endif

            <button
                type="button"
                x-on:click="toggle()"
                aria-label="{{ $labels['openCalendar'] ?? 'Open calendar' }}"
                tabindex="-1"
                @disabled($config->disabled)
                class="{{ $classes['trigger'] ?? '' }}"
            >
                <svg viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5" aria-hidden="true">
                    <path fill-rule="evenodd" d="M6 2a1 1 0 0 1 1 1v1h6V3a1 1 0 1 1 2 0v1h1a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h1V3a1 1 0 0 1 1-1Zm10 6H4v8h12V8Z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    @endunless

    {{-- Hidden field so the value is submitted with a plain (non-Livewire) form. --}}
    <input type="hidden" name="{{ $config->name }}" x-ref="hidden" :value="value ?? ''" value="{{ $config->value }}">

    <div
        id="{{ $panelId }}"
        x-ref="panel"
        role="dialog"
        aria-modal="false"
        aria-label="{{ $config->ariaLabel ?? $labels['chooseDate'] ?? '' }}"
        @unless ($config->inline)
            x-show="isOpen"
            x-cloak
            x-transition.opacity
            class="{{ $classes['popover'] ?? '' }}"
        @else
            class="{{ $classes['popover_inline'] ?? '' }}"
        @endunless
    >
        @if ($hasCalendar && $isMonth)
            {{-- Month mode: year navigation + a 12-month grid (no day grid). --}}
            <div class="{{ $classes['header'] ?? '' }}">
                <button type="button" x-on:click="previousYear()" aria-label="{{ $labels['previousYear'] ?? '' }}" class="{{ $classes['nav_button'] ?? '' }}">
                    <span aria-hidden="true">&lsaquo;</span>
                </button>

                <div class="{{ $classes['title'] ?? '' }}">
                    <label class="sr-only" for="{{ $fieldId }}-year">{{ $labels['yearSelect'] ?? '' }}</label>
                    <select id="{{ $fieldId }}-year" :value="viewYear" x-on:change="viewYear = parseInt($event.target.value, 10)" class="{{ $classes['year_select'] ?? '' }}">
                        @foreach ($dp['years'] as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="button" x-on:click="nextYear()" aria-label="{{ $labels['nextYear'] ?? '' }}" class="{{ $classes['nav_button'] ?? '' }}">
                    <span aria-hidden="true">&rsaquo;</span>
                </button>
            </div>

            <div role="grid" aria-labelledby="{{ $fieldId }}-year" x-ref="monthGrid" x-on:keydown="onMonthGridKeydown($event)" class="{{ $classes['month_grid'] ?? '' }}">
                <template x-for="cell in monthCells" :key="cell.month">
                    <button
                        type="button"
                        role="gridcell"
                        :data-month="cell.month"
                        :tabindex="cell.isFocused ? 0 : -1"
                        :aria-selected="cell.isSelected ? 'true' : 'false'"
                        :aria-disabled="cell.isDisabled ? 'true' : 'false'"
                        :aria-current="cell.isToday ? 'date' : null"
                        :data-selected="cell.isSelected ? true : null"
                        :data-today="cell.isToday ? true : null"
                        :data-disabled="cell.isDisabled ? true : null"
                        x-on:click="selectMonth(cell.year, cell.month)"
                        :class="monthCellClass(cell)"
                        x-text="cell.label"
                    ></button>
                </template>
            </div>
        @elseif ($hasCalendar)
            <div class="{{ $classes['header'] ?? '' }}">
                <button type="button" x-on:click="previousMonth()" aria-label="{{ $labels['previousMonth'] ?? '' }}" class="{{ $classes['nav_button'] ?? '' }}">
                    <span aria-hidden="true">&lsaquo;</span>
                </button>

                <div class="{{ $classes['title'] ?? '' }}">
                    <label class="sr-only" for="{{ $fieldId }}-month">{{ $labels['monthSelect'] ?? '' }}</label>
                    <select id="{{ $fieldId }}-month" :value="viewMonth" x-on:change="viewMonth = parseInt($event.target.value, 10)" class="{{ $classes['month_select'] ?? '' }}">
                        @foreach ($config->locale->months as $index => $monthName)
                            <option value="{{ $index + 1 }}">{{ $monthName }}</option>
                        @endforeach
                    </select>

                    <label class="sr-only" for="{{ $fieldId }}-year">{{ $labels['yearSelect'] ?? '' }}</label>
                    <select id="{{ $fieldId }}-year" :value="viewYear" x-on:change="viewYear = parseInt($event.target.value, 10)" class="{{ $classes['year_select'] ?? '' }}">
                        @foreach ($dp['years'] as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="button" x-on:click="nextMonth()" aria-label="{{ $labels['nextMonth'] ?? '' }}" class="{{ $classes['nav_button'] ?? '' }}">
                    <span aria-hidden="true">&rsaquo;</span>
                </button>
            </div>

            <div class="{{ $classes['weekday_row'] ?? '' }}" aria-hidden="true">
                @foreach ($dp['weekdays'] as $weekday)
                    <div class="{{ $classes['weekday'] ?? '' }}">{{ $weekday }}</div>
                @endforeach
            </div>

            <div role="grid" aria-labelledby="{{ $fieldId }}-month" x-ref="grid" x-on:keydown="onGridKeydown($event)">
                <template x-for="(week, weekIndex) in weeks" :key="weekIndex">
                    <div role="row" class="{{ $classes['week'] ?? '' }}">
                        <template x-for="day in week" :key="day.date">
                            <button
                                type="button"
                                role="gridcell"
                                :data-date="day.date"
                                :tabindex="day.isFocused ? 0 : -1"
                                :aria-selected="day.isSelected ? 'true' : 'false'"
                                :aria-disabled="day.isDisabled ? 'true' : 'false'"
                                :aria-current="day.isToday ? 'date' : null"
                                :data-selected="day.isSelected ? true : null"
                                :data-today="day.isToday ? true : null"
                                :data-outside-month="day.isOutsideMonth ? true : null"
                                :data-disabled="day.isDisabled ? true : null"
                                :data-weekend="day.isWeekend ? true : null"
                                x-on:click="selectDay(day)"
                                :class="dayClass(day)"
                                x-text="day.day"
                            ></button>
                        </template>
                    </div>
                </template>
            </div>
        @endif

        @if ($hasTime)
            <ul role="listbox" aria-label="{{ $labels['chooseTime'] ?? '' }}" x-ref="timeList" class="{{ $classes['time_list'] ?? '' }}">
                <template x-for="option in timeOptions" :key="option.value">
                    <li role="option" :aria-selected="option.isSelected ? 'true' : 'false'">
                        <button
                            type="button"
                            :data-selected="option.isSelected ? true : null"
                            :data-disabled="option.isDisabled ? true : null"
                            :aria-disabled="option.isDisabled ? 'true' : 'false'"
                            x-on:click="selectTime(option)"
                            :class="timeClass(option)"
                            x-text="option.label"
                        ></button>
                    </li>
                </template>
            </ul>
        @endif

        @if ($config->todayButton || $config->clearable || ($config->closeButton && ! $config->inline))
            <div class="{{ $classes['footer'] ?? '' }}">
                <div class="flex gap-2">
                    @if ($config->todayButton)
                        <button type="button" x-on:click="goToday()" class="{{ $classes['footer_button'] ?? '' }}">{{ $labels['today'] ?? 'Today' }}</button>
                    @endif
                    @if ($config->clearable)
                        <button type="button" x-on:click="clear()" class="{{ $classes['footer_button'] ?? '' }}">{{ $labels['clear'] ?? 'Clear' }}</button>
                    @endif
                </div>
                @if ($config->closeButton && ! $config->inline)
                    <button type="button" x-on:click="close()" class="{{ $classes['footer_button'] ?? '' }}">{{ $labels['close'] ?? 'Close' }}</button>
                @endif
            </div>
        @endif

        <div x-ref="live" class="{{ $classes['live_region'] ?? 'sr-only' }}" role="status" aria-live="polite"></div>
    </div>
</div>
