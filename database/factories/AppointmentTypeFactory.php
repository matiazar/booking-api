<?php

namespace Database\Factories;

use App\Models\AppointmentType;
use App\Models\Professional;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AppointmentType>
 */
class AppointmentTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'professional_id' => Professional::factory(),
            'name' => fake()->randomElement(['First visit', 'Follow-up', 'Consultation']),
            'duration_minutes' => 30,
            'price_cents' => fake()->numberBetween(10, 50) * 1000,
        ];
    }

    public function lasting(int $minutes): static
    {
        return $this->state(['duration_minutes' => $minutes]);
    }
}
