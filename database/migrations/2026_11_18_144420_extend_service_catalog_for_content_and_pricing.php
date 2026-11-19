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
        Schema::table('services', function (Blueprint $table): void {
            $table->text('content')->nullable();
            $table->json('benefits')->nullable();
            $table->json('faqs')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 320)->nullable();
        });
        Schema::table('price_packages', function (Blueprint $table): void {
            $table->string('pricing_type')->default('starting');
            $table->boolean('is_featured')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('price_packages', function (Blueprint $table): void {
            $table->dropColumn(['pricing_type', 'is_featured']);
        });
        Schema::table('services', function (Blueprint $table): void {
            $table->dropColumn(['content', 'benefits', 'faqs', 'seo_title', 'seo_description']);
        });
    }
};
