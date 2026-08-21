<?php

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\User;

/**
 * Owner: full organization access.
 */
test('owner has full organization access', function () {
    $owner = User::factory()->create();
    $target = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);

    // Can update organization settings.
    expect($owner->can('update', $organization))->toBeTrue();

    // Can add a member as any role, including owner (ownership transfer).
    expect($owner->can('addMember', [$organization, OrganizationRole::Owner]))->toBeTrue();
    expect($owner->can('addMember', [$organization, OrganizationRole::Admin]))->toBeTrue();

    $targetMembership = addOrganizationMember($organization, $target, OrganizationRole::Admin);

    // Can grant/revoke owner on someone else (ownership transfer).
    expect($owner->can('updateMemberRole', [$organization, $targetMembership, OrganizationRole::Owner]))->toBeTrue();

    // Can remove members, including other owners.
    $secondOwner = User::factory()->create();
    $secondOwnerMembership = addOrganizationMember($organization, $secondOwner, OrganizationRole::Owner);
    expect($owner->can('removeMember', [$organization, $secondOwnerMembership]))->toBeTrue();
});

/**
 * Admin: administrative access but not ownership transfer.
 */
test('admin has administrative access but cannot transfer ownership', function () {
    $admin = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $admin, OrganizationRole::Admin);

    $staffUser = User::factory()->create();
    $ownerUser = User::factory()->create();
    $staffMembership = addOrganizationMember($organization, $staffUser, OrganizationRole::Staff);
    $ownerMembership = addOrganizationMember($organization, $ownerUser, OrganizationRole::Owner);

    // Can update organization settings.
    expect($admin->can('update', $organization))->toBeTrue();

    // Can add a non-owner member.
    expect($admin->can('addMember', [$organization, OrganizationRole::Staff]))->toBeTrue();

    // Cannot add someone directly as owner.
    expect($admin->can('addMember', [$organization, OrganizationRole::Owner]))->toBeFalse();

    // Can change a non-owner member's role (to another non-owner role).
    expect($admin->can('updateMemberRole', [$organization, $staffMembership, OrganizationRole::Viewer]))->toBeTrue();

    // Cannot promote anyone to owner.
    expect($admin->can('updateMemberRole', [$organization, $staffMembership, OrganizationRole::Owner]))->toBeFalse();

    // Cannot change an existing owner's role at all.
    expect($admin->can('updateMemberRole', [$organization, $ownerMembership, OrganizationRole::Admin]))->toBeFalse();

    // Can remove a non-owner member.
    expect($admin->can('removeMember', [$organization, $staffMembership]))->toBeTrue();

    // Cannot remove an owner.
    expect($admin->can('removeMember', [$organization, $ownerMembership]))->toBeFalse();
});

/**
 * Staff: operational access, no organization administration, no
 * destructive member-management actions.
 */
test('staff has no organization administration access', function () {
    $staff = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $staff, OrganizationRole::Staff);

    $otherUser = User::factory()->create();
    $otherMembership = addOrganizationMember($organization, $otherUser, OrganizationRole::Viewer);

    // Can view the organization and its members (read).
    expect($staff->can('view', $organization))->toBeTrue();
    expect($staff->can('viewMembers', $organization))->toBeTrue();

    // Cannot update organization settings.
    expect($staff->can('update', $organization))->toBeFalse();

    // Cannot add, update, or remove members.
    expect($staff->can('addMember', [$organization, OrganizationRole::Staff]))->toBeFalse();
    expect($staff->can('updateMemberRole', [$organization, $otherMembership, OrganizationRole::Admin]))->toBeFalse();
    expect($staff->can('removeMember', [$organization, $otherMembership]))->toBeFalse();
});

/**
 * Viewer: read only.
 */
test('viewer has read-only access', function () {
    $viewer = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $viewer, OrganizationRole::Viewer);

    $otherUser = User::factory()->create();
    $otherMembership = addOrganizationMember($organization, $otherUser, OrganizationRole::Staff);

    // Can read.
    expect($viewer->can('view', $organization))->toBeTrue();
    expect($viewer->can('viewMembers', $organization))->toBeTrue();

    // Cannot mutate anything.
    expect($viewer->can('update', $organization))->toBeFalse();
    expect($viewer->can('addMember', [$organization, OrganizationRole::Viewer]))->toBeFalse();
    expect($viewer->can('updateMemberRole', [$organization, $otherMembership, OrganizationRole::Admin]))->toBeFalse();
    expect($viewer->can('removeMember', [$organization, $otherMembership]))->toBeFalse();
});

/**
 * The member-management endpoints are new attack surface introduced by
 * this step — verify tenant isolation holds for them too, the same way it
 * does for the organization itself.
 */
test('a member of one organization cannot manage members of another organization', function () {
    $ownerA = User::factory()->create();
    $ownerB = User::factory()->create();
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();
    addOrganizationMember($orgA, $ownerA, OrganizationRole::Owner);
    $ownerBMembership = addOrganizationMember($orgB, $ownerB, OrganizationRole::Owner);

    // Alice (owner of A) cannot view B's member list via HTTP.
    $this->actingAs($ownerA)
        ->get("/organizations/{$orgB->slug}/members")
        ->assertForbidden();

    // Alice cannot add a member to B.
    $someUser = User::factory()->create();
    $this->actingAs($ownerA)
        ->post("/organizations/{$orgB->slug}/members", [
            'email' => $someUser->email,
            'role' => 'staff',
        ])
        ->assertForbidden();

    // Alice cannot change B's owner's role, even by guessing the membership id.
    $this->actingAs($ownerA)
        ->patch("/organizations/{$orgB->slug}/members/{$ownerBMembership->id}", [
            'role' => 'staff',
        ])
        ->assertForbidden();

    // Alice cannot remove a member of B.
    $this->actingAs($ownerA)
        ->delete("/organizations/{$orgB->slug}/members/{$ownerBMembership->id}")
        ->assertForbidden();
});

/**
 * A membership id from a different organization must not be usable against
 * this organization's slug, even by an actor who legitimately manages this
 * organization (IDOR-style cross-org id confusion).
 */
test('a membership id belonging to a different organization cannot be targeted through this organization\'s routes', function () {
    $ownerA = User::factory()->create();
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();
    addOrganizationMember($orgA, $ownerA, OrganizationRole::Owner);

    $userInB = User::factory()->create();
    $membershipInB = addOrganizationMember($orgB, $userInB, OrganizationRole::Staff);

    $this->actingAs($ownerA)
        ->patch("/organizations/{$orgA->slug}/members/{$membershipInB->id}", [
            'role' => 'admin',
        ])
        ->assertForbidden();

    expect($membershipInB->fresh()->role)->toBe(OrganizationRole::Staff);
});
