<?php

use App\Enums\OrganizationRole;
use App\Enums\ServiceStatus;
use App\Models\Organization;
use App\Models\Service;
use App\Models\User;

test('a request with no current organization is redirected instead of listing services', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/services');

    $response->assertRedirect(route('organizations.index', absolute: false));
});

test('an owner can create a service', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    switchInto($this, $owner, $organization);

    $response = $this->post('/services', [
        'name' => 'Deep Tissue Massage',
        'duration_minutes' => 60,
        'price' => '3500.00',
        'currency' => 'DZD',
        'capacity' => 1,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('services', [
        'organization_id' => $organization->id,
        'name' => 'Deep Tissue Massage',
        'duration_minutes' => 60,
        'status' => 'active',
        'created_by' => $owner->id,
    ]);
});

test('creating a service requires name, duration, price, and currency', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    switchInto($this, $owner, $organization);

    $response = $this->post('/services', []);

    $response->assertSessionHasErrors(['name', 'duration_minutes', 'price', 'currency']);
});

test('an owner can edit a service', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $service = Service::factory()->create(['organization_id' => $organization->id, 'name' => 'Original']);
    switchInto($this, $owner, $organization);

    $response = $this->patch("/services/{$service->id}", [
        'name' => 'Renamed',
        'duration_minutes' => $service->duration_minutes,
        'price' => (string) $service->price,
        'currency' => $service->currency,
    ]);

    $response->assertRedirect();
    expect($service->fresh()->name)->toBe('Renamed');
});

test('an owner can deactivate and reactivate a service', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $service = Service::factory()->create(['organization_id' => $organization->id]);
    switchInto($this, $owner, $organization);

    $this->patch("/services/{$service->id}/toggle-status")->assertRedirect();
    expect($service->fresh()->status)->toBe(ServiceStatus::Inactive);

    $this->patch("/services/{$service->id}/toggle-status")->assertRedirect();
    expect($service->fresh()->status)->toBe(ServiceStatus::Active);
});

test('the default services list excludes inactive services until asked for', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    Service::factory()->create(['organization_id' => $organization->id, 'name' => 'Active One']);
    Service::factory()->inactive()->create(['organization_id' => $organization->id, 'name' => 'Inactive One']);
    switchInto($this, $owner, $organization);

    $this->get('/services')->assertInertia(fn ($page) => $page
        ->where('services', fn ($services) => count($services) === 1)
    );

    $this->get('/services?inactive=1')->assertInertia(fn ($page) => $page
        ->where('services', fn ($services) => count($services) === 2)
    );
});

test('staff can create, edit, and toggle services', function () {
    $staff = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $staff, OrganizationRole::Staff);
    $service = Service::factory()->create(['organization_id' => $organization->id]);
    switchInto($this, $staff, $organization);

    $this->post('/services', [
        'name' => 'New Service',
        'duration_minutes' => 30,
        'price' => '1000.00',
        'currency' => 'DZD',
    ])->assertRedirect();

    $this->patch("/services/{$service->id}", [
        'name' => 'Edited',
        'duration_minutes' => $service->duration_minutes,
        'price' => (string) $service->price,
        'currency' => $service->currency,
    ])->assertRedirect();

    $this->patch("/services/{$service->id}/toggle-status")->assertRedirect();
});

test('viewer can list services but cannot create, edit, or toggle them', function () {
    $viewer = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $viewer, OrganizationRole::Viewer);
    $service = Service::factory()->create(['organization_id' => $organization->id]);
    switchInto($this, $viewer, $organization);

    $this->get('/services')->assertOk();

    $this->post('/services', [
        'name' => 'New Service',
        'duration_minutes' => 30,
        'price' => '1000.00',
        'currency' => 'DZD',
    ])->assertForbidden();

    $this->patch("/services/{$service->id}", [
        'name' => 'Nope',
        'duration_minutes' => $service->duration_minutes,
        'price' => (string) $service->price,
        'currency' => $service->currency,
    ])->assertForbidden();

    $this->patch("/services/{$service->id}/toggle-status")->assertForbidden();
});
