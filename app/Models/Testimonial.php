<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = ['customer_name', 'service', 'quote', 'is_published', 'sort_order', 'consent_confirmed_at'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'consent_confirmed_at' => 'datetime'];
    }
}
