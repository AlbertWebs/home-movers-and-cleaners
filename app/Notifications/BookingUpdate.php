<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Models\SiteSetting;
use App\Support\NotificationRecipients;
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
        $subject = match ($booking->status) {
            'Quoted' => 'Your quotation is ready '.$booking->reference,
            'Expired' => 'Your quotation has expired '.$booking->reference,
            default => ($this->forAdmin ? 'New booking request ' : 'Your booking ').$booking->reference,
        };

        $mail = (new MailMessage)
            ->subject($subject)
            ->view(['html' => 'emails.booking-update', 'text' => 'emails.booking-update-text'], [
                'booking' => $booking->loadMissing('service'),
                'forAdmin' => $this->forAdmin,
                'actionUrl' => $this->url,
                'companyName' => SiteSetting::query()->where('key', 'company_name')->value('value') ?? 'Home Movers and Cleaners',
                'logoUrl' => asset(SiteSetting::query()->where('key', 'primary_logo')->value('value') ?? 'images/brand-logo.png'),
                'phone' => SiteSetting::query()->where('key', 'phone')->value('value') ?? '07999 48 186',
            ]);

        $ccRecipients = $this->forAdmin ? NotificationRecipients::cc() : [];
        if ($ccRecipients !== []) {
            $mail->cc($ccRecipients);
        }

        return $mail;
    }
}
