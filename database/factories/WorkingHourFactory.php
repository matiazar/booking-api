<?php

namespace Database\Factories;

use App\Models\Professional;
use App\Models\WorkingHour;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkingHour>
 */
class WorkingHourFactory extends Factory
{
    public function definition(): array
    {
        return [
            'professional_id' => Professional::factory(),
            'weekday' => 1,
            'opens_at' => '09:00',
            'closes_at' => '13:00',
        ];
    }
}
