<?php

use App\Enums\OrganizationRole;
use App\Models\Client;
use App\Models\Organization;
use App\Models\User;

test('a request with no current organization is redirected instead of listing clients', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/clients');

    $response->assertRedirect(route('organizations.index', absolute: false));
});

test('an owner can create a client', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    switchInto($this, $owner, $organization);

    $response = $this->post('/clients', [
        'first_name' => 'Yasmine',
        'last_name' => 'Benali',
        'phone' => '0551234567',
        'email' => 'yasmine@example.com',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('clients', [
        'organization_id' => $organization->id,
        'first_name' => 'Yasmine',
        'last_name' => 'Benali',
        'phone' => '0551234567',
        'created_by' => $owner->id,
    ]);
});

test('creating a client requires first name, last name, and phone', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    switchInto($this, $owner, $organization);

    $response = $this->post('/clients', []);

    $response->assertSessionHasErrors(['first_name', 'last_name', 'phone']);
});

test('a duplicate phone within the same organization is rejected', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    Client::factory()->create(['organization_id' => $organization->id, 'phone' => '0551234567']);
    switchInto($this, $owner, $organization);

    $response = $this->post('/clients', [
        'first_name' => 'Karim',
        'last_name' => 'Haddad',
        'phone' => '0551234567',
    ]);

    $response->assertSessionHasErrors('phone');
    expect(Client::where('organization_id', $organization->id)->where('phone', '0551234567')->count())->toBe(1);
});

test('the same phone number can belong to clients in two different organizations', function () {
    $ownerA = User::factory()->create();
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();
    addOrganizationMember($orgA, $ownerA, OrganizationRole::Owner);
    Client::factory()->create(['organization_id' => $orgB->id, 'phone' => '0551234567']);
    switchInto($this, $ownerA, $orgA);

    $response = $this->post('/clients', [
        'first_name' => 'Karim',
        'last_name' => 'Haddad',
        'phone' => '0551234567',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('clients', ['organization_id' => $orgA->id, 'phone' => '0551234567']);
});

test('an owner can view and edit a client', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $client = Client::factory()->create(['organization_id' => $organization->id]);
    switchInto($this, $owner, $organization);

    $this->get("/clients/{$client->id}")->assertOk();

    $response = $this->patch("/clients/{$client->id}", [
        'first_name' => 'Renamed',
        'last_name' => $client->last_name,
        'phone' => $client->phone,
    ]);

    $response->assertRedirect();
    expect($client->fresh()->first_name)->toBe('Renamed');
});

test('search filters clients by name, phone, or email', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    Client::factory()->create(['organization_id' => $organization->id, 'first_name' => 'Amina', 'last_name' => 'Cherif', 'phone' => '0550000001']);
    Client::factory()->create(['organization_id' => $organization->id, 'first_name' => 'Sofiane', 'last_name' => 'Meziane', 'phone' => '0550000002']);
    switchInto($this, $owner, $organization);

    $response = $this->get('/clients?search=Amina');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('clients.data.0.full_name', 'Amina Cherif')
        ->where('clients.total', 1)
    );
});

test('staff can create and edit clients but cannot archive them', function () {
    $staff = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $staff, OrganizationRole::Staff);
    $client = Client::factory()->create(['organization_id' => $organization->id]);
    switchInto($this, $staff, $organization);

    $this->post('/clients', [
        'first_name' => 'New',
        'last_name' => 'Client',
        'phone' => '0559999999',
    ])->assertRedirect();

    $this->patch("/clients/{$client->id}", [
        'first_name' => 'Edited',
        'last_name' => $client->last_name,
        'phone' => $client->phone,
    ])->assertRedirect();

    $this->delete("/clients/{$client->id}")->assertForbidden();
    expect($client->fresh()->trashed())->toBeFalse();
});

test('viewer can view but not create, edit, or archive clients', function () {
    $viewer = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $viewer, OrganizationRole::Viewer);
    $client = Client::factory()->create(['organization_id' => $organization->id]);
    switchInto($this, $viewer, $organization);

    $this->get('/clients')->assertOk();
    $this->get("/clients/{$client->id}")->assertOk();

    $this->post('/clients', [
        'first_name' => 'New',
        'last_name' => 'Client',
        'phone' => '0559999998',
    ])->assertForbidden();

    $this->patch("/clients/{$client->id}", [
        'first_name' => 'Nope',
        'last_name' => $client->last_name,
        'phone' => $client->phone,
    ])->assertForbidden();

    $this->delete("/clients/{$client->id}")->assertForbidden();
});

test('an owner can archive a client and it disappears from the active list', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $client = Client::factory()->create(['organization_id' => $organization->id]);
    switchInto($this, $owner, $organization);

    $this->delete("/clients/{$client->id}")->assertRedirect();

    expect($client->fresh()->trashed())->toBeTrue();

    $response = $this->get('/clients');
    $response->assertInertia(fn ($page) => $page->where('clients.total', 0));
});

test('archiving a client frees its phone number for a new client', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $client = Client::factory()->create(['organization_id' => $organization->id, 'phone' => '0551112222']);
    switchInto($this, $owner, $organization);

    $this->delete("/clients/{$client->id}")->assertRedirect();
    expect($client->fresh()->trashed())->toBeTrue();

    $this->post('/clients', [
        'first_name' => 'Someone',
        'last_name' => 'Else',
        'phone' => '0551112222',
    ])->assertRedirect();

    expect(Client::withTrashed()->where('organization_id', $organization->id)->where('phone', '0551112222')->count())->toBe(2);
    expect(Client::where('organization_id', $organization->id)->where('phone', '0551112222')->count())->toBe(1);
});

test('an owner can restore an archived client', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $client = Client::factory()->create(['organization_id' => $organization->id]);
    switchInto($this, $owner, $organization);

    $this->delete("/clients/{$client->id}")->assertRedirect();
    expect($client->fresh()->trashed())->toBeTrue();

    $this->post("/clients/{$client->id}/restore")->assertRedirect();
    expect($client->fresh()->trashed())->toBeFalse();
});
