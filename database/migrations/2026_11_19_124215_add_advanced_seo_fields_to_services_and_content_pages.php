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
        foreach (['services', 'content_pages'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->text('canonical_url')->nullable();
                $table->string('seo_robots', 32)->nullable();
                $table->string('og_title')->nullable();
                $table->string('og_description', 320)->nullable();
                $table->string('og_image')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['services', 'content_pages'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropColumn(['canonical_url', 'seo_robots', 'og_title', 'og_description', 'og_image']);
            });
        }
    }
};
