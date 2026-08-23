<?php

use App\Enums\OrganizationRole;
use App\Models\MembershipPlan;
use App\Models\Organization;
use App\Models\User;

test('a user cannot see another organization\'s membership plans in their list, even as a member of both', function () {
    $user = User::factory()->create();
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();
    addOrganizationMember($orgA, $user, OrganizationRole::Owner);
    addOrganizationMember($orgB, $user, OrganizationRole::Owner);
    MembershipPlan::factory()->create(['organization_id' => $orgA->id, 'name' => 'Plan In A']);
    MembershipPlan::factory()->create(['organization_id' => $orgB->id, 'name' => 'Plan In B']);

    switchInto($this, $user, $orgA);

    $response = $this->get('/membership-plans');

    $response->assertInertia(fn ($page) => $page
        ->where('plans', fn ($plans) => count($plans) === 1 && $plans[0]['name'] === 'Plan In A')
    );
});

test('a membership plan id belonging to a different organization is not reachable by guessing the id', function () {
    $ownerA = User::factory()->create();
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();
    addOrganizationMember($orgA, $ownerA, OrganizationRole::Owner);
    $planInB = MembershipPlan::factory()->create(['organization_id' => $orgB->id]);

    switchInto($this, $ownerA, $orgA);

    $this->get("/membership-plans/{$planInB->id}/edit")->assertNotFound();

    $this->patch("/membership-plans/{$planInB->id}", [
        'name' => 'Hijacked',
        'duration_value' => $planInB->duration_value,
        'duration_unit' => $planInB->duration_unit->value,
        'price' => (string) $planInB->price,
        'currency' => $planInB->currency,
        'freeze_allowed' => $planInB->freeze_allowed,
    ])->assertNotFound();

    $this->patch("/membership-plans/{$planInB->id}/toggle-status")->assertNotFound();

    expect($planInB->fresh()->name)->not->toBe('Hijacked');
    expect($planInB->fresh()->active)->toBeTrue();
});
