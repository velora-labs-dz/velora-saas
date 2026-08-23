<?php

namespace Database\Factories;

use App\Enums\DurationUnit;
use App\Models\MembershipPlan;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MembershipPlan>
 */
class MembershipPlanFactory extends Factory
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
            'name' => fake()->randomElement(['Monthly Unlimited', 'Quarterly', 'Annual', '10-Session Pack']),
            'description' => null,
            'duration_value' => 1,
            'duration_unit' => DurationUnit::Months,
            'price' => fake()->randomFloat(2, 2000, 20000),
            'currency' => 'DZD',
            'sessions_limit' => null,
            'visits_per_period' => null,
            'freeze_allowed' => true,
            'freeze_limit' => null,
            'active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['active' => false]);
    }

    public function freezeNotAllowed(): static
    {
        return $this->state(fn () => ['freeze_allowed' => false]);
    }
}
