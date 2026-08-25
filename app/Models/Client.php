<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * `organization_id` and `created_by` are intentionally excluded from
 * Fillable and are set explicitly by trusted server code (see
 * CreateClientAction), never derived from request input. See
 * docs/SECURITY.md §6 (mass assignment).
 */
#[Fillable([
    'first_name',
    'last_name',
    'phone',
    'alternate_phone',
    'email',
    'date_of_birth',
    'notes',
])]
class Client extends Model
{
    /** @use HasFactory<\Database\Factories\ClientFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
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

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function fullName(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Case-insensitive substring match against name, phone, and email —
     * backs the search requirement in docs/TESTING.md §4.
     *
     * Uses whereLike(..., caseSensitive: false) rather than raw ILIKE: the
     * app runs on Postgres, but the test suite runs on SQLite (see
     * phpunit.xml), and ILIKE is Postgres-only syntax. whereLike() is
     * Laravel's driver-aware equivalent — it compiles to ILIKE on
     * Postgres and a case-insensitive LIKE on SQLite/MySQL.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $term).'%';

        return $query->where(function (Builder $query) use ($like) {
            $query->whereLike('first_name', $like)
                ->orWhereLike('last_name', $like)
                ->orWhereLike(DB::raw("(first_name || ' ' || last_name)"), $like)
                ->orWhereLike('phone', $like)
                ->orWhereLike('alternate_phone', $like)
                ->orWhereLike('email', $like);
        });
    }
}
