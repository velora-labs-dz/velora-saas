<?php

namespace App\Actions\Services;

use App\Enums\ServiceStatus;
use App\Models\Service;

class ToggleServiceStatusAction
{
    public function handle(Service $service): Service
    {
        $service->status = $service->isActive()
            ? ServiceStatus::Inactive
            : ServiceStatus::Active;
        $service->save();

        return $service;
    }
}
