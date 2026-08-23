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
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();

            $table->foreignId('organization_id')
                ->constrained()
                ->cascadeOnDelete();

            // Clients are never hard-deleted (see clients migration), so
            // the default RESTRICT-on-delete here is intentional — it's a
            // backstop, not expected to ever actually block anything.
            $table->foreignId('client_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('membership_plan_id')
                ->constrained()
                ->restrictOnDelete();

            // Plain string, not a DB enum type — same convention as
            // services.status. Values managed via MembershipStatus in PHP;
            // legal transitions between them live in
            // MembershipStatus::allowedTransitions(), not here.
            $table->string('status')->default('draft');

            $table->date('starts_at');
            $table->date('ends_at');

            // price/currency are a snapshot of the plan's values at the
            // moment this membership was created, not a live reference —
            // same "frozen at assignment" pattern used for appointment
            // pricing in the Style Le Club project this one continues
            // from. If the plan's price changes later, existing
            // memberships must not silently reprice.
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('DZD');

            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('remaining_amount', 10, 2)->default(0);

            $table->text('notes')->nullable();

            $table->timestamp('activated_at')->nullable();
            $table->timestamp('frozen_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('organization_id');
            $table->index(['organization_id', 'status']);
            $table->index('client_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};
