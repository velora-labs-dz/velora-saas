<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Client;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'client_id' => Client::factory(),
            'check_in_at' => now()->subHour(),
            'check_out_at' => null,
            'source' => 'manual',
            'notes' => null,
            'recorded_by' => null,
        ];
    }

    public function closed(): static
    {
        return $this->state(fn () => [
            'check_out_at' => now(),
        ]);
    }
}
