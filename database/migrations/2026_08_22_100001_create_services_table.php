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
        Schema::create('services', function (Blueprint $table) {
            $table->id();

            $table->foreignId('organization_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');
            $table->text('description')->nullable();

            $table->unsignedInteger('duration_minutes');

            // Monetary values as DECIMAL, never float — see
            // docs/DATABASE_SCHEMA.md §1. Same precision/currency
            // convention as organizations.currency.
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('DZD');

            // Max simultaneous clients per booking — 1 for an individual
            // service (a haircut, a massage), >1 for a group class. Null
            // is treated the same as 1 at the application layer; stored
            // nullable because "no explicit capacity set" and "capacity of
            // exactly one" are the same thing for every service until group
            // classes are actually being scheduled (Step 6+).
            $table->unsignedInteger('capacity')->nullable();

            // Plain string, not a DB enum type — same convention as
            // organizations.status. Values managed via ServiceStatus in
            // PHP. "Active/deactivate" per docs/TESTING.md §5 toggles this
            // rather than deleting the row: no deleted_at for services in
            // docs/DATABASE_SCHEMA.md §4, unlike clients.
            $table->string('status')->default('active');

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('organization_id');
            $table->index(['organization_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
