@php
    $serviceName = $booking->service?->name ?? 'Requested service';
    $dateTime = ($booking->preferred_date?->format('j F Y') ?? 'Date to be agreed').($booking->preferred_time ? ' at '.$booking->preferred_time : '').' (Nairobi time)';
    $quoteExpiresAt = $booking->quoteExpiresAt()?->timezone('Africa/Nairobi')->format('j F Y g:i A').' Nairobi time';
    $heading = $forAdmin ? 'A new booking request' : match ($booking->status) {
        'Pending' => 'We received your request',
        'Quoted' => 'Your quotation is ready',
        'Expired' => 'Your quotation has expired',
        default => 'Your booking has been updated',
    };
@endphp
{{ $heading }}

Hello {{ $forAdmin ? 'team' : $booking->customer_name }},

@if($forAdmin)
Customer: {{ $booking->customer_name }}
Phone: {{ $booking->phone }}
@if($booking->quote_photos)
Quote photos: {{ count($booking->quote_photos) }} uploaded. View them in the admin booking record.
@endif
@endif
Reference: {{ $booking->reference }}
Service: {{ $serviceName }}
Status: {{ $booking->status }}
@if($booking->status === 'Quoted')
Your quotation is valid until {{ $quoteExpiresAt }}. Contact us before then if you would like to proceed.
@elseif($booking->status === 'Expired')
This quotation expired on {{ $quoteExpiresAt }}. Contact us if you would like an updated quotation.
@endif
Date and time: {{ $dateTime }}

{{ $booking->status === 'Pending' ? 'Our team will review your requested time and contact you to confirm the details.' : 'The booking status or schedule has been updated.' }}

{{ $actionUrl }}

{{ $companyName }} | {{ $phone }}
