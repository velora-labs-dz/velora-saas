<?php

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\User;

test('guests cannot create an organization', function () {
    $response = $this->post('/organizations', ['name' => 'Style Le Club']);

    $response->assertRedirect('/login');
    $this->assertDatabaseCount('organizations', 0);
});

test('an authenticated user can create an organization', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/organizations', [
        'name' => 'Style Le Club',
        'contact_email' => 'contact@styleleclub.dz',
    ]);

    $response->assertRedirect(route('dashboard', absolute: false));

    $this->assertDatabaseCount('organizations', 1);

    $organization = Organization::first();

    expect($organization->name)->toBe('Style Le Club');
    expect($organization->slug)->not->toBeEmpty();
    expect($organization->status->value)->toBe('active');
    expect($organization->created_by)->toBe($user->id);
});

test('creating an organization makes the creator its owner', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/organizations', ['name' => 'Style Le Club']);

    $organization = Organization::first();
    $membership = $organization->members()->where('user_id', $user->id)->first();

    expect($membership)->not->toBeNull();
    expect($membership->role)->toBe(OrganizationRole::Owner);
    expect($membership->is_active)->toBeTrue();
});

test('creating an organization sets it as the current organization in session', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/organizations', ['name' => 'Style Le Club']);

    $organization = Organization::first();

    expect(session('current_organization_id'))->toBe($organization->id);
});

test('organization slug is generated server-side and cannot be set from the request', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/organizations', [
        'name' => 'Style Le Club',
        'slug' => 'attacker-chosen-slug',
        'status' => 'cancelled',
        'created_by' => 999,
    ]);

    $organization = Organization::first();

    expect($organization->slug)->not->toBe('attacker-chosen-slug');
    expect($organization->status->value)->toBe('active');
    expect($organization->created_by)->toBe($user->id);
});

test('duplicate organization names still produce unique slugs', function () {
    $alice = User::factory()->create();
    $bob = User::factory()->create();

    $this->actingAs($alice)->post('/organizations', ['name' => 'Style Le Club']);
    $this->actingAs($bob)->post('/organizations', ['name' => 'Style Le Club']);

    $slugs = Organization::pluck('slug');

    expect($slugs)->toHaveCount(2);
    expect($slugs[0])->not->toBe($slugs[1]);
});

test('a user belonging to only one organization cannot be duplicated in that organization', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();

    addOrganizationMember($organization, $user);

    expect(fn () => addOrganizationMember($organization, $user))
        ->toThrow(\Illuminate\Database\QueryException::class);
});
