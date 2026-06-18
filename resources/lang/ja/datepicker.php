<?php

declare(strict_types=1);

return [

    'months' => [
        '1月', '2月', '3月', '4月', '5月', '6月',
        '7月', '8月', '9月', '10月', '11月', '12月',
    ],

    'months_short' => [
        '1月', '2月', '3月', '4月', '5月', '6月',
        '7月', '8月', '9月', '10月', '11月', '12月',
    ],

    // Index 0 = Sunday .. 6 = Saturday
    'weekdays' => ['日曜日', '月曜日', '火曜日', '水曜日', '木曜日', '金曜日', '土曜日'],
    'weekdays_short' => ['日', '月', '火', '水', '木', '金', '土'],
    'weekdays_min' => ['日', '月', '火', '水', '木', '金', '土'],

    'labels' => [
        'today' => '今日',
        'clear' => 'クリア',
        'close' => '閉じる',
        'previousMonth' => '前の月',
        'nextMonth' => '次の月',
        'previousYear' => '前の年',
        'nextYear' => '次の年',
        'chooseDate' => '日付を選択',
        'chooseTime' => '時刻を選択',
        'selectedDate' => '選択中の日付',
        'monthSelect' => '月を選択',
        'yearSelect' => '年を選択',
        'openCalendar' => 'カレンダーを開く',
        'am_upper' => '午前',
        'pm_upper' => '午後',
        'am_lower' => '午前',
        'pm_lower' => '午後',
    ],

    'validation' => [
        'format' => ':attributeは正しい日付ではありません。',
        'not_allowed' => ':attributeは選択できない日付です。',
    ],

];
