<?php

namespace App\Http\Controllers;

use App\Actions\Clients\ArchiveClientAction;
use App\Actions\Clients\CreateClientAction;
use App\Actions\Clients\RestoreClientAction;
use App\Actions\Clients\UpdateClientAction;
use App\Http\Requests\Clients\StoreClientRequest;
use App\Http\Requests\Clients\UpdateClientRequest;
use App\Models\Client;
use App\Support\CurrentOrganization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Every client lookup here goes through CurrentOrganization::organizationOrFail()
 * ->clients() rather than a global Client::findOrFail(). This means a client
 * belonging to a different organization isn't just unauthorized — it's not
 * even in the query's result set, so it 404s before any policy runs. See
 * docs/SECURITY.md §5 (IDOR): "resolve through current organization" first,
 * "authorize the action" second.
 */
class ClientController extends Controller
{
    public function __construct(private CurrentOrganization $currentOrganization) {}

    public function index(Request $request): Response
    {
        $organization = $this->currentOrganization->organizationOrFail();

        Gate::authorize('viewAny', [Client::class, $organization]);

        $showArchived = $request->boolean('archived');

        $clients = $organization->clients()
            ->when($showArchived, fn ($query) => $query->onlyTrashed())
            ->search($request->string('search')->toString() ?: null)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Client $client) => $this->present($client));

        $membership = $this->currentOrganization->membership();

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
            'search' => $request->string('search')->toString(),
            'showArchived' => $showArchived,
            'canManage' => $membership?->role->canManageClients() ?? false,
            'canArchive' => $membership?->role->canManageOrganization() ?? false,
        ]);
    }

    public function create(): Response
    {
        $organization = $this->currentOrganization->organizationOrFail();

        Gate::authorize('create', [Client::class, $organization]);

        return Inertia::render('Clients/Create');
    }

    public function store(StoreClientRequest $request, CreateClientAction $action): RedirectResponse
    {
        $organization = $this->currentOrganization->organizationOrFail();

        Gate::authorize('create', [Client::class, $organization]);

        $client = $action->handle($organization, $request->validated(), $request->user());

        return redirect()
            ->route('clients.show', $client->id)
            ->with('success', "{$client->fullName()} added.");
    }

    public function show(int $client): Response
    {
        $organization = $this->currentOrganization->organizationOrFail();

        $model = $organization->clients()->withTrashed()->findOrFail($client);

        Gate::authorize('view', [$model, $organization]);

        $membership = $this->currentOrganization->membership();

        return Inertia::render('Clients/Show', [
            'client' => $this->present($model, withNotes: true),
            'canManage' => $membership?->role->canManageClients() ?? false,
            'canArchive' => $membership?->role->canManageOrganization() ?? false,
        ]);
    }

    public function edit(int $client): Response
    {
        $organization = $this->currentOrganization->organizationOrFail();

        $model = $organization->clients()->findOrFail($client);

        Gate::authorize('update', [$model, $organization]);

        return Inertia::render('Clients/Edit', [
            'client' => $this->present($model, withNotes: true),
        ]);
    }

    public function update(UpdateClientRequest $request, int $client, UpdateClientAction $action): RedirectResponse
    {
        $organization = $this->currentOrganization->organizationOrFail();

        $model = $organization->clients()->findOrFail($client);

        Gate::authorize('update', [$model, $organization]);

        $action->handle($model, $request->validated());

        return redirect()
            ->route('clients.show', $model->id)
            ->with('success', 'Client updated.');
    }

    public function destroy(int $client, ArchiveClientAction $action): RedirectResponse
    {
        $organization = $this->currentOrganization->organizationOrFail();

        $model = $organization->clients()->findOrFail($client);

        Gate::authorize('archive', [$model, $organization]);

        $action->handle($model);

        return redirect()
            ->route('clients.index')
            ->with('success', "{$model->fullName()} archived.");
    }

    public function restore(int $client, RestoreClientAction $action): RedirectResponse
    {
        $organization = $this->currentOrganization->organizationOrFail();

        $model = $organization->clients()->onlyTrashed()->findOrFail($client);

        Gate::authorize('restore', [$model, $organization]);

        $action->handle($model);

        return redirect()
            ->route('clients.show', $model->id)
            ->with('success', "{$model->fullName()} restored.");
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Client $client, bool $withNotes = false): array
    {
        return [
            'id' => $client->id,
            'first_name' => $client->first_name,
            'last_name' => $client->last_name,
            'full_name' => $client->fullName(),
            'phone' => $client->phone,
            'alternate_phone' => $client->alternate_phone,
            'email' => $client->email,
            'date_of_birth' => $client->date_of_birth?->toDateString(),
            'notes' => $withNotes ? $client->notes : null,
            'is_archived' => $client->trashed(),
            'created_at' => $client->created_at?->toDateTimeString(),
        ];
    }
}
