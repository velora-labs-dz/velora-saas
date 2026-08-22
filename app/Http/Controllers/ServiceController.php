<?php

namespace App\Http\Controllers;

use App\Actions\Services\CreateServiceAction;
use App\Actions\Services\ToggleServiceStatusAction;
use App\Actions\Services\UpdateServiceAction;
use App\Http\Requests\Services\StoreServiceRequest;
use App\Http\Requests\Services\UpdateServiceRequest;
use App\Models\Service;
use App\Support\CurrentOrganization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Same IDOR-safe pattern as ClientController: every lookup resolves
 * through CurrentOrganization::organizationOrFail()->services() rather
 * than a global Service::findOrFail(). See docs/SECURITY.md §5.
 */
class ServiceController extends Controller
{
    public function __construct(private CurrentOrganization $currentOrganization) {}

    public function index(Request $request): Response
    {
        $organization = $this->currentOrganization->organizationOrFail();

        Gate::authorize('viewAny', [Service::class, $organization]);

        $showInactive = $request->boolean('inactive');

        $services = $organization->services()
            ->when(! $showInactive, fn ($query) => $query->where('status', 'active'))
            ->orderBy('name')
            ->get()
            ->map(fn (Service $service) => $this->present($service));

        $membership = $this->currentOrganization->membership();

        return Inertia::render('Services/Index', [
            'services' => $services,
            'showInactive' => $showInactive,
            'canManage' => $membership?->role->canManageServices() ?? false,
        ]);
    }

    public function create(): Response
    {
        $organization = $this->currentOrganization->organizationOrFail();

        Gate::authorize('create', [Service::class, $organization]);

        return Inertia::render('Services/Create', [
            'defaultCurrency' => $organization->currency,
        ]);
    }

    public function store(StoreServiceRequest $request, CreateServiceAction $action): RedirectResponse
    {
        $organization = $this->currentOrganization->organizationOrFail();

        Gate::authorize('create', [Service::class, $organization]);

        $service = $action->handle($organization, $request->validated(), $request->user());

        return redirect()
            ->route('services.index')
            ->with('success', "{$service->name} added.");
    }

    public function edit(int $service): Response
    {
        $organization = $this->currentOrganization->organizationOrFail();

        $model = $organization->services()->findOrFail($service);

        Gate::authorize('update', [$model, $organization]);

        return Inertia::render('Services/Edit', [
            'service' => $this->present($model),
        ]);
    }

    public function update(UpdateServiceRequest $request, int $service, UpdateServiceAction $action): RedirectResponse
    {
        $organization = $this->currentOrganization->organizationOrFail();

        $model = $organization->services()->findOrFail($service);

        Gate::authorize('update', [$model, $organization]);

        $action->handle($model, $request->validated());

        return redirect()
            ->route('services.index')
            ->with('success', 'Service updated.');
    }

    public function toggleStatus(int $service, ToggleServiceStatusAction $action): RedirectResponse
    {
        $organization = $this->currentOrganization->organizationOrFail();

        $model = $organization->services()->findOrFail($service);

        Gate::authorize('toggleStatus', [$model, $organization]);

        $action->handle($model);

        return redirect()
            ->route('services.index')
            ->with('success', $model->isActive() ? "{$model->name} activated." : "{$model->name} deactivated.");
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Service $service): array
    {
        return [
            'id' => $service->id,
            'name' => $service->name,
            'description' => $service->description,
            'duration_minutes' => $service->duration_minutes,
            'price' => (string) $service->price,
            'currency' => $service->currency,
            'capacity' => $service->capacity,
            'status' => $service->status->value,
            'is_active' => $service->isActive(),
        ];
    }
}
