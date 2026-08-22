<?php

namespace App\Actions\Services;

use App\Models\Service;

class UpdateServiceAction
{
    /**
     * @param  array<string, mixed>  $attributes  Already-validated attributes
     *                                             from UpdateServiceRequest.
     */
    public function handle(Service $service, array $attributes): Service
    {
        $service->fill($attributes);
        $service->save();

        return $service;
    }
}
