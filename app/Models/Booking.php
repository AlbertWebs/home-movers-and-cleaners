<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = ['reference', 'service_id', 'price_package_id', 'package_name', 'listed_price', 'customer_name', 'phone', 'email', 'location', 'preferred_date', 'preferred_time', 'instructions', 'pickup_location', 'destination', 'status', 'internal_notes'];

    protected function casts(): array
    {
        return ['preferred_date' => 'date', 'listed_price' => 'integer', 'scheduled_duration' => 'integer', 'status_history' => 'array'];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
