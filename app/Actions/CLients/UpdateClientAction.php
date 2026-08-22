<?php

namespace App\Actions\Clients;

use App\Models\Client;

class UpdateClientAction
{
    /**
     * @param  array<string, mixed>  $attributes  Already-validated attributes
     *                                             from UpdateClientRequest.
     */
    public function handle(Client $client, array $attributes): Client
    {
        $client->fill($attributes);
        $client->save();

        return $client;
    }
}
