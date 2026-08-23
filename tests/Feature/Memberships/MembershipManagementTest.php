<?php

use App\Enums\MembershipStatus;
use App\Enums\OrganizationRole;
use App\Models\Client;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Organization;
use App\Models\User;

function assignPayload(Client $client, MembershipPlan $plan, array $overrides = []): array
{
    return array_merge([
        'client_id' => $client->id,
        'membership_plan_id' => $plan->id,
        'starts_at' => now()->toDateString(),
        'ends_at' => now()->addMonth()->toDateString(),
        'price' => (string) $plan->price,
        'currency' => $plan->currency,
    ], $overrides);
}

test('an owner can assign a membership plan to a client, starting as draft', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $client = Client::factory()->create(['organization_id' => $organization->id]);
    $plan = MembershipPlan::factory()->create(['organization_id' => $organization->id]);
    switchInto($this, $owner, $organization);

    $response = $this->post('/memberships', assignPayload($client, $plan));

    $response->assertRedirect();
    $this->assertDatabaseHas('memberships', [
        'organization_id' => $organization->id,
        'client_id' => $client->id,
        'membership_plan_id' => $plan->id,
        'status' => 'draft',
        'created_by' => $owner->id,
    ]);
});

test('assigning a membership requires client, plan, dates, price, and currency', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    switchInto($this, $owner, $organization);

    $response = $this->post('/memberships', []);

    $response->assertSessionHasErrors(['client_id', 'membership_plan_id', 'starts_at', 'ends_at', 'price', 'currency']);
});

test('an end date that is not after the start date is rejected', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $client = Client::factory()->create(['organization_id' => $organization->id]);
    $plan = MembershipPlan::factory()->create(['organization_id' => $organization->id]);
    switchInto($this, $owner, $organization);

    $response = $this->post('/memberships', assignPayload($client, $plan, [
        'starts_at' => now()->toDateString(),
        'ends_at' => now()->toDateString(),
    ]));

    $response->assertSessionHasErrors('ends_at');

    $response = $this->post('/memberships', assignPayload($client, $plan, [
        'starts_at' => now()->toDateString(),
        'ends_at' => now()->subDay()->toDateString(),
    ]));

    $response->assertSessionHasErrors('ends_at');
});

test('a draft membership can be edited but an active one cannot', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $draft = Membership::factory()->create(['organization_id' => $organization->id]);
    $active = Membership::factory()->active()->create(['organization_id' => $organization->id]);
    switchInto($this, $owner, $organization);

    $this->patch("/memberships/{$draft->id}", [
        'starts_at' => $draft->starts_at->toDateString(),
        'ends_at' => $draft->ends_at->toDateString(),
        'price' => (string) $draft->price,
        'currency' => $draft->currency,
        'notes' => 'Updated',
    ])->assertRedirect();
    expect($draft->fresh()->notes)->toBe('Updated');

    $this->patch("/memberships/{$active->id}", [
        'starts_at' => $active->starts_at->toDateString(),
        'ends_at' => $active->ends_at->toDateString(),
        'price' => (string) $active->price,
        'currency' => $active->currency,
        'notes' => 'Should not apply',
    ])->assertSessionHasErrors('status');
    expect($active->fresh()->notes)->not->toBe('Should not apply');
});

test('a draft membership can be activated', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $membership = Membership::factory()->create(['organization_id' => $organization->id]);
    switchInto($this, $owner, $organization);

    $this->patch("/memberships/{$membership->id}/activate")->assertRedirect();

    $membership->refresh();
    expect($membership->status)->toBe(MembershipStatus::Active);
    expect($membership->activated_at)->not->toBeNull();
});

test('an already-active membership cannot be activated again', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $membership = Membership::factory()->active()->create(['organization_id' => $organization->id]);
    switchInto($this, $owner, $organization);

    $this->patch("/memberships/{$membership->id}/activate")->assertSessionHasErrors('status');
    expect($membership->fresh()->status)->toBe(MembershipStatus::Active);
});

test('a draft membership cannot be frozen, only an active one can', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $draft = Membership::factory()->create(['organization_id' => $organization->id]);
    $active = Membership::factory()->active()->create(['organization_id' => $organization->id]);
    switchInto($this, $owner, $organization);

    $this->patch("/memberships/{$draft->id}/freeze")->assertSessionHasErrors('status');
    expect($draft->fresh()->status)->toBe(MembershipStatus::Draft);

    $this->patch("/memberships/{$active->id}/freeze")->assertRedirect();
    $active->refresh();
    expect($active->status)->toBe(MembershipStatus::Frozen);
    expect($active->frozen_at)->not->toBeNull();
});

test('a membership on a plan that does not allow freezing cannot be frozen', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $plan = MembershipPlan::factory()->freezeNotAllowed()->create(['organization_id' => $organization->id]);
    $membership = Membership::factory()->active()->create([
        'organization_id' => $organization->id,
        'membership_plan_id' => $plan->id,
    ]);
    switchInto($this, $owner, $organization);

    $this->patch("/memberships/{$membership->id}/freeze")->assertSessionHasErrors('status');
    expect($membership->fresh()->status)->toBe(MembershipStatus::Active);
});

test('a frozen membership can be manually unfrozen back to active', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $membership = Membership::factory()->frozen()->create(['organization_id' => $organization->id]);
    switchInto($this, $owner, $organization);

    $this->patch("/memberships/{$membership->id}/unfreeze")->assertRedirect();

    $membership->refresh();
    expect($membership->status)->toBe(MembershipStatus::Active);
    expect($membership->frozen_at)->toBeNull();
});

test('an active membership cannot be unfrozen because it was never frozen', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $membership = Membership::factory()->active()->create(['organization_id' => $organization->id]);
    switchInto($this, $owner, $organization);

    $this->patch("/memberships/{$membership->id}/unfreeze")->assertSessionHasErrors('status');
    expect($membership->fresh()->status)->toBe(MembershipStatus::Active);
});

test('an active membership can be marked expired but a draft one cannot', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $draft = Membership::factory()->create(['organization_id' => $organization->id]);
    $active = Membership::factory()->active()->create(['organization_id' => $organization->id]);
    switchInto($this, $owner, $organization);

    $this->patch("/memberships/{$draft->id}/expire")->assertSessionHasErrors('status');
    expect($draft->fresh()->status)->toBe(MembershipStatus::Draft);

    $this->patch("/memberships/{$active->id}/expire")->assertRedirect();
    expect($active->fresh()->status)->toBe(MembershipStatus::Expired);
});

test('draft, active, and frozen memberships can all be cancelled with a reason', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $draft = Membership::factory()->create(['organization_id' => $organization->id]);
    switchInto($this, $owner, $organization);

    $this->patch("/memberships/{$draft->id}/cancel", [
        'cancellation_reason' => 'Client moved away',
    ])->assertRedirect();

    $draft->refresh();
    expect($draft->status)->toBe(MembershipStatus::Cancelled);
    expect($draft->cancellation_reason)->toBe('Client moved away');
    expect($draft->cancelled_at)->not->toBeNull();
});

test('cancelling a membership requires a reason', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $membership = Membership::factory()->active()->create(['organization_id' => $organization->id]);
    switchInto($this, $owner, $organization);

    $this->patch("/memberships/{$membership->id}/cancel", [])
        ->assertSessionHasErrors('cancellation_reason');
    expect($membership->fresh()->status)->toBe(MembershipStatus::Active);
});

test('cancelled and expired memberships are terminal — no further transition is accepted', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $cancelled = Membership::factory()->cancelled()->create(['organization_id' => $organization->id]);
    $expired = Membership::factory()->expired()->create(['organization_id' => $organization->id]);
    switchInto($this, $owner, $organization);

    $this->patch("/memberships/{$cancelled->id}/activate")->assertSessionHasErrors('status');
    $this->patch("/memberships/{$cancelled->id}/freeze")->assertSessionHasErrors('status');
    $this->patch("/memberships/{$cancelled->id}/cancel", ['cancellation_reason' => 'again'])
        ->assertSessionHasErrors('status');

    $this->patch("/memberships/{$expired->id}/activate")->assertSessionHasErrors('status');
    $this->patch("/memberships/{$expired->id}/freeze")->assertSessionHasErrors('status');
    $this->patch("/memberships/{$expired->id}/cancel", ['cancellation_reason' => 'again'])
        ->assertSessionHasErrors('status');

    expect($cancelled->fresh()->status)->toBe(MembershipStatus::Cancelled);
    expect($expired->fresh()->status)->toBe(MembershipStatus::Expired);
});

test('staff can assign, activate, freeze, and unfreeze memberships but cannot cancel them', function () {
    $staff = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $staff, OrganizationRole::Staff);
    $client = Client::factory()->create(['organization_id' => $organization->id]);
    $plan = MembershipPlan::factory()->create(['organization_id' => $organization->id]);
    $membership = Membership::factory()->active()->create(['organization_id' => $organization->id]);
    switchInto($this, $staff, $organization);

    $this->post('/memberships', assignPayload($client, $plan))->assertRedirect();

    $this->patch("/memberships/{$membership->id}/freeze")->assertRedirect();
    $this->patch("/memberships/{$membership->id}/unfreeze")->assertRedirect();

    $this->patch("/memberships/{$membership->id}/cancel", [
        'cancellation_reason' => 'Trying anyway',
    ])->assertForbidden();
    expect($membership->fresh()->status)->not->toBe(MembershipStatus::Cancelled);
});

test('viewer can list memberships but cannot assign, transition, or cancel them', function () {
    $viewer = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $viewer, OrganizationRole::Viewer);
    $client = Client::factory()->create(['organization_id' => $organization->id]);
    $plan = MembershipPlan::factory()->create(['organization_id' => $organization->id]);
    $membership = Membership::factory()->create(['organization_id' => $organization->id]);
    switchInto($this, $viewer, $organization);

    $this->get('/memberships')->assertOk();

    $this->post('/memberships', assignPayload($client, $plan))->assertForbidden();
    $this->patch("/memberships/{$membership->id}/activate")->assertForbidden();
    $this->patch("/memberships/{$membership->id}/cancel", ['cancellation_reason' => 'Nope'])->assertForbidden();
});
