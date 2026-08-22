<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();

            $table->foreignId('organization_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('first_name');
            $table->string('last_name');

            // Phone is the primary contact/identification field for Phase 1 —
            // service businesses in this market reliably collect it, unlike
            // email. It is the field "duplicate handling" (TESTING.md §4) is
            // enforced against; see the unique index below.
            $table->string('phone');
            $table->string('alternate_phone')->nullable();
            $table->string('email')->nullable();

            $table->date('date_of_birth')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            // Archiving a client is a soft delete. There is no permanent
            // delete exposed in Phase 1: clients will accrue memberships,
            // appointments, attendance and payments in later steps, and
            // those histories must not be able to lose their client
            // reference out from under them. See docs/SECURITY.md §9
            // (immutable history) — the same principle applied one step
            // early, before financial records exist yet.
            $table->softDeletes();

            // A phone number identifies one client within an organization.
            // Soft-deleted (archived) rows are excluded further down by
            // application-level validation so an archived client's phone
            // can be reused for a new registration; the DB-level unique
            // index still applies to live rows only via the partial index
            // below (Postgres-specific), matching the org+user pattern
            // already used for organization_members.
            $table->index('organization_id');
            $table->index(['organization_id', 'deleted_at']);
        });

        // Postgres partial unique index: only enforced while the client is
        // not archived, so archiving frees the phone number up for reuse.
        // whereNull('deleted_at') in the validation layer mirrors this.
        DB::statement(
            'create unique index clients_organization_id_phone_active_unique '.
            'on clients (organization_id, phone) where deleted_at is null',
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
