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
        Schema::create('membership_plans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('organization_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');
            $table->text('description')->nullable();

            // duration_value + duration_unit describe how long one purchase
            // of this plan covers (e.g. 1 month, 12 weeks) — used to
            // compute a Membership's ends_at from its starts_at at
            // creation time. See CreateMembershipAction.
            $table->unsignedInteger('duration_value');
            $table->string('duration_unit')->default('months');

            // Monetary values as DECIMAL, never float — see
            // docs/DATABASE_SCHEMA.md §1.
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('DZD');

            // Usage caps, both optional — a plan may be unlimited-visit
            // (null) or capped (e.g. 12 sessions total, or 3 visits per
            // week). Enforcing these caps against actual attendance is
            // Step 6+ (Scheduling/Attendance) territory; the columns exist
            // now per docs/DATABASE_SCHEMA.md §6 target schema, but nothing
            // reads them yet.
            $table->unsignedInteger('sessions_limit')->nullable();
            $table->unsignedInteger('visits_per_period')->nullable();

            // Whether a Membership on this plan is allowed to freeze at
            // all. freeze_limit (how many times / how long) is stored per
            // docs/DATABASE_SCHEMA.md §6 but deliberately unenforced for
            // now — docs/ROADMAP.md §2.3 lists freeze *limits* as later
            // membership depth; only the on/off gate is live in Phase 1.
            $table->boolean('freeze_allowed')->default(true);
            $table->unsignedInteger('freeze_limit')->nullable();

            // Same convention as services.status: a boolean toggle here
            // rather than a status string, since a plan only ever has two
            // states (sellable / not), unlike Membership which has a real
            // multi-state lifecycle.
            $table->boolean('active')->default(true);

            $table->timestamps();

            $table->index('organization_id');
            $table->index(['organization_id', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_plans');
    }
};
