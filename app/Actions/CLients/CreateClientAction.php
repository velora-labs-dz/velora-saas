<?php

namespace App\Actions\Clients;

use App\Models\Client;
use App\Models\Organization;
use App\Models\User;

class CreateClientAction
{
    /**
     * @param  array<string, mixed>  $attributes  Already-validated attributes
     *                                             from StoreClientRequest.
     */
    public function handle(Organization $organization, array $attributes, User $creator): Client
    {
        $client = new Client($attributes);
        $client->organization_id = $organization->id;
        $client->created_by = $creator->id;
        $client->save();

        return $client;
    }
}
