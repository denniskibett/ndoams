<?php

return [
    // One paid "day" of work = this many entries.
    'entries_per_day' => (int) env('EARNINGS_ENTRIES_PER_DAY', 50),

    // Amount paid for each completed day.
    'rate_per_day' => (int) env('EARNINGS_RATE_PER_DAY', 1000),

    'currency' => env('EARNINGS_CURRENCY', 'KES'),

    // Entries with these system statuses do not count towards earnings.
    'excluded_statuses' => ['Cancelled'],

    // Roles whose people earn from entries.
    'earning_roles' => ['data_clerk', 'marriage_teller'],
];
