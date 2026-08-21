<?php

namespace App\Http\Controllers;

use App\Actions\Organizations\CreateOrganizationAction;
use App\Http\Requests\Organizations\StoreOrganizationRequest;
use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationController extends Controller
{
    public function index(Request $request): Response
    {
        $organizations = $request->user()->organizations()
            ->orderBy('name')
            ->get()
            ->map(fn (Organization $organization) => [
                'id' => $organization->id,
                'name' => $organization->name,
                'slug' => $organization->slug,
                'status' => $organization->status->value,
                'role' => $organization->pivot->role->value,
                'is_active' => (bool) $organization->pivot->is_active,
            ]);

        return Inertia::render('Organizations/Index', [
            'organizations' => $organizations,
            'currentOrganizationId' => $request->session()->get('current_organization_id'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Organizations/Create');
    }

    public function store(StoreOrganizationRequest $request, CreateOrganizationAction $action): RedirectResponse
    {
        $organization = $action->handle($request->user(), $request->validated());

        $request->session()->put('current_organization_id', $organization->id);

        return redirect()->route('dashboard')->with('success', "{$organization->name} created.");
    }

    public function show(Request $request, Organization $organization): Response
    {
        Gate::authorize('view', $organization);

        $membership = $organization->members()
            ->where('user_id', $request->user()->id)
            ->first();

        return Inertia::render('Organizations/Show', [
            'organization' => [
                'id' => $organization->id,
                'name' => $organization->name,
                'slug' => $organization->slug,
                'status' => $organization->status->value,
            ],
            'role' => $membership->role->value,
        ]);
    }

    public function switch(Request $request, Organization $organization): RedirectResponse
    {
        Gate::authorize('switchTo', $organization);

        $request->session()->put('current_organization_id', $organization->id);

        return redirect()->route('dashboard')->with('success', "Switched to {$organization->name}.");
    }
}
