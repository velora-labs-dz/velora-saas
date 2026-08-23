<?php

namespace App\Http\Controllers;

use App\Actions\Memberships\ActivateMembershipAction;
use App\Actions\Memberships\CancelMembershipAction;
use App\Actions\Memberships\CreateMembershipAction;
use App\Actions\Memberships\ExpireMembershipAction;
use App\Actions\Memberships\FreezeMembershipAction;
use App\Actions\Memberships\UnfreezeMembershipAction;
use App\Actions\Memberships\UpdateMembershipAction;
use App\Http\Requests\Memberships\CancelMembershipRequest;
use App\Http\Requests\Memberships\StoreMembershipRequest;
use App\Http\Requests\Memberships\UpdateMembershipRequest;
use App\Models\Client;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Support\CurrentOrganization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Same IDOR-safe pattern as ClientController/ServiceController: every
 * lookup resolves through CurrentOrganization::organizationOrFail()
 * ->memberships() rather than a global Membership::findOrFail(). See
 * docs/SECURITY.md §5.
 *
 * Transition endpoints (activate/freeze/unfreeze/cancel/expire) are
 * deliberately separate routes/methods rather than a generic
 * "PATCH status" — each has its own policy method and its own Action, so
 * the set of legal callers and the set of legal current-states can differ
 * per transition (e.g. cancel is Owner/Admin-only; freeze also checks the
 * plan's freeze_allowed). See MembershipPolicy, MembershipStatus.
 */
class MembershipController extends Controller
{
    public function __construct(private CurrentOrganization $currentOrganization) {}

    public function index(Request $request): Response
    {
        $organization = $this->currentOrganization->organizationOrFail();

        Gate::authorize('viewAny', [Membership::class, $organization]);

        $status = $request->string('status')->toString();

        $memberships = $organization->memberships()
            ->with(['client', 'membershipPlan'])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Membership $membership) => $this->present($membership));

        $membership = $this->currentOrganization->membership();

        return Inertia::render('Memberships/Index', [
            'memberships' => $memberships,
            'status' => $status,
            'canManage' => $membership?->role->canManageMemberships() ?? false,
        ]);
    }

    public function create(): Response
    {
        $organization = $this->currentOrganization->organizationOrFail();

        Gate::authorize('create', [Membership::class, $organization]);

        return Inertia::render('Memberships/Create', [
            'clients' => $organization->clients()
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get()
                ->map(fn (Client $client) => [
                    'id' => $client->id,
                    'full_name' => $client->fullName(),
                ]),
            'plans' => $organization->membershipPlans()
                ->where('active', true)
                ->orderBy('name')
                ->get()
                ->map(fn (MembershipPlan $plan) => [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'duration_value' => $plan->duration_value,
                    'duration_unit' => $plan->duration_unit->value,
                    'price' => (string) $plan->price,
                    'currency' => $plan->currency,
                    'freeze_allowed' => $plan->freeze_allowed,
                ]),
        ]);
    }

    public function store(StoreMembershipRequest $request, CreateMembershipAction $action): RedirectResponse
    {
        $organization = $this->currentOrganization->organizationOrFail();

        Gate::authorize('create', [Membership::class, $organization]);

        $membership = $action->handle($organization, $request->validated(), $request->user());

        return redirect()
            ->route('memberships.show', $membership->id)
            ->with('success', 'Membership assigned as draft.');
    }

    public function show(int $membership): Response
    {
        $organization = $this->currentOrganization->organizationOrFail();

        $model = $organization->memberships()->with(['client', 'membershipPlan'])->findOrFail($membership);

        Gate::authorize('view', [$model, $organization]);

        $actorMembership = $this->currentOrganization->membership();
        $role = $actorMembership?->role;

        return Inertia::render('Memberships/Show', [
            'membership' => $this->present($model),
            'canManage' => $role?->canManageMemberships() ?? false,
            'canCancel' => $role?->canCancelMemberships() ?? false,
        ]);
    }

    public function edit(int $membership): Response
    {
        $organization = $this->currentOrganization->organizationOrFail();

        $model = $organization->memberships()->with(['client', 'membershipPlan'])->findOrFail($membership);

        Gate::authorize('update', [$model, $organization]);

        return Inertia::render('Memberships/Edit', [
            'membership' => $this->present($model),
        ]);
    }

    public function update(UpdateMembershipRequest $request, int $membership, UpdateMembershipAction $action): RedirectResponse
    {
        $organization = $this->currentOrganization->organizationOrFail();

        $model = $organization->memberships()->findOrFail($membership);

        Gate::authorize('update', [$model, $organization]);

        $action->handle($model, $request->validated());

        return redirect()
            ->route('memberships.show', $model->id)
            ->with('success', 'Membership updated.');
    }

    public function activate(int $membership, ActivateMembershipAction $action): RedirectResponse
    {
        $organization = $this->currentOrganization->organizationOrFail();

        $model = $organization->memberships()->findOrFail($membership);

        Gate::authorize('activate', [$model, $organization]);

        $action->handle($model);

        return redirect()
            ->route('memberships.show', $model->id)
            ->with('success', 'Membership activated.');
    }

    public function freeze(int $membership, FreezeMembershipAction $action): RedirectResponse
    {
        $organization = $this->currentOrganization->organizationOrFail();

        $model = $organization->memberships()->findOrFail($membership);

        Gate::authorize('freeze', [$model, $organization]);

        $action->handle($model);

        return redirect()
            ->route('memberships.show', $model->id)
            ->with('success', 'Membership frozen.');
    }

    public function unfreeze(int $membership, UnfreezeMembershipAction $action): RedirectResponse
    {
        $organization = $this->currentOrganization->organizationOrFail();

        $model = $organization->memberships()->findOrFail($membership);

        Gate::authorize('unfreeze', [$model, $organization]);

        $action->handle($model);

        return redirect()
            ->route('memberships.show', $model->id)
            ->with('success', 'Membership unfrozen.');
    }

    public function cancel(CancelMembershipRequest $request, int $membership, CancelMembershipAction $action): RedirectResponse
    {
        $organization = $this->currentOrganization->organizationOrFail();

        $model = $organization->memberships()->findOrFail($membership);

        Gate::authorize('cancel', [$model, $organization]);

        $action->handle($model, $request->validated()['cancellation_reason']);

        return redirect()
            ->route('memberships.show', $model->id)
            ->with('success', 'Membership cancelled.');
    }

    public function expire(int $membership, ExpireMembershipAction $action): RedirectResponse
    {
        $organization = $this->currentOrganization->organizationOrFail();

        $model = $organization->memberships()->findOrFail($membership);

        Gate::authorize('expire', [$model, $organization]);

        $action->handle($model);

        return redirect()
            ->route('memberships.show', $model->id)
            ->with('success', 'Membership marked expired.');
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Membership $membership): array
    {
        return [
            'id' => $membership->id,
            'status' => $membership->status->value,
            'status_label' => $membership->status->label(),
            'client' => [
                'id' => $membership->client->id,
                'full_name' => $membership->client->fullName(),
            ],
            'plan' => [
                'id' => $membership->membershipPlan->id,
                'name' => $membership->membershipPlan->name,
                'freeze_allowed' => $membership->membershipPlan->freeze_allowed,
            ],
            'starts_at' => $membership->starts_at?->toDateString(),
            'ends_at' => $membership->ends_at?->toDateString(),
            'price' => (string) $membership->price,
            'currency' => $membership->currency,
            'paid_amount' => (string) $membership->paid_amount,
            'remaining_amount' => (string) $membership->remaining_amount,
            'notes' => $membership->notes,
            'activated_at' => $membership->activated_at?->toDateTimeString(),
            'frozen_at' => $membership->frozen_at?->toDateTimeString(),
            'cancelled_at' => $membership->cancelled_at?->toDateTimeString(),
            'cancellation_reason' => $membership->cancellation_reason,
            'created_at' => $membership->created_at?->toDateTimeString(),
        ];
    }
}
