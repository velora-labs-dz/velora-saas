<?php

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\Service;
use App\Models\User;

test('a user cannot see another organization\'s services in their list, even as a member of both', function () {
    $user = User::factory()->create();
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();
    addOrganizationMember($orgA, $user, OrganizationRole::Owner);
    addOrganizationMember($orgB, $user, OrganizationRole::Owner);
    Service::factory()->create(['organization_id' => $orgA->id, 'name' => 'InA']);
    Service::factory()->create(['organization_id' => $orgB->id, 'name' => 'InB']);

    switchInto($this, $user, $orgA);

    $response = $this->get('/services');

    $response->assertInertia(fn ($page) => $page
        ->where('services', fn ($services) => count($services) === 1 && $services[0]['name'] === 'InA')
    );
});

test('a service id belonging to a different organization is not reachable by guessing the id', function () {
    $ownerA = User::factory()->create();
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();
    addOrganizationMember($orgA, $ownerA, OrganizationRole::Owner);
    $serviceInB = Service::factory()->create(['organization_id' => $orgB->id]);

    switchInto($this, $ownerA, $orgA);

    $this->get("/services/{$serviceInB->id}/edit")->assertNotFound();

    $this->patch("/services/{$serviceInB->id}", [
        'name' => 'Hijacked',
        'duration_minutes' => $serviceInB->duration_minutes,
        'price' => (string) $serviceInB->price,
        'currency' => $serviceInB->currency,
    ])->assertNotFound();

    $this->patch("/services/{$serviceInB->id}/toggle-status")->assertNotFound();

    expect($serviceInB->fresh()->name)->not->toBe('Hijacked');
});
