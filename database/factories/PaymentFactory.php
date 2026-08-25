<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Client;
use App\Models\Organization;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
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
            'membership_id' => null,
            'amount' => fake()->randomFloat(2, 500, 15000),
            'currency' => 'DZD',
            'method' => PaymentMethod::Cash,
            'status' => PaymentStatus::Recorded,
            'reference' => null,
            'paid_at' => now(),
            'refunded_amount' => 0,
            'voided_at' => null,
            'void_reason' => null,
            'notes' => null,
            'recorded_by' => null,
        ];
    }

    public function voided(): static
    {
        return $this->state(fn () => [
            'status' => PaymentStatus::Voided,
            'voided_at' => now(),
            'void_reason' => 'Data entry mistake',
        ]);
    }

    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Refunded,
            'refunded_amount' => $attributes['amount'] ?? 1000,
        ]);
    }
}
