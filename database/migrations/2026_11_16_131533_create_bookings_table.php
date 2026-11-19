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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('service_id')->constrained();
            $table->foreignId('price_package_id')->nullable()->constrained()->nullOnDelete();
            $table->string('package_name');
            $table->unsignedInteger('listed_price');
            $table->string('customer_name');
            $table->string('phone', 30);
            $table->string('email')->nullable();
            $table->string('location');
            $table->date('preferred_date')->nullable();
            $table->string('preferred_time')->nullable();
            $table->text('instructions')->nullable();
            $table->string('pickup_location')->nullable();
            $table->string('destination')->nullable();
            $table->string('status')->default('New')->index();
            $table->text('internal_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
