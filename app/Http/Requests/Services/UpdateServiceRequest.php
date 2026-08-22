<?php

namespace App\Http\Requests\Services;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Real authorization happens via ServicePolicy::update in the
        // controller, once the current organization and target service
        // are resolved.
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
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'currency' => ['required', 'string', 'size:3'],
            'capacity' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
