<?php

use App\Enums\AppointmentStatus;
use App\Enums\OrganizationRole;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Organization;
use App\Models\Service;
use App\Models\User;

function bookingPayload(Client $client, Service $service, $employeeId, array $overrides = []): array
{
    return array_merge([
        'client_id' => $client->id,
        'service_id' => $service->id,
        'employee_id' => $employeeId,
        'starts_at' => now()->addDay()->setTime(10, 0)->toDateTimeString(),
        'ends_at' => now()->addDay()->setTime(11, 0)->toDateTimeString(),
    ], $overrides);
}

test('an owner can book an appointment for a client with a staff member', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $staffUser = User::factory()->create();
    $staffMember = addOrganizationMember($organization, $staffUser, OrganizationRole::Staff);
    $client = Client::factory()->create(['organization_id' => $organization->id]);
    $service = Service::factory()->create(['organization_id' => $organization->id]);
    switchInto($this, $owner, $organization);

    $response = $this->post('/appointments', bookingPayload($client, $service, $staffMember->id));

    $response->assertRedirect();
    $this->assertDatabaseHas('appointments', [
        'organization_id' => $organization->id,
        'client_id' => $client->id,
        'service_id' => $service->id,
        'employee_id' => $staffMember->id,
        'status' => 'scheduled',
        'created_by' => $owner->id,
    ]);
});

test('booking an appointment requires client, service, staff, and a valid time range', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    switchInto($this, $owner, $organization);

    $response = $this->post('/appointments', []);

    $response->assertSessionHasErrors(['client_id', 'service_id', 'employee_id', 'starts_at', 'ends_at']);
});

test('an end time that is not after the start time is rejected', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $staffUser = User::factory()->create();
    $staffMember = addOrganizationMember($organization, $staffUser, OrganizationRole::Staff);
    $client = Client::factory()->create(['organization_id' => $organization->id]);
    $service = Service::factory()->create(['organization_id' => $organization->id]);
    switchInto($this, $owner, $organization);

    $response = $this->post('/appointments', bookingPayload($client, $service, $staffMember->id, [
        'starts_at' => now()->addDay()->setTime(10, 0)->toDateTimeString(),
        'ends_at' => now()->addDay()->setTime(9, 0)->toDateTimeString(),
    ]));

    $response->assertSessionHasErrors('ends_at');
});

test('only an organization member with the staff role can be assigned as the employee', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    $ownerMember = addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $client = Client::factory()->create(['organization_id' => $organization->id]);
    $service = Service::factory()->create(['organization_id' => $organization->id]);
    switchInto($this, $owner, $organization);

    // The Owner's own membership row exists but is not role=staff, so it
    // must not pass as a valid employee_id.
    $response = $this->post('/appointments', bookingPayload($client, $service, $ownerMember->id));

    $response->assertSessionHasErrors('employee_id');
});

test('a scheduled appointment can be edited but a cancelled one cannot', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $staffUser = User::factory()->create();
    $staffMember = addOrganizationMember($organization, $staffUser, OrganizationRole::Staff);
    $client = Client::factory()->create(['organization_id' => $organization->id]);
    $service = Service::factory()->create(['organization_id' => $organization->id]);
    $scheduled = Appointment::factory()->create([
        'organization_id' => $organization->id,
        'client_id' => $client->id,
        'service_id' => $service->id,
        'employee_id' => $staffMember->id,
    ]);
    $cancelled = Appointment::factory()->cancelled()->create([
        'organization_id' => $organization->id,
        'client_id' => $client->id,
        'service_id' => $service->id,
        'employee_id' => $staffMember->id,
        'starts_at' => now()->addDays(2)->setTime(10, 0),
        'ends_at' => now()->addDays(2)->setTime(11, 0),
    ]);
    switchInto($this, $owner, $organization);

    $this->patch("/appointments/{$scheduled->id}", bookingPayload($client, $service, $staffMember->id, [
        'notes' => 'Rescheduled note',
    ]))->assertRedirect();
    expect($scheduled->fresh()->notes)->toBe('Rescheduled note');

    $this->patch("/appointments/{$cancelled->id}", bookingPayload($client, $service, $staffMember->id, [
        'starts_at' => now()->addDays(3)->setTime(10, 0)->toDateTimeString(),
        'ends_at' => now()->addDays(3)->setTime(11, 0)->toDateTimeString(),
        'notes' => 'Should not apply',
    ]))->assertSessionHasErrors('status');
    expect($cancelled->fresh()->notes)->not->toBe('Should not apply');
});

test('a scheduled appointment can be cancelled with an optional reason', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $staffUser = User::factory()->create();
    $staffMember = addOrganizationMember($organization, $staffUser, OrganizationRole::Staff);
    $appointment = Appointment::factory()->create([
        'organization_id' => $organization->id,
        'employee_id' => $staffMember->id,
    ]);
    switchInto($this, $owner, $organization);

    $this->patch("/appointments/{$appointment->id}/cancel", [])->assertRedirect();

    $appointment->refresh();
    expect($appointment->status)->toBe(AppointmentStatus::Cancelled);
    expect($appointment->cancellation_reason)->toBeNull();
});

test('an already-cancelled appointment cannot be cancelled again', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $appointment = Appointment::factory()->cancelled()->create(['organization_id' => $organization->id]);
    switchInto($this, $owner, $organization);

    $this->patch("/appointments/{$appointment->id}/cancel", ['cancellation_reason' => 'again'])
        ->assertSessionHasErrors('status');
});

test('the same staff member cannot be double-booked for overlapping times', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $staffUser = User::factory()->create();
    $staffMember = addOrganizationMember($organization, $staffUser, OrganizationRole::Staff);
    $service = Service::factory()->create(['organization_id' => $organization->id]);
    $clientA = Client::factory()->create(['organization_id' => $organization->id]);
    $clientB = Client::factory()->create(['organization_id' => $organization->id]);

    Appointment::factory()->create([
        'organization_id' => $organization->id,
        'client_id' => $clientA->id,
        'service_id' => $service->id,
        'employee_id' => $staffMember->id,
        'starts_at' => now()->addDay()->setTime(10, 0),
        'ends_at' => now()->addDay()->setTime(11, 0),
    ]);

    switchInto($this, $owner, $organization);

    // Overlaps the existing 10:00–11:00 booking for the same staff member.
    $response = $this->post('/appointments', bookingPayload($clientB, $service, $staffMember->id, [
        'starts_at' => now()->addDay()->setTime(10, 30)->toDateTimeString(),
        'ends_at' => now()->addDay()->setTime(11, 30)->toDateTimeString(),
    ]));

    $response->assertSessionHasErrors('employee_id');
    $this->assertDatabaseMissing('appointments', ['client_id' => $clientB->id]);
});

test('the same client cannot be double-booked for overlapping times, even with different staff', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $staffUserA = User::factory()->create();
    $staffA = addOrganizationMember($organization, $staffUserA, OrganizationRole::Staff);
    $staffUserB = User::factory()->create();
    $staffB = addOrganizationMember($organization, $staffUserB, OrganizationRole::Staff);
    $service = Service::factory()->create(['organization_id' => $organization->id]);
    $client = Client::factory()->create(['organization_id' => $organization->id]);

    Appointment::factory()->create([
        'organization_id' => $organization->id,
        'client_id' => $client->id,
        'service_id' => $service->id,
        'employee_id' => $staffA->id,
        'starts_at' => now()->addDay()->setTime(10, 0),
        'ends_at' => now()->addDay()->setTime(11, 0),
    ]);

    switchInto($this, $owner, $organization);

    $response = $this->post('/appointments', bookingPayload($client, $service, $staffB->id, [
        'starts_at' => now()->addDay()->setTime(10, 30)->toDateTimeString(),
        'ends_at' => now()->addDay()->setTime(11, 30)->toDateTimeString(),
    ]));

    $response->assertSessionHasErrors('client_id');
});

test('back-to-back appointments that only touch at the boundary do not conflict', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $staffUser = User::factory()->create();
    $staffMember = addOrganizationMember($organization, $staffUser, OrganizationRole::Staff);
    $service = Service::factory()->create(['organization_id' => $organization->id]);
    $client = Client::factory()->create(['organization_id' => $organization->id]);

    Appointment::factory()->create([
        'organization_id' => $organization->id,
        'client_id' => $client->id,
        'service_id' => $service->id,
        'employee_id' => $staffMember->id,
        'starts_at' => now()->addDay()->setTime(10, 0),
        'ends_at' => now()->addDay()->setTime(11, 0),
    ]);

    switchInto($this, $owner, $organization);

    $response = $this->post('/appointments', bookingPayload($client, $service, $staffMember->id, [
        'starts_at' => now()->addDay()->setTime(11, 0)->toDateTimeString(),
        'ends_at' => now()->addDay()->setTime(12, 0)->toDateTimeString(),
    ]));

    $response->assertRedirect();
});

test('a cancelled appointment does not count as a conflict', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $staffUser = User::factory()->create();
    $staffMember = addOrganizationMember($organization, $staffUser, OrganizationRole::Staff);
    $service = Service::factory()->create(['organization_id' => $organization->id]);
    $client = Client::factory()->create(['organization_id' => $organization->id]);

    Appointment::factory()->cancelled()->create([
        'organization_id' => $organization->id,
        'client_id' => $client->id,
        'service_id' => $service->id,
        'employee_id' => $staffMember->id,
        'starts_at' => now()->addDay()->setTime(10, 0),
        'ends_at' => now()->addDay()->setTime(11, 0),
    ]);

    switchInto($this, $owner, $organization);

    $response = $this->post('/appointments', bookingPayload($client, $service, $staffMember->id, [
        'starts_at' => now()->addDay()->setTime(10, 30)->toDateTimeString(),
        'ends_at' => now()->addDay()->setTime(11, 30)->toDateTimeString(),
    ]));

    $response->assertRedirect();
});

test('staff can book, edit, and cancel appointments', function () {
    $staffActorUser = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $staffActorUser, OrganizationRole::Staff);
    $assigneeUser = User::factory()->create();
    $assignee = addOrganizationMember($organization, $assigneeUser, OrganizationRole::Staff);
    $client = Client::factory()->create(['organization_id' => $organization->id]);
    $service = Service::factory()->create(['organization_id' => $organization->id]);
    switchInto($this, $staffActorUser, $organization);

    $response = $this->post('/appointments', bookingPayload($client, $service, $assignee->id));
    $response->assertRedirect();

    $appointment = Appointment::where('organization_id', $organization->id)->firstOrFail();

    $this->patch("/appointments/{$appointment->id}", bookingPayload($client, $service, $assignee->id, [
        'notes' => 'Edited by staff',
    ]))->assertRedirect();

    $this->patch("/appointments/{$appointment->id}/cancel", [])->assertRedirect();
});

test('viewer can list appointments but cannot book, edit, or cancel them', function () {
    $viewer = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $viewer, OrganizationRole::Viewer);
    $staffUser = User::factory()->create();
    $staffMember = addOrganizationMember($organization, $staffUser, OrganizationRole::Staff);
    $client = Client::factory()->create(['organization_id' => $organization->id]);
    $service = Service::factory()->create(['organization_id' => $organization->id]);
    // A different day than bookingPayload()'s default (tomorrow 10–11) so
    // the forbidden POST/PATCH below are rejected on authorization, not
    // masked by an incidental double-booking conflict on the same slot.
    $appointment = Appointment::factory()->create([
        'organization_id' => $organization->id,
        'employee_id' => $staffMember->id,
        'starts_at' => now()->addDays(5)->setTime(10, 0),
        'ends_at' => now()->addDays(5)->setTime(11, 0),
    ]);
    switchInto($this, $viewer, $organization);

    $this->get('/appointments')->assertOk();

    $this->post('/appointments', bookingPayload($client, $service, $staffMember->id))->assertForbidden();
    $this->patch("/appointments/{$appointment->id}", bookingPayload($client, $service, $staffMember->id, [
        'starts_at' => now()->addDays(5)->setTime(10, 0)->toDateTimeString(),
        'ends_at' => now()->addDays(5)->setTime(11, 0)->toDateTimeString(),
    ]))->assertForbidden();
    $this->patch("/appointments/{$appointment->id}/cancel", [])->assertForbidden();
});
