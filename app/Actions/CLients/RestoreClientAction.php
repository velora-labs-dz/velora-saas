<?php

namespace App\Actions\Clients;

use App\Models\Client;

class RestoreClientAction
{
    /**
     * Authorization (Owner/Admin only) is the caller's responsibility via
     * ClientPolicy::restore.
     */
    public function handle(Client $client): void
    {
        $client->restore();
    }
}
