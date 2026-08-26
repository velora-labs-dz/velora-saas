<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Support\CurrentOrganization;
use Inertia\Inertia;
use Inertia\Response;

/**
 * A same-day operational snapshot — what's scheduled, who's currently
 * checked in, what's due — not a historical analytics dashboard. Read
 * models only; no writes happen here.
 *
 * Deliberately NOT behind 'current-org' middleware. /dashboard has to
 * stay reachable even when no valid organization is resolved for this
 * request — see tests/Security/TenantIsolationTest.php's "a forged
 * session organization id is never trusted as proof of membership" test,
 * which relies on GET /dashboard returning 200 right after
 * ResolveCurrentOrganization clears a forged session value. That's an
 * existing, deliberate security-test invariant; this controller adapts
 * to it (a graceful empty state) rather than the other way around.
 */
class DashboardController extends Controller
{
    public function __construct(private CurrentOrganization $currentOrganization) {}

    public function index(): Response
    {
        if (! $this->currentOrganization->exists()) {
            return Inertia::render('Dashboard', [
                'organization' => null,
            ]);
        }

        $organization = $this->currentOrganization->organizationOrFail();
        $today = now()->toDateString();

        $todaysAppointments = $organization->appointments()
            ->with(['client', 'service'])
            ->whereDate('starts_at', $today)
            ->where('status', 'scheduled')
            ->orderBy('starts_at')
            ->get()
            ->map(fn (Appointment $appointment) => [
                'id' => $appointment->id,
                'client_name' => $appointment->client->fullName(),
                'service_name' => $appointment->service->name,
                'starts_at' => $appointment->starts_at?->toIso8601String(),
            ]);

        $openAttendanceCount = $organization->attendance()
            ->whereNull('check_out_at')
            ->count();

        $todaysPaymentsTotal = $organization->payments()
            ->whereDate('paid_at', $today)
            ->where('status', 'recorded')
            ->sum('amount');

        $activeMembershipsCount = $organization->memberships()
            ->where('status', 'active')
            ->count();

        return Inertia::render('Dashboard', [
            'organization' => [
                'name' => $organization->name,
            ],
            'todaysAppointments' => $todaysAppointments,
            'openAttendanceCount' => $openAttendanceCount,
            'todaysPaymentsTotal' => (string) $todaysPaymentsTotal,
            'activeMembershipsCount' => $activeMembershipsCount,
        ]);
    }
}
