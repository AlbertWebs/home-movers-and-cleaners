<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentPage extends Model
{
    protected $fillable = ['slug', 'title', 'summary', 'body', 'image', 'image_alt', 'seo_title', 'seo_description', 'canonical_url', 'seo_robots', 'og_title', 'og_description', 'og_image', 'is_published'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }
}
