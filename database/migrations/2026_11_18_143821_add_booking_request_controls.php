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
        Schema::table('bookings', function (Blueprint $table): void {
            $table->uuid('request_token')->nullable()->unique();
            $table->unsignedSmallInteger('scheduled_duration')->nullable();
            $table->json('status_history')->nullable();
            $table->index(['preferred_date', 'preferred_time']);
        });
        Schema::table('enquiries', function (Blueprint $table): void {
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enquiries', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('service_id');
        });
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropIndex(['preferred_date', 'preferred_time']);
            $table->dropUnique(['request_token']);
            $table->dropColumn(['request_token', 'scheduled_duration', 'status_history']);
        });
    }
};
