<?php

namespace App\Http\Requests\Memberships;

use Illuminate\Foundation\Http\FormRequest;

/**
 * client_id and membership_plan_id are deliberately not editable here —
 * reassigning a draft to a different client/plan is really "cancel this,
 * assign a new one", not an edit. Whether the edit is even allowed given
 * the membership's current status (Draft only) is enforced by
 * UpdateMembershipAction, not here — that's a business-state rule, not a
 * shape-of-input rule.
 */
class UpdateMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Real authorization happens via MembershipPolicy::update in the
        // controller, once the current organization is resolved.
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
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
