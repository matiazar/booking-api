<?php

namespace Database\Seeders;

use App\Models\Professional;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $dentist = Professional::factory()->withWeekdayHours()->create([
            'name' => 'Dr. Laura Gómez',
            'email' => 'laura@example.com',
        ]);
        $dentist->appointmentTypes()->createMany([
            ['name' => 'First visit', 'duration_minutes' => 45, 'price_cents' => 3500000],
            ['name' => 'Check-up', 'duration_minutes' => 30, 'price_cents' => 2000000],
        ]);

        $physio = Professional::factory()->withWeekdayHours()->create([
            'name' => 'Lic. Martín Ruiz',
            'email' => 'martin@example.com',
        ]);
        $physio->appointmentTypes()->create(
            ['name' => 'Physiotherapy session', 'duration_minutes' => 60, 'price_cents' => 2500000],
        );
    }
}
