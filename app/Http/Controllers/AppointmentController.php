<?php

namespace App\Http\Controllers;

use App\Actions\Appointments\CancelAppointmentAction;
use App\Actions\Appointments\CreateAppointmentAction;
use App\Actions\Appointments\UpdateAppointmentAction;
use App\Http\Requests\Appointments\CancelAppointmentRequest;
use App\Http\Requests\Appointments\StoreAppointmentRequest;
use App\Http\Requests\Appointments\UpdateAppointmentRequest;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Organization;
use App\Models\Service;
use App\Support\CurrentOrganization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Same IDOR-safe pattern as ClientController/ServiceController/
 * MembershipController: every lookup resolves through
 * CurrentOrganization::organizationOrFail()->appointments() rather than a
 * global Appointment::findOrFail(). See docs/SECURITY.md §5.
 *
 * The index is a date-filtered day list, not a drag-and-drop calendar
 * widget — docs/ROADMAP.md §2.4 lists real scheduling depth (recurring
 * appointments, staff/resource availability) as later scope, so a fuller
 * calendar UI belongs there too. This gives the same information (what's
 * booked, for whom, with whom, on a given day) without that build cost.
 */
class AppointmentController extends Controller
{
    public function __construct(private CurrentOrganization $currentOrganization) {}

    public function index(Request $request): Response
    {
        $organization = $this->currentOrganization->organizationOrFail();

        Gate::authorize('viewAny', [Appointment::class, $organization]);

        $date = $request->string('date')->toString() ?: now()->toDateString();

        $appointments = $organization->appointments()
            ->with(['client', 'service', 'employee.user'])
            ->whereDate('starts_at', $date)
            ->orderBy('starts_at')
            ->get()
            ->map(fn (Appointment $appointment) => $this->present($appointment));

        $membership = $this->currentOrganization->membership();

        return Inertia::render('Appointments/Index', [
            'appointments' => $appointments,
            'date' => $date,
            'canManage' => $membership?->role->canManageAppointments() ?? false,
        ]);
    }

    public function create(): Response
    {
        $organization = $this->currentOrganization->organizationOrFail();

        Gate::authorize('create', [Appointment::class, $organization]);

        return Inertia::render('Appointments/Create', $this->formOptions($organization));
    }

    public function store(StoreAppointmentRequest $request, CreateAppointmentAction $action): RedirectResponse
    {
        $organization = $this->currentOrganization->organizationOrFail();

        Gate::authorize('create', [Appointment::class, $organization]);

        $appointment = $action->handle($organization, $request->validated(), $request->user());

        return redirect()
            ->route('appointments.index', ['date' => $appointment->starts_at->toDateString()])
            ->with('success', 'Appointment booked.');
    }

    public function edit(int $appointment): Response
    {
        $organization = $this->currentOrganization->organizationOrFail();

        $model = $organization->appointments()->findOrFail($appointment);

        Gate::authorize('update', [$model, $organization]);

        return Inertia::render('Appointments/Edit', [
            ...$this->formOptions($organization),
            'appointment' => $this->present($model),
        ]);
    }

    public function update(UpdateAppointmentRequest $request, int $appointment, UpdateAppointmentAction $action): RedirectResponse
    {
        $organization = $this->currentOrganization->organizationOrFail();

        $model = $organization->appointments()->findOrFail($appointment);

        Gate::authorize('update', [$model, $organization]);

        $action->handle($model, $request->validated());

        return redirect()
            ->route('appointments.index', ['date' => $model->fresh()->starts_at->toDateString()])
            ->with('success', 'Appointment updated.');
    }

    public function cancel(CancelAppointmentRequest $request, int $appointment, CancelAppointmentAction $action): RedirectResponse
    {
        $organization = $this->currentOrganization->organizationOrFail();

        $model = $organization->appointments()->findOrFail($appointment);

        Gate::authorize('cancel', [$model, $organization]);

        $action->handle($model, $request->validated()['cancellation_reason'] ?? null);

        return redirect()
            ->route('appointments.index', ['date' => $model->starts_at->toDateString()])
            ->with('success', 'Appointment cancelled.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(Organization $organization): array
    {
        return [
            'clients' => $organization->clients()
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get()
                ->map(fn (Client $client) => [
                    'id' => $client->id,
                    'full_name' => $client->fullName(),
                ]),
            'services' => $organization->services()
                ->where('status', 'active')
                ->orderBy('name')
                ->get()
                ->map(fn (Service $service) => [
                    'id' => $service->id,
                    'name' => $service->name,
                    'duration_minutes' => $service->duration_minutes,
                ]),
            'staff' => $organization->members()
                ->with('user')
                ->where('role', 'staff')
                ->where('is_active', true)
                ->get()
                ->map(fn (\App\Models\OrganizationMember $member) => [
                    'id' => $member->id,
                    'name' => $member->user->name,
                ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Appointment $appointment): array
    {
        return [
            'id' => $appointment->id,
            'status' => $appointment->status->value,
            'status_label' => $appointment->status->label(),
            'client' => [
                'id' => $appointment->client->id,
                'full_name' => $appointment->client->fullName(),
            ],
            'service' => [
                'id' => $appointment->service->id,
                'name' => $appointment->service->name,
            ],
            'employee' => [
                'id' => $appointment->employee->id,
                'name' => $appointment->employee->user->name,
            ],
            'starts_at' => $appointment->starts_at?->toIso8601String(),
            'ends_at' => $appointment->ends_at?->toIso8601String(),
            'notes' => $appointment->notes,
            'cancellation_reason' => $appointment->cancellation_reason,
        ];
    }
}
