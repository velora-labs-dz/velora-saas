<?php

namespace Database\Factories;

use App\Enums\AppointmentStatus;
use App\Enums\OrganizationRole;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\OrganizationMember;
use App\Models\Organization;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = now()->addDay()->setTime(10, 0);

        return [
            'organization_id' => Organization::factory(),
            'client_id' => Client::factory(),
            'service_id' => Service::factory(),
            // OrganizationMember has no factory of its own (see
            // tests/Pest.php's addOrganizationMember() — it's deliberately
            // not mass-assignable), so build one directly here rather than
            // OrganizationMember::factory(), which doesn't exist. Tests
            // that care about the employee's organization or role should
            // override this with a real staff member via
            // addOrganizationMember() instead of relying on the default.
            'employee_id' => function () {
                $member = new OrganizationMember();
                $member->organization_id = Organization::factory()->create()->id;
                $member->user_id = User::factory()->create()->id;
                $member->role = OrganizationRole::Staff;
                $member->is_active = true;
                $member->joined_at = now();
                $member->save();

                return $member->id;
            },
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addMinutes(60),
            'status' => AppointmentStatus::Scheduled,
            'booking_channel' => 'dashboard',
            'notes' => null,
            'cancellation_reason' => null,
            'created_by' => null,
        ];
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status' => AppointmentStatus::Cancelled,
            'cancellation_reason' => 'Client request',
        ]);
    }
}
