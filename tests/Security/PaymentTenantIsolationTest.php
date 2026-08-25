<?php

use App\Enums\OrganizationRole;
use App\Models\Client;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\User;

test('a user cannot see another organization\'s payments in their list, even as a member of both', function () {
    $user = User::factory()->create();
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();
    addOrganizationMember($orgA, $user, OrganizationRole::Owner);
    addOrganizationMember($orgB, $user, OrganizationRole::Owner);

    $today = now();
    Payment::factory()->create(['organization_id' => $orgA->id, 'paid_at' => $today]);
    Payment::factory()->create(['organization_id' => $orgB->id, 'paid_at' => $today]);

    switchInto($this, $user, $orgA);

    $response = $this->get('/payments?date='.$today->toDateString());

    $response->assertInertia(fn ($page) => $page
        ->where('payments', fn ($payments) => count($payments) === 1)
    );
});

test('a payment id belonging to a different organization is not reachable by guessing the id', function () {
    $ownerA = User::factory()->create();
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();
    addOrganizationMember($orgA, $ownerA, OrganizationRole::Owner);
    $paymentInB = Payment::factory()->create(['organization_id' => $orgB->id]);

    switchInto($this, $ownerA, $orgA);

    $this->patch("/payments/{$paymentInB->id}/void", ['void_reason' => 'Hijacked'])->assertNotFound();
    $this->patch("/payments/{$paymentInB->id}/refund", ['amount' => '10.00', 'refund_reason' => 'Hijacked'])->assertNotFound();

    expect($paymentInB->fresh()->status->value)->toBe('recorded');
});

test('a client or membership belonging to a different organization cannot be used on a payment, even by guessing the id', function () {
    $ownerA = User::factory()->create();
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();
    addOrganizationMember($orgA, $ownerA, OrganizationRole::Owner);
    $clientInA = Client::factory()->create(['organization_id' => $orgA->id]);
    $clientInB = Client::factory()->create(['organization_id' => $orgB->id]);
    $membershipInB = Membership::factory()->create([
        'organization_id' => $orgB->id,
        'client_id' => $clientInB->id,
    ]);

    switchInto($this, $ownerA, $orgA);

    // A client that exists, but belongs to another organization.
    $this->post('/payments', [
        'client_id' => $clientInB->id,
        'amount' => '1000.00',
        'currency' => 'DZD',
        'method' => 'cash',
        'paid_at' => now()->toDateTimeString(),
    ])->assertSessionHasErrors('client_id');

    // A membership that exists, but belongs to another organization.
    $this->post('/payments', [
        'client_id' => $clientInA->id,
        'membership_id' => $membershipInB->id,
        'amount' => '1000.00',
        'currency' => 'DZD',
        'method' => 'cash',
        'paid_at' => now()->toDateTimeString(),
    ])->assertSessionHasErrors('membership_id');

    $this->assertDatabaseMissing('payments', ['organization_id' => $orgA->id]);
});
