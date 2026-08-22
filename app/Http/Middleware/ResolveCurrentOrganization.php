<?php

namespace App\Http\Middleware;

use App\Models\OrganizationMember;
use App\Support\CurrentOrganization;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the authenticated user's active organization for this request.
 *
 * The session only ever stores an organization id as a *hint* of what the
 * user last selected. It is never trusted as proof of membership. Every
 * request re-verifies that an active OrganizationMember row still exists
 * for that user/organization pair before anything is exposed as "current".
 * If it doesn't (removed, deactivated, or a forged/stale value), the hint
 * is silently dropped rather than trusted.
 */
class ResolveCurrentOrganization
{
    public function __construct(private CurrentOrganization $currentOrganization) {}

    public function handle(Request $request, Closure $next): Response
    {
        // Unconditionally reset first. CurrentOrganization is bound as a
        // singleton (see AppServiceProvider) so every downstream consumer
        // in this request sees what this middleware sets — but the same
        // instance can otherwise carry state over from a *previous*
        // request in any environment that reuses the application between
        // requests (Laravel Octane workers; sequential HTTP calls within
        // one test). Clearing first means a request that shouldn't have a
        // current organization never inherits one left over from before.
        $this->currentOrganization->clear();

        $user = $request->user();

        if ($user) {
            $organizationId = $request->session()->get('current_organization_id');

            if ($organizationId) {
                $membership = OrganizationMember::query()
                    ->with('organization')
                    ->where('organization_id', $organizationId)
                    ->where('user_id', $user->id)
                    ->where('is_active', true)
                    ->first();

                if ($membership && $membership->organization) {
                    $this->currentOrganization->set($membership->organization, $membership);
                } else {
                    $request->session()->forget('current_organization_id');
                }
            }
        }

        return $next($request);
    }
}
