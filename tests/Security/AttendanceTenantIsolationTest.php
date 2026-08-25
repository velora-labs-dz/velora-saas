<?php

use App\Enums\OrganizationRole;
use App\Models\Attendance;
use App\Models\Client;
use App\Models\Organization;
use App\Models\User;

test('a user cannot see another organization\'s attendance in their list, even as a member of both', function () {
    $user = User::factory()->create();
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();
    addOrganizationMember($orgA, $user, OrganizationRole::Owner);
    addOrganizationMember($orgB, $user, OrganizationRole::Owner);

    $today = now();
    Attendance::factory()->create(['organization_id' => $orgA->id, 'check_in_at' => $today]);
    Attendance::factory()->create(['organization_id' => $orgB->id, 'check_in_at' => $today]);

    switchInto($this, $user, $orgA);

    $response = $this->get('/attendance?date='.$today->toDateString());

    $response->assertInertia(fn ($page) => $page
        ->where('records', fn ($records) => count($records) === 1)
    );
});

test('an attendance id belonging to a different organization is not reachable by guessing the id', function () {
    $ownerA = User::factory()->create();
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();
    addOrganizationMember($orgA, $ownerA, OrganizationRole::Owner);
    $attendanceInB = Attendance::factory()->create(['organization_id' => $orgB->id]);

    switchInto($this, $ownerA, $orgA);

    $this->patch("/attendance/{$attendanceInB->id}/check-out")->assertNotFound();

    expect($attendanceInB->fresh()->check_out_at)->toBeNull();
});

test('a client belonging to a different organization cannot be checked in, even by guessing the id', function () {
    $ownerA = User::factory()->create();
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();
    addOrganizationMember($orgA, $ownerA, OrganizationRole::Owner);
    $clientInB = Client::factory()->create(['organization_id' => $orgB->id]);

    switchInto($this, $ownerA, $orgA);

    $this->post('/attendance/check-in', ['client_id' => $clientInB->id])
        ->assertSessionHasErrors('client_id');

    $this->assertDatabaseMissing('attendance', ['organization_id' => $orgA->id]);
});
