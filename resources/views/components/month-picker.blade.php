{{-- <x-month-picker> — forwards to the month-mode picker (year + month only). --}}
<x-datepicker mode="month" {{ $attributes }}>{{ $slot ?? '' }}</x-datepicker>
