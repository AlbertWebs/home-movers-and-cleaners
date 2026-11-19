<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricePackage extends Model
{
    protected $fillable = ['service_id', 'name', 'description', 'amount', 'is_active', 'sort_order', 'pricing_type', 'is_featured'];

    protected function casts(): array
    {
        return ['amount' => 'integer', 'is_active' => 'boolean', 'is_featured' => 'boolean'];
    }

    public function priceLabel(): string
    {
        return match ($this->pricing_type) {
            'quote' => 'Request a Quote',
            'fixed' => 'KSh '.number_format($this->amount),
            default => 'From KSh '.number_format($this->amount),
        };
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
