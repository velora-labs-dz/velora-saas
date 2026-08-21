<?php

namespace Database\Factories;

use App\Enums\OrganizationStatus;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Organization>
 */
class OrganizationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 99999),
            'legal_name' => null,
            'timezone' => 'Africa/Algiers',
            'locale' => 'fr',
            'currency' => 'DZD',
            'status' => OrganizationStatus::Active,
            'contact_email' => fake()->unique()->companyEmail(),
            'contact_phone' => null,
            'address_line_1' => null,
            'address_line_2' => null,
            'city' => null,
            'wilaya' => null,
            'postal_code' => null,
            'country_code' => 'DZ',
            'created_by' => User::factory(),
        ];
    }
}
