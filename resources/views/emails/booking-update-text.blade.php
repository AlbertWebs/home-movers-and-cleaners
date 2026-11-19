@php
    $serviceName = $booking->service?->name ?? 'Requested service';
    $dateTime = ($booking->preferred_date?->format('j F Y') ?? 'Date to be agreed').($booking->preferred_time ? ' at '.$booking->preferred_time : '').' (Nairobi time)';
    $heading = $forAdmin ? 'A new booking request' : ($booking->status === 'Pending' ? 'We received your request' : 'Your booking has been updated');
@endphp
{{ $heading }}

Hello {{ $forAdmin ? 'team' : $booking->customer_name }},

@if($forAdmin)
Customer: {{ $booking->customer_name }}
Phone: {{ $booking->phone }}
@endif
Reference: {{ $booking->reference }}
Service: {{ $serviceName }}
Status: {{ $booking->status }}
Date and time: {{ $dateTime }}

{{ $booking->status === 'Pending' ? 'Our team will review your requested time and contact you to confirm the details.' : 'The booking status or schedule has been updated.' }}

{{ $actionUrl }}

{{ $companyName }} | {{ $phone }}
