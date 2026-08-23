<?php

namespace Database\Factories;

use App\Enums\MembershipStatus;
use App\Models\Client;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Membership>
 */
class MembershipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = now()->startOfDay();

        return [
            'organization_id' => Organization::factory(),
            'client_id' => Client::factory(),
            'membership_plan_id' => MembershipPlan::factory(),
            'status' => MembershipStatus::Draft,
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addMonth(),
            'price' => fake()->randomFloat(2, 2000, 20000),
            'currency' => 'DZD',
            'paid_amount' => 0,
            'remaining_amount' => 0,
            'notes' => null,
            'activated_at' => null,
            'frozen_at' => null,
            'cancelled_at' => null,
            'cancellation_reason' => null,
            'created_by' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'status' => MembershipStatus::Active,
            'activated_at' => now(),
        ]);
    }

    public function frozen(): static
    {
        return $this->state(fn () => [
            'status' => MembershipStatus::Frozen,
            'activated_at' => now()->subDays(5),
            'frozen_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status' => MembershipStatus::Cancelled,
            'activated_at' => now()->subDays(5),
            'cancelled_at' => now(),
            'cancellation_reason' => 'Client request',
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'status' => MembershipStatus::Expired,
            'activated_at' => now()->subMonths(2),
            'starts_at' => now()->subMonths(2),
            'ends_at' => now()->subDay(),
        ]);
    }
}
