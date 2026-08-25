<?php

use App\Enums\OrganizationRole;
use App\Models\Client;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\User;

function paymentPayload(Client $client, array $overrides = []): array
{
    return array_merge([
        'client_id' => $client->id,
        'amount' => '5000.00',
        'currency' => 'DZD',
        'method' => 'cash',
        'paid_at' => now()->toDateTimeString(),
    ], $overrides);
}

test('an owner can record a payment for a client', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $client = Client::factory()->create(['organization_id' => $organization->id]);
    switchInto($this, $owner, $organization);

    $response = $this->post('/payments', paymentPayload($client));

    $response->assertRedirect();
    $this->assertDatabaseHas('payments', [
        'organization_id' => $organization->id,
        'client_id' => $client->id,
        'amount' => '5000.00',
        'status' => 'recorded',
        'recorded_by' => $owner->id,
    ]);
});

test('recording a payment requires a client, amount, currency, method, and paid date', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    switchInto($this, $owner, $organization);

    $response = $this->post('/payments', []);

    $response->assertSessionHasErrors(['client_id', 'amount', 'currency', 'method', 'paid_at']);
});

test('an invalid (zero or negative) amount is rejected', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $client = Client::factory()->create(['organization_id' => $organization->id]);
    switchInto($this, $owner, $organization);

    $this->post('/payments', paymentPayload($client, ['amount' => '0']))
        ->assertSessionHasErrors('amount');

    $this->post('/payments', paymentPayload($client, ['amount' => '-100']))
        ->assertSessionHasErrors('amount');
});

test('an invalid currency code is rejected', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $client = Client::factory()->create(['organization_id' => $organization->id]);
    switchInto($this, $owner, $organization);

    $response = $this->post('/payments', paymentPayload($client, ['currency' => 'TOOLONG']));

    $response->assertSessionHasErrors('currency');
});

test('recording a payment linked to a membership increases its paid_amount and lowers remaining_amount', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $client = Client::factory()->create(['organization_id' => $organization->id]);
    $plan = MembershipPlan::factory()->create(['organization_id' => $organization->id, 'price' => '10000.00']);
    $membership = Membership::factory()->create([
        'organization_id' => $organization->id,
        'client_id' => $client->id,
        'membership_plan_id' => $plan->id,
        'price' => '10000.00',
        'paid_amount' => 0,
        'remaining_amount' => 0,
    ]);
    switchInto($this, $owner, $organization);

    $this->post('/payments', paymentPayload($client, [
        'membership_id' => $membership->id,
        'amount' => '4000.00',
    ]))->assertRedirect();

    $membership->refresh();
    expect((string) $membership->paid_amount)->toBe('4000.00');
    expect((string) $membership->remaining_amount)->toBe('6000.00');
});

test('a membership that does not belong to the payment\'s client is rejected', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $clientA = Client::factory()->create(['organization_id' => $organization->id]);
    $clientB = Client::factory()->create(['organization_id' => $organization->id]);
    $membershipForB = Membership::factory()->create([
        'organization_id' => $organization->id,
        'client_id' => $clientB->id,
    ]);
    switchInto($this, $owner, $organization);

    $response = $this->post('/payments', paymentPayload($clientA, [
        'membership_id' => $membershipForB->id,
    ]));

    $response->assertSessionHasErrors('membership_id');
});

test('a recorded payment can be voided, and the correction reverses a linked membership balance', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $plan = MembershipPlan::factory()->create(['organization_id' => $organization->id, 'price' => '10000.00']);
    $membership = Membership::factory()->create([
        'organization_id' => $organization->id,
        'membership_plan_id' => $plan->id,
        'price' => '10000.00',
        'paid_amount' => '4000.00',
        'remaining_amount' => '6000.00',
    ]);
    $payment = Payment::factory()->create([
        'organization_id' => $organization->id,
        'client_id' => $membership->client_id,
        'membership_id' => $membership->id,
        'amount' => '4000.00',
    ]);
    switchInto($this, $owner, $organization);

    $response = $this->patch("/payments/{$payment->id}/void", ['void_reason' => 'Entered twice by mistake']);

    $response->assertRedirect();
    $payment->refresh();
    expect($payment->status->value)->toBe('voided');
    expect($payment->void_reason)->toBe('Entered twice by mistake');

    $membership->refresh();
    expect((string) $membership->paid_amount)->toBe('0.00');
    expect((string) $membership->remaining_amount)->toBe('10000.00');
});

test('voiding requires a reason', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $payment = Payment::factory()->create(['organization_id' => $organization->id]);
    switchInto($this, $owner, $organization);

    $this->patch("/payments/{$payment->id}/void", [])->assertSessionHasErrors('void_reason');
    expect($payment->fresh()->status->value)->toBe('recorded');
});

test('an already-voided payment cannot be voided again', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $payment = Payment::factory()->voided()->create(['organization_id' => $organization->id]);
    switchInto($this, $owner, $organization);

    $this->patch("/payments/{$payment->id}/void", ['void_reason' => 'again'])
        ->assertSessionHasErrors('status');
});

test('a recorded payment can be partially refunded, and the correction reverses only that amount from a linked membership', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $plan = MembershipPlan::factory()->create(['organization_id' => $organization->id, 'price' => '10000.00']);
    $membership = Membership::factory()->create([
        'organization_id' => $organization->id,
        'membership_plan_id' => $plan->id,
        'price' => '10000.00',
        'paid_amount' => '4000.00',
        'remaining_amount' => '6000.00',
    ]);
    $payment = Payment::factory()->create([
        'organization_id' => $organization->id,
        'client_id' => $membership->client_id,
        'membership_id' => $membership->id,
        'amount' => '4000.00',
    ]);
    switchInto($this, $owner, $organization);

    $response = $this->patch("/payments/{$payment->id}/refund", [
        'amount' => '1000.00',
        'refund_reason' => 'Client cancelled part of the package',
    ]);

    $response->assertRedirect();
    $payment->refresh();
    expect($payment->status->value)->toBe('refunded');
    expect((string) $payment->refunded_amount)->toBe('1000.00');

    $membership->refresh();
    expect((string) $membership->paid_amount)->toBe('3000.00');
    expect((string) $membership->remaining_amount)->toBe('7000.00');
});

test('a payment can be refunded more than once as long as the total does not exceed the original amount', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $payment = Payment::factory()->create([
        'organization_id' => $organization->id,
        'amount' => '4000.00',
    ]);
    switchInto($this, $owner, $organization);

    $this->patch("/payments/{$payment->id}/refund", [
        'amount' => '1500.00',
        'refund_reason' => 'First partial refund',
    ])->assertRedirect();

    $this->patch("/payments/{$payment->id}/refund", [
        'amount' => '1500.00',
        'refund_reason' => 'Second partial refund',
    ])->assertRedirect();

    expect((string) $payment->fresh()->refunded_amount)->toBe('3000.00');
});

test('a refund cannot exceed the original payment amount, even across multiple refunds', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $payment = Payment::factory()->create([
        'organization_id' => $organization->id,
        'amount' => '4000.00',
    ]);
    switchInto($this, $owner, $organization);

    $this->patch("/payments/{$payment->id}/refund", [
        'amount' => '3000.00',
        'refund_reason' => 'Large refund',
    ])->assertRedirect();

    $response = $this->patch("/payments/{$payment->id}/refund", [
        'amount' => '2000.00',
        'refund_reason' => 'Too much',
    ]);

    $response->assertSessionHasErrors('amount');
    expect((string) $payment->fresh()->refunded_amount)->toBe('3000.00');
});

test('a voided payment cannot be refunded', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $owner, OrganizationRole::Owner);
    $payment = Payment::factory()->voided()->create(['organization_id' => $organization->id]);
    switchInto($this, $owner, $organization);

    $this->patch("/payments/{$payment->id}/refund", [
        'amount' => '100.00',
        'refund_reason' => 'Nope',
    ])->assertSessionHasErrors('status');
});

test('staff can record payments but cannot void or refund them', function () {
    $staff = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $staff, OrganizationRole::Staff);
    $client = Client::factory()->create(['organization_id' => $organization->id]);
    switchInto($this, $staff, $organization);

    $this->post('/payments', paymentPayload($client))->assertRedirect();

    $payment = Payment::where('client_id', $client->id)->firstOrFail();

    $this->patch("/payments/{$payment->id}/void", ['void_reason' => 'Trying anyway'])
        ->assertForbidden();
    $this->patch("/payments/{$payment->id}/refund", ['amount' => '100.00', 'refund_reason' => 'Trying anyway'])
        ->assertForbidden();

    expect($payment->fresh()->status->value)->toBe('recorded');
});

test('viewer can list payments but cannot record, void, or refund them', function () {
    $viewer = User::factory()->create();
    $organization = Organization::factory()->create();
    addOrganizationMember($organization, $viewer, OrganizationRole::Viewer);
    $client = Client::factory()->create(['organization_id' => $organization->id]);
    $payment = Payment::factory()->create(['organization_id' => $organization->id]);
    switchInto($this, $viewer, $organization);

    $this->get('/payments')->assertOk();

    $this->post('/payments', paymentPayload($client))->assertForbidden();
    $this->patch("/payments/{$payment->id}/void", ['void_reason' => 'x'])->assertForbidden();
    $this->patch("/payments/{$payment->id}/refund", ['amount' => '100.00', 'refund_reason' => 'x'])->assertForbidden();
});
