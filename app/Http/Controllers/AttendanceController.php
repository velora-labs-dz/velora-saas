<?php

namespace App\Http\Controllers;

use App\Actions\Attendance\CheckInAction;
use App\Actions\Attendance\CheckOutAction;
use App\Http\Requests\Attendance\CheckInRequest;
use App\Models\Attendance;
use App\Models\Client;
use App\Support\CurrentOrganization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Same IDOR-safe pattern as every controller since Clients: every lookup
 * resolves through CurrentOrganization::organizationOrFail()->attendance()
 * rather than a global Attendance::findOrFail(). See docs/SECURITY.md §5.
 *
 * Deliberately no create/edit pages — check-in is a single quick action
 * (pick a client, optionally add a note) from the index itself, and
 * check-out is a one-click action on an open row. Matches the "manual
 * history" scope in docs/ROADMAP.md §Step 7; there's nothing here that
 * warrants its own page the way Appointments' longer form did.
 */
class AttendanceController extends Controller
{
    public function __construct(private CurrentOrganization $currentOrganization) {}

    public function index(Request $request): Response
    {
        $organization = $this->currentOrganization->organizationOrFail();

        Gate::authorize('viewAny', [Attendance::class, $organization]);

        $date = $request->string('date')->toString() ?: now()->toDateString();

        $records = $organization->attendance()
            ->with('client')
            ->whereDate('check_in_at', $date)
            ->orderByDesc('check_in_at')
            ->get()
            ->map(fn (Attendance $attendance) => $this->present($attendance));

        $membership = $this->currentOrganization->membership();

        return Inertia::render('Attendance/Index', [
            'records' => $records,
            'date' => $date,
            'canManage' => $membership?->role->canManageAttendance() ?? false,
            'clients' => $organization->clients()
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get()
                ->map(fn (Client $client) => [
                    'id' => $client->id,
                    'full_name' => $client->fullName(),
                ]),
        ]);
    }

    public function checkIn(CheckInRequest $request, CheckInAction $action): RedirectResponse
    {
        $organization = $this->currentOrganization->organizationOrFail();

        Gate::authorize('checkIn', [Attendance::class, $organization]);

        $attendance = $action->handle($organization, $request->validated(), $request->user());

        return redirect()
            ->route('attendance.index', ['date' => $attendance->check_in_at->toDateString()])
            ->with('success', 'Checked in.');
    }

    public function checkOut(int $attendance, CheckOutAction $action): RedirectResponse
    {
        $organization = $this->currentOrganization->organizationOrFail();

        $model = $organization->attendance()->findOrFail($attendance);

        Gate::authorize('checkOut', [$model, $organization]);

        $action->handle($model);

        return redirect()
            ->route('attendance.index', ['date' => $model->check_in_at->toDateString()])
            ->with('success', 'Checked out.');
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Attendance $attendance): array
    {
        return [
            'id' => $attendance->id,
            'client' => [
                'id' => $attendance->client->id,
                'full_name' => $attendance->client->fullName(),
            ],
            'check_in_at' => $attendance->check_in_at?->toIso8601String(),
            'check_out_at' => $attendance->check_out_at?->toIso8601String(),
            'is_open' => $attendance->isOpen(),
            'notes' => $attendance->notes,
        ];
    }
}
