<?php

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentType;
use App\Models\Professional;
use App\Notifications\AppointmentConfirmedNotification;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    freezeAt('2026-11-02 08:00');
    Notification::fake();

    $this->type = AppointmentType::factory()
        ->for(Professional::factory()->withWeekdayHours())
        ->lasting(30)
        ->create(['name' => 'Check-up']);
});

function book(array $overrides = [])
{
    return test()->postJson('/api/v1/appointments', array_merge([
        'appointment_type_id' => test()->type->id,
        'starts_at' => '2026-11-03T09:00:00-03:00',
        'customer_name' => 'Ana Pérez',
        'customer_email' => 'Ana@Example.com',
    ], $overrides));
}

it('books a free slot and emails the customer', function () {
    $response = book()->assertCreated()
        ->assertJsonPath('data.status', 'confirmed')
        ->assertJsonPath('data.starts_at', '2026-11-03T12:00:00Z')
        ->assertJsonPath('data.ends_at', '2026-11-03T12:30:00Z')
        ->assertJsonPath('data.customer.email', 'ana@example.com')
        ->assertJsonPath('data.appointment_type.name', 'Check-up');

    $appointment = Appointment::query()->findOrFail($response->json('data.id'));

    expect($appointment->status)->toBe(AppointmentStatus::Confirmed)
        ->and($appointment->starts_at->toIso8601ZuluString())->toBe('2026-11-03T12:00:00Z');

    Notification::assertSentOnDemand(
        AppointmentConfirmedNotification::class,
        fn ($notification, array $channels, AnonymousNotifiable $notifiable) => $notifiable->routes['mail'] === 'ana@example.com',
    );
});

it('accepts any timezone offset and stores UTC', function () {
    book(['starts_at' => '2026-11-03T12:00:00Z'])->assertCreated()
        ->assertJsonPath('data.starts_at', '2026-11-03T12:00:00Z');
});

it('refuses a slot that is already taken', function () {
    Appointment::factory()->scheduled($this->type, local('2026-11-03 09:15'))->create();

    book()->assertConflict()->assertJsonPath('error', 'slot_unavailable');

    Notification::assertNothingSent();
});

it('refuses times that are not a bookable slot', function (string $startsAt) {
    book(['starts_at' => $startsAt])->assertConflict()->assertJsonPath('error', 'slot_unavailable');
})->with([
    'lunch break' => '2026-11-03T13:00:00-03:00',
    'after closing' => '2026-11-03T18:45:00-03:00',
    'off the slot grid' => '2026-11-03T09:10:00-03:00',
    'saturday' => '2026-11-07T10:00:00-03:00',
    'inside the lead time' => '2026-11-02T08:30:00-03:00',
]);

it('validates the payload', function () {
    $this->postJson('/api/v1/appointments', ['customer_email' => 'not-an-email'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['appointment_type_id', 'starts_at', 'customer_name', 'customer_email']);
});

it('rejects past dates at validation level', function () {
    book(['starts_at' => '2026-11-01T09:00:00-03:00'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('starts_at');
});

it('shows an appointment by its public id', function () {
    $appointment = Appointment::factory()->scheduled($this->type, local('2026-11-03 09:00'))->create();

    $this->getJson("/api/v1/appointments/{$appointment->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $appointment->id)
        ->assertJsonPath('data.professional.id', $this->type->professional_id);
});

it('returns 404 for unknown appointments', function () {
    $this->getJson('/api/v1/appointments/01JZZZZZZZZZZZZZZZZZZZZZZZ')->assertNotFound();
});
