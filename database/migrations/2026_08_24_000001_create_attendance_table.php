<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Table name is singular "attendance", not "attendances" — matches
        // docs/DATABASE_SCHEMA.md §8 exactly. See the Attendance model's
        // explicit $table override.
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();

            $table->foreignId('organization_id')
                ->constrained()
                ->cascadeOnDelete();

            // Clients are never hard-deleted, so RESTRICT here is a
            // backstop, not expected to actually block anything.
            $table->foreignId('client_id')
                ->constrained()
                ->restrictOnDelete();

            $table->timestamp('check_in_at');
            // Null while the session is open. "Open" (checked in, not yet
            // checked out) is represented purely by this being null —
            // there's no separate status column, per
            // docs/DATABASE_SCHEMA.md §8's target schema.
            $table->timestamp('check_out_at')->nullable();

            // Only 'manual' is reachable in Phase 1 — docs/DATABASE_SCHEMA.md
            // §8 lists qr/barcode/rfid/device as future source values, not
            // built yet. Column exists now so it doesn't need a later
            // migration once those land.
            $table->string('source')->default('manual');

            $table->text('notes')->nullable();

            $table->foreignId('recorded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('organization_id');
            // Drives the "does this client already have an open session"
            // check in CheckInAction — the whole point of the
            // duplicate/open-session rule docs/TESTING.md §8 calls for.
            $table->index(['organization_id', 'client_id', 'check_out_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
