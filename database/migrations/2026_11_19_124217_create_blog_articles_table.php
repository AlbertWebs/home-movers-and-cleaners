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
        Schema::create('blog_articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category', 80);
            $table->string('excerpt', 500);
            $table->longText('body');
            $table->string('featured_image')->nullable();
            $table->string('image_alt')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 320)->nullable();
            $table->text('canonical_url')->nullable();
            $table->string('seo_robots', 32)->nullable();
            $table->string('og_title')->nullable();
            $table->string('og_description', 320)->nullable();
            $table->string('og_image')->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_articles');
    }
};
