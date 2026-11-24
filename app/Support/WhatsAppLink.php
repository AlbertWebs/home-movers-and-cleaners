<?php

namespace App\Support;

class WhatsAppLink
{
    public static function make(?string $number, string $message): ?string
    {
        $digits = preg_replace('/\D+/', '', $number ?? '');

        if (! $digits) {
            return null;
        }

        return 'https://wa.me/'.$digits.'?text='.rawurlencode($message);
    }
}
