<?php

namespace App\Http\Controllers;

use App\Actions\MembershipPlans\CreateMembershipPlanAction;
use App\Actions\MembershipPlans\ToggleMembershipPlanStatusAction;
use App\Actions\MembershipPlans\UpdateMembershipPlanAction;
use App\Http\Requests\MembershipPlans\StoreMembershipPlanRequest;
use App\Http\Requests\MembershipPlans\UpdateMembershipPlanRequest;
use App\Models\MembershipPlan;
use App\Support\CurrentOrganization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Same IDOR-safe pattern as ClientController/ServiceController: every
 * lookup resolves through
 * CurrentOrganization::organizationOrFail()->membershipPlans() rather than
 * a global MembershipPlan::findOrFail(). See docs/SECURITY.md §5.
 */
class MembershipPlanController extends Controller
{
    public function __construct(private CurrentOrganization $currentOrganization) {}

    public function index(Request $request): Response
    {
        $organization = $this->currentOrganization->organizationOrFail();

        Gate::authorize('viewAny', [MembershipPlan::class, $organization]);

        $showInactive = $request->boolean('inactive');

        $plans = $organization->membershipPlans()
            ->when(! $showInactive, fn ($query) => $query->where('active', true))
            ->orderBy('name')
            ->get()
            ->map(fn (MembershipPlan $plan) => $this->present($plan));

        $membership = $this->currentOrganization->membership();

        return Inertia::render('MembershipPlans/Index', [
            'plans' => $plans,
            'showInactive' => $showInactive,
            'canManage' => $membership?->role->canManageMemberships() ?? false,
        ]);
    }

    public function create(): Response
    {
        $organization = $this->currentOrganization->organizationOrFail();

        Gate::authorize('create', [MembershipPlan::class, $organization]);

        return Inertia::render('MembershipPlans/Create', [
            'defaultCurrency' => $organization->currency,
        ]);
    }

    public function store(StoreMembershipPlanRequest $request, CreateMembershipPlanAction $action): RedirectResponse
    {
        $organization = $this->currentOrganization->organizationOrFail();

        Gate::authorize('create', [MembershipPlan::class, $organization]);

        $plan = $action->handle($organization, $request->validated());

        return redirect()
            ->route('membership-plans.index')
            ->with('success', "{$plan->name} added.");
    }

    public function edit(int $membershipPlan): Response
    {
        $organization = $this->currentOrganization->organizationOrFail();

        $model = $organization->membershipPlans()->findOrFail($membershipPlan);

        Gate::authorize('update', [$model, $organization]);

        return Inertia::render('MembershipPlans/Edit', [
            'plan' => $this->present($model),
        ]);
    }

    public function update(UpdateMembershipPlanRequest $request, int $membershipPlan, UpdateMembershipPlanAction $action): RedirectResponse
    {
        $organization = $this->currentOrganization->organizationOrFail();

        $model = $organization->membershipPlans()->findOrFail($membershipPlan);

        Gate::authorize('update', [$model, $organization]);

        $action->handle($model, $request->validated());

        return redirect()
            ->route('membership-plans.index')
            ->with('success', 'Plan updated.');
    }

    public function toggleStatus(int $membershipPlan, ToggleMembershipPlanStatusAction $action): RedirectResponse
    {
        $organization = $this->currentOrganization->organizationOrFail();

        $model = $organization->membershipPlans()->findOrFail($membershipPlan);

        Gate::authorize('toggleStatus', [$model, $organization]);

        $action->handle($model);

        return redirect()
            ->route('membership-plans.index')
            ->with('success', $model->active ? "{$model->name} activated." : "{$model->name} deactivated.");
    }

    /**
     * @return array<string, mixed>
     */
    private function present(MembershipPlan $plan): array
    {
        return [
            'id' => $plan->id,
            'name' => $plan->name,
            'description' => $plan->description,
            'duration_value' => $plan->duration_value,
            'duration_unit' => $plan->duration_unit->value,
            'price' => (string) $plan->price,
            'currency' => $plan->currency,
            'sessions_limit' => $plan->sessions_limit,
            'visits_per_period' => $plan->visits_per_period,
            'freeze_allowed' => $plan->freeze_allowed,
            'freeze_limit' => $plan->freeze_limit,
            'active' => $plan->active,
        ];
    }
}
