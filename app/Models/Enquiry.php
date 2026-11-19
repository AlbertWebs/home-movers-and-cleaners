<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enquiry extends Model
{
    protected $fillable = ['name', 'phone', 'email', 'subject', 'message', 'is_read', 'service_id', 'status', 'internal_notes'];

    protected function casts(): array
    {
        return ['is_read' => 'boolean'];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
