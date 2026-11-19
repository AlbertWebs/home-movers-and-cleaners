<?php

namespace App\Models;

use Database\Factories\BlogArticleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogArticle extends Model
{
    /** @use HasFactory<BlogArticleFactory> */
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'category', 'excerpt', 'body', 'featured_image', 'image_alt',
        'seo_title', 'seo_description', 'canonical_url', 'seo_robots', 'og_title',
        'og_description', 'og_image', 'status', 'published_at',
    ];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }
}
