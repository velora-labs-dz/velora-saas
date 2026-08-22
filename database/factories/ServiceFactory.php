<?php

namespace Database\Factories;

use App\Enums\ServiceStatus;
use App\Models\Organization;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
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
            'name' => fake()->randomElement(['Haircut', 'Massage', 'Yoga Class', 'Personal Training', 'Manicure']),
            'description' => null,
            'duration_minutes' => fake()->randomElement([30, 45, 60, 90]),
            'price' => fake()->randomFloat(2, 500, 5000),
            'currency' => 'DZD',
            'capacity' => 1,
            'status' => ServiceStatus::Active,
            'created_by' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => ServiceStatus::Inactive]);
    }
}
