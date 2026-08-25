<?php

use App\Enums\OrganizationRole;
use App\Models\Attendance;
use App\Models\Client;
use App\Models\Organization;
use App\Models\User;

test('an owner can check a client in', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $client = Client::factory()->create(['organization_id' => $organization->id]);
    switchInto($this, $owner, $organization);

    $response = $this->post('/attendance/check-in', [
        'client_id' => $client->id,
        'notes' => 'First visit',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('attendance', [
        'organization_id' => $organization->id,
        'client_id' => $client->id,
        'source' => 'manual',
        'notes' => 'First visit',
        'check_out_at' => null,
        'recorded_by' => $owner->id,
    ]);
});

test('checking in requires a client', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    switchInto($this, $owner, $organization);

    $response = $this->post('/attendance/check-in', []);

    $response->assertSessionHasErrors('client_id');
});

test('a client with no open session can be checked in again after checking out', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $client = Client::factory()->create(['organization_id' => $organization->id]);
    Attendance::factory()->closed()->create([
        'organization_id' => $organization->id,
        'client_id' => $client->id,
    ]);
    switchInto($this, $owner, $organization);

    $response = $this->post('/attendance/check-in', ['client_id' => $client->id]);

    $response->assertRedirect();
    expect(Attendance::where('client_id', $client->id)->count())->toBe(2);
});

test('a client cannot be checked in twice while already having an open session', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $client = Client::factory()->create(['organization_id' => $organization->id]);
    Attendance::factory()->create([
        'organization_id' => $organization->id,
        'client_id' => $client->id,
    ]);
    switchInto($this, $owner, $organization);

    $response = $this->post('/attendance/check-in', ['client_id' => $client->id]);

    $response->assertSessionHasErrors('client_id');
    expect(Attendance::where('client_id', $client->id)->count())->toBe(1);
});

test('an open attendance record can be checked out', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $attendance = Attendance::factory()->create(['organization_id' => $organization->id]);
    switchInto($this, $owner, $organization);

    $response = $this->patch("/attendance/{$attendance->id}/check-out");

    $response->assertRedirect();
    expect($attendance->fresh()->check_out_at)->not->toBeNull();
    expect($attendance->fresh()->isOpen())->toBeFalse();
});

test('an already checked-out record cannot be checked out again', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $attendance = Attendance::factory()->closed()->create(['organization_id' => $organization->id]);
    $originalCheckOut = $attendance->check_out_at;
    switchInto($this, $owner, $organization);

    $response = $this->patch("/attendance/{$attendance->id}/check-out");

    $response->assertSessionHasErrors('check_out_at');
    expect($attendance->fresh()->check_out_at->equalTo($originalCheckOut))->toBeTrue();
});

test('staff can check clients in and out', function () {
    $staff = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $staff, OrganizationRole::Staff);
    $client = Client::factory()->create(['organization_id' => $organization->id]);
    switchInto($this, $staff, $organization);

    $this->post('/attendance/check-in', ['client_id' => $client->id])->assertRedirect();

    $attendance = Attendance::where('client_id', $client->id)->firstOrFail();

    $this->patch("/attendance/{$attendance->id}/check-out")->assertRedirect();
});

test('viewer can list attendance but cannot check clients in or out', function () {
    $viewer = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $viewer, OrganizationRole::Viewer);
    $client = Client::factory()->create(['organization_id' => $organization->id]);
    $attendance = Attendance::factory()->create(['organization_id' => $organization->id]);
    switchInto($this, $viewer, $organization);

    $this->get('/attendance')->assertOk();

    $this->post('/attendance/check-in', ['client_id' => $client->id])->assertForbidden();
    $this->patch("/attendance/{$attendance->id}/check-out")->assertForbidden();
});
