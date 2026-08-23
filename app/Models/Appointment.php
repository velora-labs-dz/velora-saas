<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * `status`, `organization_id`, `created_by`, and `cancellation_reason` are
 * intentionally excluded from Fillable — set only by trusted server code
 * (CreateAppointmentAction / CancelAppointmentAction), never derived from
 * request input directly. See docs/SECURITY.md §6 (mass assignment).
 */
#[Fillable([
    'client_id',
    'service_id',
    'employee_id',
    'starts_at',
    'ends_at',
    'notes',
])]
class Appointment extends Model
{
    /** @use HasFactory<\Database\Factories\AppointmentFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => AppointmentStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(OrganizationMember::class, 'employee_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function canTransitionTo(AppointmentStatus $target): bool
    {
        return $this->status->canTransitionTo($target);
    }
}
