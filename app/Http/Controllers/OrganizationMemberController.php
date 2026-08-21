<?php

namespace App\Http\Controllers;

use App\Actions\Organizations\AddOrganizationMemberAction;
use App\Actions\Organizations\RemoveOrganizationMemberAction;
use App\Actions\Organizations\UpdateOrganizationMemberRoleAction;
use App\Enums\OrganizationRole;
use App\Http\Requests\Organizations\AddOrganizationMemberRequest;
use App\Http\Requests\Organizations\UpdateOrganizationMemberRoleRequest;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationMemberController extends Controller
{
    public function index(Request $request, Organization $organization): Response
    {
        Gate::authorize('viewMembers', $organization);

        $viewerMembership = $organization->members()
            ->where('user_id', $request->user()->id)
            ->first();

        $members = $organization->members()
            ->with('user:id,name,email')
            ->orderBy('created_at')
            ->get()
            ->map(fn (OrganizationMember $member) => [
                'id' => $member->id,
                'name' => $member->user->name,
                'email' => $member->user->email,
                'role' => $member->role->value,
                'is_active' => $member->is_active,
                'joined_at' => $member->joined_at,
                'is_you' => $member->user_id === $request->user()->id,
            ]);

        return Inertia::render('Organizations/Members', [
            'organization' => [
                'id' => $organization->id,
                'name' => $organization->name,
                'slug' => $organization->slug,
            ],
            'members' => $members,
            'canManage' => $viewerMembership?->role->canManageOrganization() ?? false,
            'viewerIsOwner' => $viewerMembership?->role->isOwner() ?? false,
        ]);
    }

    public function store(
        AddOrganizationMemberRequest $request,
        Organization $organization,
        AddOrganizationMemberAction $action,
    ): RedirectResponse {
        $role = OrganizationRole::from($request->validated('role'));

        Gate::authorize('addMember', [$organization, $role]);

        $targetUser = User::where('email', $request->validated('email'))->firstOrFail();

        $action->handle($organization, $targetUser, $role);

        return back()->with('success', "{$targetUser->name} added to {$organization->name}.");
    }

    public function update(
        UpdateOrganizationMemberRoleRequest $request,
        Organization $organization,
        OrganizationMember $member,
        UpdateOrganizationMemberRoleAction $action,
    ): RedirectResponse {
        $newRole = OrganizationRole::from($request->validated('role'));

        Gate::authorize('updateMemberRole', [$organization, $member, $newRole]);

        $action->handle($organization, $member, $newRole);

        return back()->with('success', 'Role updated.');
    }

    public function destroy(
        Organization $organization,
        OrganizationMember $member,
        RemoveOrganizationMemberAction $action,
    ): RedirectResponse {
        Gate::authorize('removeMember', [$organization, $member]);

        $action->handle($organization, $member);

        return back()->with('success', 'Member removed.');
    }

    public function leave(
        Request $request,
        Organization $organization,
        RemoveOrganizationMemberAction $action,
    ): RedirectResponse {
        $member = $organization->members()
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        Gate::authorize('leaveOrganization', [$organization, $member]);

        $action->handle($organization, $member);

        return redirect()->route('organizations.index')->with('success', "You left {$organization->name}.");
    }
}
