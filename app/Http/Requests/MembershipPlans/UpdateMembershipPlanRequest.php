<?php

namespace App\Http\Requests\MembershipPlans;

use App\Enums\DurationUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMembershipPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Real authorization happens via MembershipPlanPolicy::update in
        // the controller, once the current organization is resolved.
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'duration_value' => ['required', 'integer', 'min:1', 'max:60'],
            'duration_unit' => ['required', Rule::enum(DurationUnit::class)],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'currency' => ['required', 'string', 'size:3'],
            'sessions_limit' => ['nullable', 'integer', 'min:1'],
            'visits_per_period' => ['nullable', 'integer', 'min:1'],
            'freeze_allowed' => ['required', 'boolean'],
            'freeze_limit' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
