<?php

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;

/**
 * Alice belongs to Organization A. Bob belongs to Organization B.
 *
 *   Alice -> Organization A = allowed
 *   Alice -> Organization B = denied
 *   Bob   -> Organization B = allowed
 *   Bob   -> Organization A = denied
 *
 * This is asserted through real Laravel authorization/application paths
 * (HTTP requests through the actual routes/policy), not by calling internal
 * methods directly.
 */
function makeTenantIsolationFixture(): array
{
    $alice = User::factory()->create(['name' => 'Alice']);
    $bob = User::factory()->create(['name' => 'Bob']);

    $orgA = Organization::factory()->create(['name' => 'Organization A']);
    $orgB = Organization::factory()->create(['name' => 'Organization B']);

    addOrganizationMember($orgA, $alice, OrganizationRole::Owner);
    addOrganizationMember($orgB, $bob, OrganizationRole::Owner);

    return compact('alice', 'bob', 'orgA', 'orgB');
}

test('alice can access organization A', function () {
    ['alice' => $alice, 'orgA' => $orgA] = makeTenantIsolationFixture();

    $response = $this->actingAs($alice)->get("/organizations/{$orgA->slug}");

    $response->assertOk();
});

test('alice cannot access organization B', function () {
    ['alice' => $alice, 'orgB' => $orgB] = makeTenantIsolationFixture();

    $response = $this->actingAs($alice)->get("/organizations/{$orgB->slug}");

    $response->assertForbidden();
});

test('bob can access organization B', function () {
    ['bob' => $bob, 'orgB' => $orgB] = makeTenantIsolationFixture();

    $response = $this->actingAs($bob)->get("/organizations/{$orgB->slug}");

    $response->assertOk();
});

test('bob cannot access organization A', function () {
    ['bob' => $bob, 'orgA' => $orgA] = makeTenantIsolationFixture();

    $response = $this->actingAs($bob)->get("/organizations/{$orgA->slug}");

    $response->assertForbidden();
});

test('alice cannot switch her active organization to organization B', function () {
    ['alice' => $alice, 'orgA' => $orgA, 'orgB' => $orgB] = makeTenantIsolationFixture();

    $this->actingAs($alice)->post("/organizations/{$orgA->slug}/switch");

    $response = $this->post("/organizations/{$orgB->slug}/switch");

    $response->assertForbidden();
    expect(session('current_organization_id'))->toBe($orgA->id);
});

test('a forged session organization id is never trusted as proof of membership', function () {
    ['bob' => $bob, 'orgA' => $orgA] = makeTenantIsolationFixture();

    // Simulate an attacker-controlled or stale session value pointing at an
    // organization Bob does not belong to.
    $this->withSession(['current_organization_id' => $orgA->id]);

    $response = $this->actingAs($bob)->get('/dashboard');

    $response->assertOk();
    // The resolver must have re-verified membership and dropped the forged hint.
    expect(session('current_organization_id'))->toBeNull();
});

test('organization membership rows cannot be duplicated for the same user and organization', function () {
    ['alice' => $alice, 'orgA' => $orgA] = makeTenantIsolationFixture();

    expect(fn () => addOrganizationMember($orgA, $alice))
        ->toThrow(\Illuminate\Database\QueryException::class);
});

test('deactivated membership no longer grants access to the organization', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $member = addOrganizationMember($organization, $user, OrganizationRole::Staff);
    $member->is_active = false;
    $member->save();

    $response = $this->actingAs($user)->get("/organizations/{$organization->slug}");

    $response->assertForbidden();
});

test('removing a membership row revokes access to the organization', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $member = addOrganizationMember($organization, $user, OrganizationRole::Staff);
    $member->delete();

    $response = $this->actingAs($user)->get("/organizations/{$organization->slug}");

    $response->assertForbidden();
});
