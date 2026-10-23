<?php

use App\Models\AppointmentType;
use App\Models\Professional;

it('lists professionals with their appointment types', function () {
    $professional = Professional::factory()->create(['name' => 'Dr. Laura Gómez']);
    AppointmentType::factory()->for($professional)->count(2)->create();

    $this->getJson('/api/v1/professionals')
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Dr. Laura Gómez')
        ->assertJsonCount(2, 'data.0.appointment_types')
        ->assertJsonStructure(['data', 'links', 'meta']);
});
