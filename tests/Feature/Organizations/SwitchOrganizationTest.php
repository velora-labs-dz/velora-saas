<?php

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\User;

test('a user can switch to an organization they belong to', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $user, OrganizationRole::Staff);

    $response = $this->actingAs($user)->post("/organizations/{$organization->slug}/switch");

    $response->assertRedirect(route('dashboard', absolute: false));
    expect(session('current_organization_id'))->toBe($organization->id);
});

test('a user cannot switch to an organization they do not belong to', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();

    $response = $this->actingAs($user)->post("/organizations/{$organization->slug}/switch");

    $response->assertForbidden();
    expect(session('current_organization_id'))->toBeNull();
});

test('switching to an unauthorized organization does not overwrite a valid current organization', function () {
    $user = User::factory()->create();
    $ownOrganization = Organization::factory()->create();
    $otherOrganization = Organization::factory()->create();
    addOrganizationMember($ownOrganization, $user);

    $this->actingAs($user)->post("/organizations/{$ownOrganization->slug}/switch");

    $response = $this->post("/organizations/{$otherOrganization->slug}/switch");

    $response->assertForbidden();
    expect(session('current_organization_id'))->toBe($ownOrganization->id);
});

test('deactivating a membership revokes the ability to switch to it', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $user, OrganizationRole::Staff, isActive: false);

    $response = $this->actingAs($user)->post("/organizations/{$organization->slug}/switch");

    $response->assertForbidden();
});
