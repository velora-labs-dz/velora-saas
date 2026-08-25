<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * `organization_id` and `recorded_by` are intentionally excluded from
 * Fillable and set explicitly by trusted server code (CheckInAction /
 * CheckOutAction), never derived from request input. See
 * docs/SECURITY.md §6 (mass assignment). `check_out_at` is also excluded
 * — it's written only by CheckOutAction, never by a generic update (there
 * is no generic update; attendance rows aren't editable once created,
 * only closed).
 */
#[Fillable([
    'client_id',
    'check_in_at',
    'source',
    'notes',
])]
class Attendance extends Model
{
    /** @use HasFactory<\Database\Factories\AttendanceFactory> */
    use HasFactory;

    // Matches docs/DATABASE_SCHEMA.md §8's table name exactly — "attendance",
    // not the Eloquent-default pluralized "attendances".
    protected $table = 'attendance';

    protected function casts(): array
    {
        return [
            'check_in_at' => 'datetime',
            'check_out_at' => 'datetime',
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

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function isOpen(): bool
    {
        return $this->check_out_at === null;
    }
}
