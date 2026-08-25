<?php

namespace App\Http\Requests\Attendance;

use App\Support\CurrentOrganization;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckInRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Real authorization happens via AttendancePolicy::checkIn in the
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
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
