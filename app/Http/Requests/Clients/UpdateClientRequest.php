<?php

namespace App\Http\Requests\Clients;

use App\Support\CurrentOrganization;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Real authorization happens via ClientPolicy::update in the
        // controller, once the current organization and target client are
        // resolved.
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $organizationId = app(CurrentOrganization::class)->id();

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => [
                'required',
                'string',
                'max:32',
                Rule::unique('clients', 'phone')
                    ->where('organization_id', $organizationId)
                    ->whereNull('deleted_at')
                    ->ignore($this->route('client')),
            ],
            'alternate_phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.unique' => 'A client with this phone number already exists in this organization.',
        ];
    }
}
