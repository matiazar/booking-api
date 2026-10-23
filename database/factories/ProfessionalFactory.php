<?php

namespace Database\Factories;

use App\Models\Professional;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Professional>
 */
class ProfessionalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'timezone' => 'America/Argentina/Buenos_Aires',
        ];
    }

    /**
     * Open Monday to Friday with a split shift: 09:00-13:00 and 15:00-19:00.
     */
    public function withWeekdayHours(): static
    {
        return $this->afterCreating(function (Professional $professional) {
            foreach (range(1, 5) as $weekday) {
                $professional->workingHours()->createMany([
                    ['weekday' => $weekday, 'opens_at' => '09:00', 'closes_at' => '13:00'],
                    ['weekday' => $weekday, 'opens_at' => '15:00', 'closes_at' => '19:00'],
                ]);
            }
        });
    }
}
