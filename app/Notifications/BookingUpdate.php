<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Models\SiteSetting;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingUpdate extends Notification
{
    public function __construct(public Booking $booking, public bool $forAdmin, public string $url) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $booking = $this->booking;

        return (new MailMessage)
            ->subject(($this->forAdmin ? 'New booking request ' : 'Your booking ').$booking->reference)
            ->view(['html' => 'emails.booking-update', 'text' => 'emails.booking-update-text'], [
                'booking' => $booking->loadMissing('service'),
                'forAdmin' => $this->forAdmin,
                'actionUrl' => $this->url,
                'companyName' => SiteSetting::query()->where('key', 'company_name')->value('value') ?? 'Home Movers and Cleaners',
                'logoUrl' => asset(SiteSetting::query()->where('key', 'primary_logo')->value('value') ?? 'images/brand-logo.png'),
                'phone' => SiteSetting::query()->where('key', 'phone')->value('value') ?? '07999 48 186',
            ]);
    }
}
