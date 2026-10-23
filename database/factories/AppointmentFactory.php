<?php

namespace Database\Factories;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentType;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        $startsAt = CarbonImmutable::now()->addDays(3)->setTime(12, 0)->utc();

        return [
            'appointment_type_id' => AppointmentType::factory(),
            'professional_id' => fn (array $attributes) => AppointmentType::query()
                ->findOrFail($attributes['appointment_type_id'])->professional_id,
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->addMinutes(30),
            'status' => AppointmentStatus::Confirmed,
        ];
    }

    public function scheduled(AppointmentType $type, CarbonImmutable $startsAt): static
    {
        return $this->state([
            'appointment_type_id' => $type->id,
            'professional_id' => $type->professional_id,
            'starts_at' => $startsAt->utc(),
            'ends_at' => $startsAt->utc()->addMinutes($type->duration_minutes),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state([
            'status' => AppointmentStatus::Cancelled,
            'cancelled_at' => CarbonImmutable::now(),
        ]);
    }
}
