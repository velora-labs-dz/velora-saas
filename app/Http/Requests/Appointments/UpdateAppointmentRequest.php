<?php

namespace App\Http\Requests\Appointments;

use App\Http\Requests\Appointments\Concerns\ValidatesAppointmentConflicts;
use App\Support\CurrentOrganization;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppointmentRequest extends FormRequest
{
    use ValidatesAppointmentConflicts;

    public function authorize(): bool
    {
        // Real authorization happens via AppointmentPolicy::update in the
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
            // Scoped exists checks, not a bare `exists:table,id` — an id
            // belonging to another organization must fail validation
            // here, the same way ClientController/MembershipController
            // resolve everything through the current organization rather
            // than a global lookup. See docs/SECURITY.md §5 (IDOR).
            'client_id' => [
                'required',
                'integer',
                Rule::exists('clients', 'id')
                    ->where('organization_id', $organizationId)
                    ->whereNull('deleted_at'),
            ],
            'service_id' => [
                'required',
                'integer',
                Rule::exists('services', 'id')
                    ->where('organization_id', $organizationId),
            ],
            // Only organization_members with role=staff are eligible —
            // Owner/Admin aren't valid appointment assignees even though
            // they're allowed to manage appointments.
            'employee_id' => [
                'required',
                'integer',
                Rule::exists('organization_members', 'id')
                    ->where('organization_id', $organizationId)
                    ->where('role', 'staff')
                    ->where('is_active', true),
            ],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'ends_at.after' => 'The end time must be after the start time.',
        ];
    }
}
