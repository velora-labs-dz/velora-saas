<?php

use App\Enums\OrganizationRole;
use App\Models\Client;
use App\Models\Organization;
use App\Models\User;

test('a user cannot see another organization\'s clients in their list, even as a member of both', function () {
    $user = User::factory()->create();
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();
    addOrganizationMember($orgA, $user, OrganizationRole::Owner);
    addOrganizationMember($orgB, $user, OrganizationRole::Owner);
    Client::factory()->create(['organization_id' => $orgA->id, 'first_name' => 'InA']);
    Client::factory()->create(['organization_id' => $orgB->id, 'first_name' => 'InB']);

    switchInto($this, $user, $orgA);

    $response = $this->get('/clients');

    $response->assertInertia(fn ($page) => $page
        ->where('clients.total', 1)
        ->where('clients.data.0.full_name', fn ($name) => str_starts_with($name, 'InA'))
    );
});

test('a client id belonging to a different organization is not reachable by guessing the id, even for an owner of the current organization', function () {
    $ownerA = User::factory()->create();
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();
    addOrganizationMember($orgA, $ownerA, OrganizationRole::Owner);
    $clientInB = Client::factory()->create(['organization_id' => $orgB->id]);

    switchInto($this, $ownerA, $orgA);

    $this->get("/clients/{$clientInB->id}")->assertNotFound();
    $this->get("/clients/{$clientInB->id}/edit")->assertNotFound();

    $this->patch("/clients/{$clientInB->id}", [
        'first_name' => 'Hijacked',
        'last_name' => $clientInB->last_name,
        'phone' => $clientInB->phone,
    ])->assertNotFound();

    $this->delete("/clients/{$clientInB->id}")->assertNotFound();

    expect($clientInB->fresh()->first_name)->not->toBe('Hijacked');
    expect($clientInB->fresh()->trashed())->toBeFalse();
});

test('a user who is not a member of an organization cannot select it as current and therefore cannot list its clients', function () {
    $outsider = User::factory()->create();
    $organization = Organization::factory()->create();
    Client::factory()->create(['organization_id' => $organization->id]);

    // The switch attempt itself is rejected — no membership exists.
    $this->actingAs($outsider)
        ->post("/organizations/{$organization->slug}/switch")
        ->assertForbidden();

    // With no current organization resolved, /clients redirects rather
    // than leaking data.
    $this->get('/clients')->assertRedirect(route('organizations.index', absolute: false));
});

test('deactivating a membership revokes access to that organization\'s clients on the next request', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $membership = addOrganizationMember($organization, $user, OrganizationRole::Staff);
    Client::factory()->create(['organization_id' => $organization->id]);

    switchInto($this, $user, $organization);
    $this->get('/clients')->assertOk();

    $membership->is_active = false;
    $membership->save();

    // ResolveCurrentOrganization re-verifies on every request; the stale
    // session hint no longer resolves to a real membership.
    $this->get('/clients')->assertRedirect(route('organizations.index', absolute: false));
});
