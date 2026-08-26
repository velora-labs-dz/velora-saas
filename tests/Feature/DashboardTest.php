<?php

use App\Enums\OrganizationRole;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Organization;
use App\Models\Service;
use App\Models\User;

test('a brand new user with no organization still sees the dashboard, with an empty state', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->where('organization', null)
    );
});

test('the dashboard shows today\'s scheduled appointments for the current organization', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $client = Client::factory()->create(['organization_id' => $organization->id]);
    $service = Service::factory()->create(['organization_id' => $organization->id]);
    $staffUser = User::factory()->create();
    $staffMember = addOrganizationMember($organization, $staffUser, OrganizationRole::Staff);

    Appointment::factory()->create([
        'organization_id' => $organization->id,
        'client_id' => $client->id,
        'service_id' => $service->id,
        'employee_id' => $staffMember->id,
        'starts_at' => now()->setTime(14, 0),
        'ends_at' => now()->setTime(15, 0),
    ]);

    // A cancelled appointment today should not count.
    Appointment::factory()->cancelled()->create([
        'organization_id' => $organization->id,
        'client_id' => $client->id,
        'service_id' => $service->id,
        'employee_id' => $staffMember->id,
        'starts_at' => now()->setTime(16, 0),
        'ends_at' => now()->setTime(17, 0),
    ]);

    // Tomorrow's appointment should not count either.
    Appointment::factory()->create([
        'organization_id' => $organization->id,
        'client_id' => $client->id,
        'service_id' => $service->id,
        'employee_id' => $staffMember->id,
        'starts_at' => now()->addDay()->setTime(10, 0),
        'ends_at' => now()->addDay()->setTime(11, 0),
    ]);

    switchInto($this, $owner, $organization);

    $response = $this->get('/dashboard');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->where('organization.name', $organization->name)
        ->where('todaysAppointments', fn ($appointments) => count($appointments) === 1)
    );
});
