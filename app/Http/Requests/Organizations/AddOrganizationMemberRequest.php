<?php

namespace App\Http\Requests\Organizations;

use App\Enums\OrganizationRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddOrganizationMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Real authorization (who may add members, and whether the requested
        // role is allowed) happens via OrganizationPolicy::addMember in the
        // controller, once we know the organization and the requested role.
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:users,email'],
            'role' => ['required', Rule::enum(OrganizationRole::class)],
        ];
    }
}
