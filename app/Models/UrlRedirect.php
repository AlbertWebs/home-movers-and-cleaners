<?php

namespace App\Models;

use Database\Factories\UrlRedirectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UrlRedirect extends Model
{
    /** @use HasFactory<UrlRedirectFactory> */
    use HasFactory;

    protected $fillable = ['from_path', 'destination', 'status_code', 'is_active', 'history'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'status_code' => 'integer', 'history' => 'array'];
    }
}
