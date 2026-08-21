<?php

namespace App\Http\Middleware;

use App\Support\CurrentOrganization;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks the request unless ResolveCurrentOrganization already resolved a
 * verified organization context. Apply this to routes for any
 * organization-owned resource.
 */
class EnsureCurrentOrganization
{
    public function __construct(private CurrentOrganization $currentOrganization) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->currentOrganization->exists()) {
            if ($request->expectsJson()) {
                abort(403, 'No active organization selected.');
            }

            return redirect()
                ->route('organizations.index')
                ->with('error', 'Select an organization to continue.');
        }

        return $next($request);
    }
}
