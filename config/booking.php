<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Booking rules
    |--------------------------------------------------------------------------
    |
    | Business rules live in config so they can change per environment
    | without touching the services that enforce them.
    |
    */

    // Step between candidate slots inside a working-hours range.
    'slot_interval_minutes' => (int) env('BOOKING_SLOT_INTERVAL', 15),

    // Customers cannot book a slot that starts sooner than this.
    'min_lead_minutes' => (int) env('BOOKING_MIN_LEAD_MINUTES', 60),

    // How far into the future the agenda is open.
    'max_days_ahead' => (int) env('BOOKING_MAX_DAYS_AHEAD', 60),

    // Appointments can only be cancelled with at least this much notice.
    'cancellation_notice_hours' => (int) env('BOOKING_CANCELLATION_NOTICE_HOURS', 24),

    /*
    |--------------------------------------------------------------------------
    | Error reporting
    |--------------------------------------------------------------------------
    |
    | Unexpected exceptions are emailed to this address (throttled per
    | exception signature). Off by default in local and testing.
    |
    */

    'report_errors' => (bool) env('BOOKING_REPORT_ERRORS', ! in_array(env('APP_ENV'), ['local', 'testing'], true)),

    'admin_email' => env('BOOKING_ADMIN_EMAIL'),

    'error_mail_throttle_minutes' => (int) env('BOOKING_ERROR_MAIL_THROTTLE', 10),

];
