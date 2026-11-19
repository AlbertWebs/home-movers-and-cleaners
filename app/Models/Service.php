<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $fillable = ['name', 'slug', 'summary', 'description', 'icon', 'image', 'is_active', 'sort_order', 'content', 'benefits', 'faqs', 'seo_title', 'seo_description', 'canonical_url', 'seo_robots', 'og_title', 'og_description', 'og_image'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'benefits' => 'array', 'faqs' => 'array'];
    }

    public function photo(): ?string
    {
        return $this->image ?: config('site_content.services.'.$this->slug.'.image');
    }

    public function packages(): HasMany
    {
        return $this->hasMany(PricePackage::class)->orderBy('sort_order');
    }
}
