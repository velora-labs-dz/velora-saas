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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('organization_id')
                ->constrained()
                ->cascadeOnDelete();

            // Clients are never hard-deleted, so RESTRICT here is a
            // backstop, not expected to actually block anything.
            $table->foreignId('client_id')
                ->constrained()
                ->restrictOnDelete();

            // Nullable — a payment doesn't have to be tied to a specific
            // membership (e.g. a one-off service payment). When it is,
            // CreateAppointmentAction-style balance syncing keeps
            // Membership.paid_amount/remaining_amount up to date. See
            // ADR-010; this column and refunded_amount below are both
            // deviations from docs/DATABASE_SCHEMA.md's original §9 draft,
            // now reconciled — the doc has been updated to match.
            $table->foreignId('membership_id')
                ->nullable()
                ->constrained()
                ->restrictOnDelete();

            // DECIMAL, never float, per docs/DATABASE_SCHEMA.md §1.
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('DZD');

            // Plain string, not a DB enum type — same convention as every
            // other status/method column in this schema. Values managed
            // via PaymentMethod/PaymentStatus in PHP.
            $table->string('method')->default('cash');
            $table->string('status')->default('recorded');

            // e.g. a bank transfer reference number. Optional — cash
            // payments won't have one.
            $table->string('reference')->nullable();

            $table->timestamp('paid_at');

            // Cumulative amount refunded so far — 0 unless status is
            // refunded. A single payment can be partially refunded more
            // than once (this grows each time), up to the original
            // amount. See PaymentStatus.
            $table->decimal('refunded_amount', 10, 2)->default(0);
            $table->text('refund_reason')->nullable();

            $table->timestamp('voided_at')->nullable();
            $table->text('void_reason')->nullable();

            $table->text('notes')->nullable();

            $table->foreignId('recorded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('organization_id');
            $table->index(['organization_id', 'client_id']);
            $table->index(['organization_id', 'membership_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
