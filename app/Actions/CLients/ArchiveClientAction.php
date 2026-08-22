<?php

namespace App\Actions\Clients;

use App\Models\Client;

class ArchiveClientAction
{
    /**
     * Soft-delete the client. There is no permanent delete in Phase 1 —
     * see the migration for why. Authorization (Owner/Admin only) is the
     * caller's responsibility via ClientPolicy::archive.
     */
    public function handle(Client $client): void
    {
        $client->delete();
    }
}
