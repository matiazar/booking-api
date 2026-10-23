<?php

use App\Models\Appointment;
use App\Models\AppointmentType;
use App\Models\Professional;

beforeEach(function () {
    freezeAt('2026-11-02 08:00'); // Monday

    $this->professional = Professional::factory()->withWeekdayHours()->create();
    $this->type = AppointmentType::factory()->for($this->professional)->lasting(30)->create();
});

function availability(Professional $professional, AppointmentType $type, string $date)
{
    return test()->getJson("/api/v1/professionals/{$professional->id}/availability?appointment_type_id={$type->id}&date={$date}");
}

it('lists every slot of a split-shift day', function () {
    $response = availability($this->professional, $this->type, '2026-11-03')->assertOk();

    $slots = collect($response->json('data.slots'))->pluck('starts_at');

    // 09:00-13:00 and 15:00-19:00 local, 30 min sessions every 15 min => 15 + 15.
    expect($slots)->toHaveCount(30)
        ->and($slots->first())->toBe('2026-11-03T12:00:00Z')  // 09:00 local
        ->and($slots->last())->toBe('2026-11-03T21:30:00Z')   // 18:30 local
        ->and($slots)->not->toContain('2026-11-03T16:00:00Z'); // 13:00 local, lunch break

    $response->assertJsonPath('data.timezone', 'America/Argentina/Buenos_Aires');
});

it('hides slots that overlap an existing appointment', function () {
    Appointment::factory()->scheduled($this->type, local('2026-11-03 10:00'))->create();

    $slots = collect(availability($this->professional, $this->type, '2026-11-03')->json('data.slots'))
        ->pluck('starts_at');

    expect($slots)->toHaveCount(27)
        ->not->toContain('2026-11-03T12:45:00Z') // 09:45 would run into 10:00
        ->not->toContain('2026-11-03T13:00:00Z')
        ->not->toContain('2026-11-03T13:15:00Z')
        ->toContain('2026-11-03T13:30:00Z');     // 10:30, free again
});

it('ignores cancelled appointments', function () {
    Appointment::factory()->scheduled($this->type, local('2026-11-03 10:00'))->cancelled()->create();

    expect(availability($this->professional, $this->type, '2026-11-03')->json('data.slots'))->toHaveCount(30);
});

it('respects the minimum lead time for today', function () {
    freezeAt('2026-11-02 10:20');

    $first = availability($this->professional, $this->type, '2026-11-02')->json('data.slots.0.starts_at');

    expect($first)->toBe('2026-11-02T14:30:00Z'); // 11:30 local: first slot >= now + 60 min
});

it('returns no slots on days off or outside the booking window', function (string $date) {
    expect(availability($this->professional, $this->type, $date)->assertOk()->json('data.slots'))->toBe([]);
})->with([
    'saturday' => '2026-11-07',
    'past day' => '2026-11-01',
    'beyond max days ahead' => '2027-01-15',
]);

it('rejects an appointment type from another professional', function () {
    $foreignType = AppointmentType::factory()->create();

    availability($this->professional, $foreignType, '2026-11-03')
        ->assertUnprocessable()
        ->assertJsonValidationErrors('appointment_type_id');
});

it('validates the date format', function () {
    availability($this->professional, $this->type, '03/11/2026')
        ->assertUnprocessable()
        ->assertJsonValidationErrors('date');
});
