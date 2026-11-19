<?php

namespace App\Notifications;

use App\Models\Enquiry;
use App\Models\SiteSetting;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EnquiryReceived extends Notification
{
    public function __construct(public Enquiry $enquiry) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('A new website enquiry')
            ->view(['html' => 'emails.enquiry-received', 'text' => 'emails.enquiry-received-text'], [
                'enquiry' => $this->enquiry->loadMissing('service'),
                'companyName' => SiteSetting::query()->where('key', 'company_name')->value('value') ?? 'Home Movers and Cleaners',
                'logoUrl' => asset(SiteSetting::query()->where('key', 'primary_logo')->value('value') ?? 'images/brand-logo.png'),
                'phone' => SiteSetting::query()->where('key', 'phone')->value('value') ?? '07999 48 186',
                'actionUrl' => route('admin.enquiries'),
            ]);
    }
}
