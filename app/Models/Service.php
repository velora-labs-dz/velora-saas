<?php

namespace App\Models;

use App\Enums\ServiceStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * `status`, `organization_id`, and `created_by` are intentionally excluded
 * from Fillable and are set explicitly by trusted server code, never
 * derived from request input. See docs/SECURITY.md §6 (mass assignment).
 */
#[Fillable([
    'name',
    'description',
    'duration_minutes',
    'price',
    'currency',
    'capacity',
])]
class Service extends Model
{
    /** @use HasFactory<\Database\Factories\ServiceFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => ServiceStatus::class,
            'duration_minutes' => 'integer',
            'price' => 'decimal:2',
            'capacity' => 'integer',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function isActive(): bool
    {
        return $this->status === ServiceStatus::Active;
    }
}
