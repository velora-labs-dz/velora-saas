<?php

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\User;

test('an owner can view the members list', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);

    $response = $this->actingAs($owner)->get("/organizations/{$organization->slug}/members");

    $response->assertOk();
});

test('an owner can add an existing user as a member', function () {
    $owner = User::factory()->create();
    $newMember = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);

    $response = $this->actingAs($owner)->post("/organizations/{$organization->slug}/members", [
        'email' => $newMember->email,
        'role' => 'staff',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('organization_members', [
        'organization_id' => $organization->id,
        'user_id' => $newMember->id,
        'role' => 'staff',
    ]);
});

test('adding a member requires an existing user account', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);

    $response = $this->actingAs($owner)->post("/organizations/{$organization->slug}/members", [
        'email' => 'nobody@example.com',
        'role' => 'staff',
    ]);

    $response->assertSessionHasErrors('email');
});

test('a user cannot be added to the same organization twice', function () {
    $owner = User::factory()->create();
    $existingMember = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    addOrganizationMember($organization, $existingMember, OrganizationRole::Staff);

    $response = $this->actingAs($owner)->post("/organizations/{$organization->slug}/members", [
        'email' => $existingMember->email,
        'role' => 'admin',
    ]);

    $response->assertSessionHasErrors('email');
    expect($organization->members()->where('user_id', $existingMember->id)->count())->toBe(1);
});

test('an owner can change a member\'s role', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $membership = addOrganizationMember($organization, $member, OrganizationRole::Staff);

    $response = $this->actingAs($owner)->patch(
        "/organizations/{$organization->slug}/members/{$membership->id}",
        ['role' => 'admin'],
    );

    $response->assertRedirect();
    expect($membership->fresh()->role)->toBe(OrganizationRole::Admin);
});

test('an owner can remove a member', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $membership = addOrganizationMember($organization, $member, OrganizationRole::Staff);

    $response = $this->actingAs($owner)->delete(
        "/organizations/{$organization->slug}/members/{$membership->id}",
    );

    $response->assertRedirect();
    $this->assertDatabaseMissing('organization_members', ['id' => $membership->id]);
});

test('a member can leave an organization voluntarily', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    addOrganizationMember($organization, $member, OrganizationRole::Staff);

    $response = $this->actingAs($member)->post("/organizations/{$organization->slug}/leave");

    $response->assertRedirect(route('organizations.index', absolute: false));
    expect($organization->members()->where('user_id', $member->id)->exists())->toBeFalse();
});

test('the last owner cannot leave the organization', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);

    $response = $this->actingAs($owner)->post("/organizations/{$organization->slug}/leave");

    $response->assertSessionHasErrors('role');
    expect($organization->members()->where('user_id', $owner->id)->exists())->toBeTrue();
});

test('the sole remaining owner cannot be demoted', function () {
    $ownerA = User::factory()->create();
    $ownerB = User::factory()->create();
    $organization = Organization::factory()->create();
    $membershipA = addOrganizationMember($organization, $ownerA, OrganizationRole::Owner);
    addOrganizationMember($organization, $ownerB, OrganizationRole::Owner);

    // Remove B first, leaving A as the sole owner.
    $membershipB = $organization->members()->where('user_id', $ownerB->id)->first();
    $this->actingAs($ownerA)
        ->delete("/organizations/{$organization->slug}/members/{$membershipB->id}")
        ->assertRedirect();
    expect($organization->members()->where('role', 'owner')->where('is_active', true)->count())->toBe(1);

    // Now A is the last owner and cannot be removed via a hypothetical second owner —
    // simulate by trying to demote A itself, which must also fail.
    $response = $this->actingAs($ownerA)->patch(
        "/organizations/{$organization->slug}/members/{$membershipA->id}",
        ['role' => 'admin'],
    );

    $response->assertSessionHasErrors('role');
    expect($membershipA->fresh()->role)->toBe(OrganizationRole::Owner);
});

test('nobody can change their own role', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    $ownerMembership = addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $adminUser = User::factory()->create();
    $adminMembership = addOrganizationMember($organization, $adminUser, OrganizationRole::Admin);

    $response = $this->actingAs($adminUser)->patch(
        "/organizations/{$organization->slug}/members/{$adminMembership->id}",
        ['role' => 'owner'],
    );

    $response->assertForbidden();
    expect($adminMembership->fresh()->role)->toBe(OrganizationRole::Admin);
});
