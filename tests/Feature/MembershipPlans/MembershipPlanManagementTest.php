<?php

use App\Enums\OrganizationRole;
use App\Models\MembershipPlan;
use App\Models\Organization;
use App\Models\User;

test('a request with no current organization is redirected instead of listing plans', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/membership-plans');

    $response->assertRedirect(route('organizations.index', absolute: false));
});

test('an owner can create a membership plan', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    switchInto($this, $owner, $organization);

    $response = $this->post('/membership-plans', [
        'name' => 'Monthly Unlimited',
        'duration_value' => 1,
        'duration_unit' => 'months',
        'price' => '5000.00',
        'currency' => 'DZD',
        'freeze_allowed' => true,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('membership_plans', [
        'organization_id' => $organization->id,
        'name' => 'Monthly Unlimited',
        'duration_value' => 1,
        'duration_unit' => 'months',
        'active' => true,
    ]);
});

test('creating a membership plan requires name, duration, price, and currency', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    switchInto($this, $owner, $organization);

    $response = $this->post('/membership-plans', []);

    $response->assertSessionHasErrors(['name', 'duration_value', 'duration_unit', 'price', 'currency', 'freeze_allowed']);
});

test('an owner can edit a membership plan', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $plan = MembershipPlan::factory()->create(['organization_id' => $organization->id, 'name' => 'Original']);
    switchInto($this, $owner, $organization);

    $response = $this->patch("/membership-plans/{$plan->id}", [
        'name' => 'Renamed',
        'duration_value' => $plan->duration_value,
        'duration_unit' => $plan->duration_unit->value,
        'price' => (string) $plan->price,
        'currency' => $plan->currency,
        'freeze_allowed' => $plan->freeze_allowed,
    ]);

    $response->assertRedirect();
    expect($plan->fresh()->name)->toBe('Renamed');
});

test('an owner can deactivate and reactivate a membership plan', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $plan = MembershipPlan::factory()->create(['organization_id' => $organization->id]);
    switchInto($this, $owner, $organization);

    $this->patch("/membership-plans/{$plan->id}/toggle-status")->assertRedirect();
    expect($plan->fresh()->active)->toBeFalse();

    $this->patch("/membership-plans/{$plan->id}/toggle-status")->assertRedirect();
    expect($plan->fresh()->active)->toBeTrue();
});

test('the default plans list excludes inactive plans until asked for', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    MembershipPlan::factory()->create(['organization_id' => $organization->id, 'name' => 'Active One']);
    MembershipPlan::factory()->inactive()->create(['organization_id' => $organization->id, 'name' => 'Inactive One']);
    switchInto($this, $owner, $organization);

    $this->get('/membership-plans')->assertInertia(fn ($page) => $page
        ->where('plans', fn ($plans) => count($plans) === 1)
    );

    $this->get('/membership-plans?inactive=1')->assertInertia(fn ($page) => $page
        ->where('plans', fn ($plans) => count($plans) === 2)
    );
});

test('staff can create, edit, and toggle membership plans', function () {
    $staff = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $staff, OrganizationRole::Staff);
    $plan = MembershipPlan::factory()->create(['organization_id' => $organization->id]);
    switchInto($this, $staff, $organization);

    $this->post('/membership-plans', [
        'name' => 'New Plan',
        'duration_value' => 3,
        'duration_unit' => 'months',
        'price' => '12000.00',
        'currency' => 'DZD',
        'freeze_allowed' => true,
    ])->assertRedirect();

    $this->patch("/membership-plans/{$plan->id}", [
        'name' => 'Edited',
        'duration_value' => $plan->duration_value,
        'duration_unit' => $plan->duration_unit->value,
        'price' => (string) $plan->price,
        'currency' => $plan->currency,
        'freeze_allowed' => $plan->freeze_allowed,
    ])->assertRedirect();

    $this->patch("/membership-plans/{$plan->id}/toggle-status")->assertRedirect();
});

test('viewer can list membership plans but cannot create, edit, or toggle them', function () {
    $viewer = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $viewer, OrganizationRole::Viewer);
    $plan = MembershipPlan::factory()->create(['organization_id' => $organization->id]);
    switchInto($this, $viewer, $organization);

    $this->get('/membership-plans')->assertOk();

    $this->post('/membership-plans', [
        'name' => 'New Plan',
        'duration_value' => 1,
        'duration_unit' => 'months',
        'price' => '5000.00',
        'currency' => 'DZD',
        'freeze_allowed' => true,
    ])->assertForbidden();

    $this->patch("/membership-plans/{$plan->id}", [
        'name' => 'Nope',
        'duration_value' => $plan->duration_value,
        'duration_unit' => $plan->duration_unit->value,
        'price' => (string) $plan->price,
        'currency' => $plan->currency,
        'freeze_allowed' => $plan->freeze_allowed,
    ])->assertForbidden();

    $this->patch("/membership-plans/{$plan->id}/toggle-status")->assertForbidden();
});
