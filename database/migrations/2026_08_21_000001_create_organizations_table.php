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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            // Public-facing identifier used in URLs instead of exposing the numeric id.
            $table->string('slug')->unique();
            $table->string('legal_name')->nullable();

            $table->string('timezone')->default('Africa/Algiers');
            $table->string('locale', 5)->default('fr');
            $table->string('currency', 3)->default('DZD');

            // Business status of the organization itself (not membership status).
            $table->string('status')->default('active');

            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();

            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('wilaya')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country_code', 2)->default('DZ');

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
