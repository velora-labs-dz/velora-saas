<?php

use App\Enums\OrganizationRole;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Organization;
use App\Models\Service;
use App\Models\User;

test('a user cannot see another organization\'s appointments in their list, even as a member of both', function () {
    $user = User::factory()->create();
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();
    addOrganizationMember($orgA, $user, OrganizationRole::Owner);
    addOrganizationMember($orgB, $user, OrganizationRole::Owner);

    $sameDay = now()->addDay()->setTime(9, 0);
    Appointment::factory()->create(['organization_id' => $orgA->id, 'starts_at' => $sameDay, 'ends_at' => $sameDay->copy()->addHour()]);
    Appointment::factory()->create(['organization_id' => $orgB->id, 'starts_at' => $sameDay, 'ends_at' => $sameDay->copy()->addHour()]);

    switchInto($this, $user, $orgA);

    $response = $this->get('/appointments?date='.$sameDay->toDateString());

    $response->assertInertia(fn ($page) => $page
        ->where('appointments', fn ($appointments) => count($appointments) === 1)
    );
});

test('an appointment id belonging to a different organization is not reachable by guessing the id', function () {
    $ownerA = User::factory()->create();
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();
    addOrganizationMember($orgA, $ownerA, OrganizationRole::Owner);
    $appointmentInB = Appointment::factory()->create(['organization_id' => $orgB->id]);

    switchInto($this, $ownerA, $orgA);

    $this->get("/appointments/{$appointmentInB->id}/edit")->assertNotFound();
    $this->patch("/appointments/{$appointmentInB->id}/cancel", [])->assertNotFound();

    expect($appointmentInB->fresh()->status->value)->toBe('scheduled');
});

test('a client, service, or staff member belonging to a different organization cannot be booked, even by guessing the id', function () {
    $ownerA = User::factory()->create();
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();
    addOrganizationMember($orgA, $ownerA, OrganizationRole::Owner);
    $staffUserA = User::factory()->create();
    $staffInA = addOrganizationMember($orgA, $staffUserA, OrganizationRole::Staff);
    $clientInA = Client::factory()->create(['organization_id' => $orgA->id]);
    $serviceInA = Service::factory()->create(['organization_id' => $orgA->id]);

    $clientInB = Client::factory()->create(['organization_id' => $orgB->id]);
    $serviceInB = Service::factory()->create(['organization_id' => $orgB->id]);
    $staffUserB = User::factory()->create();
    $staffInB = addOrganizationMember($orgB, $staffUserB, OrganizationRole::Staff);

    switchInto($this, $ownerA, $orgA);

    $base = [
        'starts_at' => now()->addDay()->setTime(10, 0)->toDateTimeString(),
        'ends_at' => now()->addDay()->setTime(11, 0)->toDateTimeString(),
    ];

    $this->post('/appointments', $base + [
        'client_id' => $clientInB->id,
        'service_id' => $serviceInA->id,
        'employee_id' => $staffInA->id,
    ])->assertSessionHasErrors('client_id');

    $this->post('/appointments', $base + [
        'client_id' => $clientInA->id,
        'service_id' => $serviceInB->id,
        'employee_id' => $staffInA->id,
    ])->assertSessionHasErrors('service_id');

    $this->post('/appointments', $base + [
        'client_id' => $clientInA->id,
        'service_id' => $serviceInA->id,
        'employee_id' => $staffInB->id,
    ])->assertSessionHasErrors('employee_id');

    $this->assertDatabaseMissing('appointments', ['organization_id' => $orgA->id]);
});
