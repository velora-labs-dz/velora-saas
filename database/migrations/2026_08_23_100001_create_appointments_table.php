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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('organization_id')
                ->constrained()
                ->cascadeOnDelete();

            // Clients and services are never hard-deleted (soft-delete /
            // deactivate instead), so RESTRICT here is a backstop, not
            // expected to actually block anything in practice.
            $table->foreignId('client_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('service_id')
                ->constrained()
                ->restrictOnDelete();

            // References organization_members, not a separate "employees"
            // table — docs/FOUNDATION.md's Phase 1 MVP entity list (§3)
            // has no Employee entity; the servicing staff member IS an
            // OrganizationMember with role=staff. A dedicated Employee
            // model (profiles, qualifications, availability) is
            // docs/ROADMAP.md §2.2, explicitly later scope.
            $table->foreignId('employee_id')
                ->constrained('organization_members')
                ->restrictOnDelete();

            // location_id intentionally omitted — docs/DATABASE_SCHEMA.md
            // §7 marks it "nullable until location module is active", and
            // there's no locations table yet in this schema.
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');

            // Plain string, not a DB enum type — same convention as
            // memberships.status / services.status. Values managed via
            // AppointmentStatus in PHP.
            $table->string('status')->default('scheduled');

            // How the appointment was booked. Only 'dashboard' is
            // reachable in Phase 1 (there's no client-facing booking
            // surface yet — that's docs/ROADMAP.md marketplace/booking
            // depth); the column exists now so it doesn't need a later
            // migration once online/client-initiated booking ships.
            $table->string('booking_channel')->default('dashboard');

            $table->text('notes')->nullable();
            $table->text('cancellation_reason')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('organization_id');
            // Both drive the overlap ("conflict") queries in
            // CreateAppointmentAction/UpdateAppointmentAction: one to find
            // a staff member's other bookings in a time window, the other
            // to find a client's.
            $table->index(['organization_id', 'employee_id', 'starts_at']);
            $table->index(['organization_id', 'client_id', 'starts_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
