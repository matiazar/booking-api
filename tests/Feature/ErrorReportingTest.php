<?php

use App\Models\Appointment;
use App\Models\AppointmentType;
use App\Models\Professional;
use App\Notifications\ExceptionOccurredNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Notification::fake();
    config(['booking.report_errors' => true, 'booking.admin_email' => 'admin@example.com']);

    Route::get('/api/_test/boom', fn () => throw new RuntimeException('Something broke'));
});

it('emails the admin when an unexpected exception happens', function () {
    $this->getJson('/api/_test/boom')->assertServerError();

    Notification::assertSentOnDemand(
        ExceptionOccurredNotification::class,
        fn (ExceptionOccurredNotification $notification, array $channels, $notifiable) => $notifiable->routes['mail'] === 'admin@example.com'
            && $notification->exceptionClass === RuntimeException::class
            && $notification->exceptionMessage === 'Something broke',
    );
});

it('throttles repeated errors with the same signature', function () {
    $this->getJson('/api/_test/boom');
    $this->getJson('/api/_test/boom');
    $this->getJson('/api/_test/boom');

    Notification::assertSentOnDemandTimes(ExceptionOccurredNotification::class, 1);
});

it('does not report business-rule violations', function () {
    freezeAt('2026-11-02 08:00');
    $type = AppointmentType::factory()->for(Professional::factory()->withWeekdayHours())->create();
    Appointment::factory()->scheduled($type, local('2026-11-03 09:00'))->create();

    $this->postJson('/api/v1/appointments', [
        'appointment_type_id' => $type->id,
        'starts_at' => '2026-11-03T09:00:00-03:00',
        'customer_name' => 'Ana',
        'customer_email' => 'ana@example.com',
    ])->assertConflict();

    Notification::assertNothingSent();
});

it('stays silent when no admin address is configured', function () {
    config(['booking.admin_email' => null]);

    $this->getJson('/api/_test/boom')->assertServerError();

    Notification::assertNothingSent();
});

it('is disabled by default outside production-like environments', function () {
    config(['booking.report_errors' => false]);

    $this->getJson('/api/_test/boom')->assertServerError();

    Notification::assertNothingSent();
});
