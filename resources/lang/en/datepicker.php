<?php

declare(strict_types=1);

return [

    'months' => [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December',
    ],

    'months_short' => [
        'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
    ],

    // Index 0 = Sunday .. 6 = Saturday
    'weekdays' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
    'weekdays_short' => ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
    'weekdays_min' => ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],

    'labels' => [
        'today' => 'Today',
        'clear' => 'Clear',
        'close' => 'Close',
        'previousMonth' => 'Previous month',
        'nextMonth' => 'Next month',
        'previousYear' => 'Previous year',
        'nextYear' => 'Next year',
        'chooseDate' => 'Choose date',
        'chooseTime' => 'Choose time',
        'selectedDate' => 'Selected date',
        'monthSelect' => 'Select month',
        'yearSelect' => 'Select year',
        'openCalendar' => 'Open calendar',
        'am_upper' => 'AM',
        'pm_upper' => 'PM',
        'am_lower' => 'am',
        'pm_lower' => 'pm',
    ],

    'validation' => [
        'format' => 'The :attribute is not a valid date.',
        'not_allowed' => 'The :attribute is not an allowed date.',
    ],

];
