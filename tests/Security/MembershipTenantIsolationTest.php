<?php

use App\Enums\OrganizationRole;
use App\Models\Client;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Organization;
use App\Models\User;

test('a user cannot see another organization\'s memberships in their list, even as a member of both', function () {
    $user = User::factory()->create();
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();
    addOrganizationMember($orgA, $user, OrganizationRole::Owner);
    addOrganizationMember($orgB, $user, OrganizationRole::Owner);
    Membership::factory()->create(['organization_id' => $orgA->id]);
    Membership::factory()->create(['organization_id' => $orgB->id]);

    switchInto($this, $user, $orgA);

    $response = $this->get('/memberships');

    $response->assertInertia(fn ($page) => $page->where('memberships.total', 1));
});

test('a membership id belonging to a different organization is not reachable by guessing the id', function () {
    $ownerA = User::factory()->create();
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();
    addOrganizationMember($orgA, $ownerA, OrganizationRole::Owner);
    $membershipInB = Membership::factory()->create(['organization_id' => $orgB->id]);

    switchInto($this, $ownerA, $orgA);

    $this->get("/memberships/{$membershipInB->id}")->assertNotFound();
    $this->get("/memberships/{$membershipInB->id}/edit")->assertNotFound();
    $this->patch("/memberships/{$membershipInB->id}/activate")->assertNotFound();
    $this->patch("/memberships/{$membershipInB->id}/cancel", ['cancellation_reason' => 'x'])->assertNotFound();

    expect($membershipInB->fresh()->status->value)->toBe('draft');
});

test('a client or plan belonging to a different organization cannot be assigned a membership, even by guessing the id', function () {
    $ownerA = User::factory()->create();
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();
    addOrganizationMember($orgA, $ownerA, OrganizationRole::Owner);
    $clientInA = Client::factory()->create(['organization_id' => $orgA->id]);
    $planInA = MembershipPlan::factory()->create(['organization_id' => $orgA->id]);
    $clientInB = Client::factory()->create(['organization_id' => $orgB->id]);
    $planInB = MembershipPlan::factory()->create(['organization_id' => $orgB->id]);

    switchInto($this, $ownerA, $orgA);

    // A client that exists, but belongs to another organization.
    $this->post('/memberships', [
        'client_id' => $clientInB->id,
        'membership_plan_id' => $planInA->id,
        'starts_at' => now()->toDateString(),
        'ends_at' => now()->addMonth()->toDateString(),
        'price' => '5000.00',
        'currency' => 'DZD',
    ])->assertSessionHasErrors('client_id');

    // A plan that exists, but belongs to another organization.
    $this->post('/memberships', [
        'client_id' => $clientInA->id,
        'membership_plan_id' => $planInB->id,
        'starts_at' => now()->toDateString(),
        'ends_at' => now()->addMonth()->toDateString(),
        'price' => '5000.00',
        'currency' => 'DZD',
    ])->assertSessionHasErrors('membership_plan_id');

    $this->assertDatabaseMissing('memberships', ['organization_id' => $orgA->id]);
});
