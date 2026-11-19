<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaAsset extends Model
{
    protected $fillable = ['path', 'original_name', 'title', 'alt_text', 'category', 'mime_type', 'size', 'uploaded_by'];

    public function url(): string
    {
        return asset('storage/'.$this->path);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
