<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = ['reference', 'service_id', 'price_package_id', 'package_name', 'listed_price', 'customer_name', 'phone', 'email', 'location', 'preferred_date', 'preferred_time', 'instructions', 'pickup_location', 'destination', 'status', 'quote_issued_at', 'internal_notes'];

    protected function casts(): array
    {
        return ['preferred_date' => 'date', 'quote_issued_at' => 'immutable_datetime', 'listed_price' => 'integer', 'scheduled_duration' => 'integer', 'status_history' => 'array', 'quote_photos' => 'array'];
    }

    public function quoteExpiresAt(): ?CarbonImmutable
    {
        return $this->quote_issued_at?->toImmutable()->addDays(30);
    }

    public function quoteHasExpired(?CarbonImmutable $at = null): bool
    {
        $expiresAt = $this->quoteExpiresAt();

        return $this->status === 'Quoted' && $expiresAt !== null && ($at ?? CarbonImmutable::now())->greaterThanOrEqualTo($expiresAt);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function pricePackage(): BelongsTo
    {
        return $this->belongsTo(PricePackage::class);
    }
}
