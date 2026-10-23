<?php

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentType;
use App\Models\Professional;
use App\Notifications\AppointmentCancelledNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    freezeAt('2026-11-02 08:00');
    Notification::fake();

    $this->type = AppointmentType::factory()
        ->for(Professional::factory()->withWeekdayHours())
        ->lasting(30)
        ->create();
});

it('cancels with enough notice and frees the slot', function () {
    $appointment = Appointment::factory()->scheduled($this->type, local('2026-11-04 10:00'))->create();

    $this->postJson("/api/v1/appointments/{$appointment->id}/cancel", ['reason' => 'Travelling'])
        ->assertOk()
        ->assertJsonPath('data.status', 'cancelled')
        ->assertJsonPath('data.cancellation_reason', 'Travelling')
        ->assertJsonPath('data.cancelled_at', '2026-11-02T11:00:00Z');

    expect($appointment->refresh()->status)->toBe(AppointmentStatus::Cancelled);

    Notification::assertSentOnDemand(AppointmentCancelledNotification::class);
});

it('refuses to cancel inside the notice window', function () {
    $appointment = Appointment::factory()->scheduled($this->type, local('2026-11-03 07:00'))->create();

    $this->postJson("/api/v1/appointments/{$appointment->id}/cancel")
        ->assertUnprocessable()
        ->assertJsonPath('error', 'cancellation_window_closed');

    expect($appointment->refresh()->status)->toBe(AppointmentStatus::Confirmed);
});

it('refuses to cancel twice', function () {
    $appointment = Appointment::factory()->scheduled($this->type, local('2026-11-04 10:00'))->cancelled()->create();

    $this->postJson("/api/v1/appointments/{$appointment->id}/cancel")
        ->assertUnprocessable()
        ->assertJsonPath('error', 'already_cancelled');

    Notification::assertNothingSent();
});
