<?php

namespace App\Support;

use App\Models\SiteSetting;

class NotificationRecipients
{
    /** @return list<string> */
    public static function cc(): array
    {
        $recipients = preg_split('/[,;\s]+/', (string) SiteSetting::query()->where('key', 'notification_cc')->value('value'), -1, PREG_SPLIT_NO_EMPTY);

        return array_values(array_unique(array_map('strtolower', array_filter($recipients ?: [], fn (string $email): bool => filter_var($email, FILTER_VALIDATE_EMAIL) !== false))));
    }
}
