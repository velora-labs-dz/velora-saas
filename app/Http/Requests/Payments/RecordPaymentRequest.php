<?php

namespace App\Http\Requests\Payments;

use App\Enums\PaymentMethod;
use App\Models\Membership;
use App\Support\CurrentOrganization;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Real authorization happens via PaymentPolicy::create in the
        // controller, once the current organization is resolved.
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $organizationId = app(CurrentOrganization::class)->id();

        return [
            // Scoped exists check, not a bare `exists:clients,id` — a
            // client id belonging to another organization must fail
            // validation here. See docs/SECURITY.md §5 (IDOR).
            'client_id' => [
                'required',
                'integer',
                Rule::exists('clients', 'id')
                    ->where('organization_id', $organizationId)
                    ->whereNull('deleted_at'),
            ],
            // Optional — a payment doesn't have to be tied to a specific
            // membership. See ADR-010.
            'membership_id' => [
                'nullable',
                'integer',
                Rule::exists('memberships', 'id')
                    ->where('organization_id', $organizationId),
            ],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999.99'],
            'currency' => ['required', 'string', 'size:3'],
            'method' => ['required', Rule::enum(PaymentMethod::class)],
            'reference' => ['nullable', 'string', 'max:255'],
            'paid_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->hasAny(['client_id', 'membership_id'])) {
                return;
            }

            $membershipId = $this->input('membership_id');

            if ($membershipId === null) {
                return;
            }

            // A membership belongs to exactly one client — a payment
            // linking to a membership must be for that same client, not
            // just any client in the organization. Prevents a genuine
            // data-integrity bug, not just a cross-tenant one (both ids
            // already passed their own organization-scoped exists checks
            // above).
            $membership = Membership::query()->find($membershipId);

            if ($membership && (string) $membership->client_id !== (string) $this->input('client_id')) {
                $validator->errors()->add('membership_id', 'This membership does not belong to the selected client.');
            }
        });
    }
}
