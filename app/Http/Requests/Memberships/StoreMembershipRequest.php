<?php

namespace App\Http\Requests\Memberships;

use App\Support\CurrentOrganization;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Real authorization happens via MembershipPolicy::create in the
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
            // Scoped exists checks, not a bare `exists:clients,id` / plain
            // findOrFail — a client_id or membership_plan_id belonging to
            // another organization must fail validation here, the same way
            // ClientController resolves everything through
            // $organization->clients() rather than a global lookup. See
            // docs/SECURITY.md §5 (IDOR).
            'client_id' => [
                'required',
                'integer',
                Rule::exists('clients', 'id')
                    ->where('organization_id', $organizationId)
                    ->whereNull('deleted_at'),
            ],
            'membership_plan_id' => [
                'required',
                'integer',
                Rule::exists('membership_plans', 'id')
                    ->where('organization_id', $organizationId),
            ],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'currency' => ['required', 'string', 'size:3'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'ends_at.after' => 'The end date must be after the start date.',
        ];
    }
}
