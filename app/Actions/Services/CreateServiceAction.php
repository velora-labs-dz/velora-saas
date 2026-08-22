<?php

namespace App\Actions\Services;

use App\Enums\ServiceStatus;
use App\Models\Organization;
use App\Models\Service;
use App\Models\User;

class CreateServiceAction
{
    /**
     * @param  array<string, mixed>  $attributes  Already-validated attributes
     *                                             from StoreServiceRequest.
     */
    public function handle(Organization $organization, array $attributes, User $creator): Service
    {
        $service = new Service($attributes);
        $service->organization_id = $organization->id;
        $service->created_by = $creator->id;
        $service->status = ServiceStatus::Active;
        $service->save();

        return $service;
    }
}
