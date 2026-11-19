<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GalleryItem extends Model
{
    public const CATEGORIES = ['Moving', 'Fumigation', 'Sofa Cleaning', 'Mattress Cleaning', 'House Deep Cleaning', 'Before and After'];

    protected $fillable = ['path', 'caption', 'alt_text', 'category', 'is_published', 'sort_order'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }
}
