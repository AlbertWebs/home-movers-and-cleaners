@php
    $serviceName = $booking->service?->name ?? 'Requested service';
    $dateTime = ($booking->preferred_date?->format('j F Y') ?? 'Date to be agreed').($booking->preferred_time ? ' at '.$booking->preferred_time : '').' (Nairobi time)';
    $quoteExpiresAt = $booking->quoteExpiresAt()?->timezone('Africa/Nairobi')->format('j F Y g:i A').' Nairobi time';
    $nextStep = match ($booking->status) {
        'Pending' => 'Our team will review your requested time and contact you to confirm the details.',
        'Quoted' => 'Your quotation is valid until '.$quoteExpiresAt.'. Contact us before then if you would like to proceed.',
        'Expired' => 'This quotation expired on '.$quoteExpiresAt.'. Contact us if you would like an updated quotation.',
        default => 'The booking status or schedule has been updated. Use the button below to review the latest information.',
    };
    $heading = $forAdmin ? 'A new booking request' : match ($booking->status) {
        'Pending' => 'We received your request',
        'Quoted' => 'Your quotation is ready',
        'Expired' => 'Your quotation has expired',
        default => 'Your booking has been updated',
    };
    $buttonLabel = $forAdmin ? 'Manage bookings' : 'View booking status';
@endphp
<!doctype html>
<html lang="en">
<body style="margin:0;background:#f3f5fa;font-family:Arial,sans-serif;color:#263451">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:28px 12px;background:#f3f5fa"><tr><td align="center">
    <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="max-width:600px;width:100%;background:#fff;border-radius:16px;overflow:hidden">
        <tr><td style="padding:22px 28px;background:#102956"><img src="{{ $logoUrl }}" alt="{{ $companyName }}" width="190" style="display:block;max-width:100%;height:auto"></td></tr>
        <tr><td style="padding:30px 28px">
            <p style="margin:0 0 8px;color:#e95421;font-size:12px;font-weight:bold;letter-spacing:1px;text-transform:uppercase">Home service booking</p>
            <h1 style="margin:0 0 18px;color:#102956;font-size:26px">{{ $heading }}</h1>
            <p>Hello {{ $forAdmin ? 'team' : $booking->customer_name }},</p>
            <p style="font-size:16px;line-height:1.6">{{ $nextStep }}</p>
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:22px 0;border-collapse:collapse">
                @if($forAdmin)
                    <tr><td style="padding:9px 0;border-bottom:1px solid #e6eaf0;color:#647086">Customer</td><td style="padding:9px 0;border-bottom:1px solid #e6eaf0">{{ $booking->customer_name }}</td></tr>
                    <tr><td style="padding:9px 0;border-bottom:1px solid #e6eaf0;color:#647086">Phone</td><td style="padding:9px 0;border-bottom:1px solid #e6eaf0">{{ $booking->phone }}</td></tr>
                    @if($booking->quote_photos)<tr><td style="padding:9px 0;border-bottom:1px solid #e6eaf0;color:#647086">Quote photos</td><td style="padding:9px 0;border-bottom:1px solid #e6eaf0">{{ count($booking->quote_photos) }} uploaded. View them in the admin booking record.</td></tr>@endif
                @endif
                    @if($booking->status === 'Quoted')<tr><td style="padding:9px 0;border-bottom:1px solid #e6eaf0;color:#647086">Quote valid until</td><td style="padding:9px 0;border-bottom:1px solid #e6eaf0">{{ $quoteExpiresAt }}</td></tr>@endif
                    @if($booking->status === 'Expired')<tr><td style="padding:9px 0;border-bottom:1px solid #e6eaf0;color:#647086">Quote expired</td><td style="padding:9px 0;border-bottom:1px solid #e6eaf0">{{ $quoteExpiresAt }}</td></tr>@endif
                @foreach(['Reference' => $booking->reference, 'Service' => $serviceName, 'Status' => $booking->status, 'Date and time' => $dateTime] as $label => $value)
                    <tr><td style="padding:9px 0;border-bottom:1px solid #e6eaf0;color:#647086">{{ $label }}</td><td style="padding:9px 0;border-bottom:1px solid #e6eaf0">{{ $value }}</td></tr>
                @endforeach
            </table>
            <p style="margin:24px 0"><a href="{{ $actionUrl }}" style="display:inline-block;padding:13px 20px;border-radius:8px;background:#ee5425;color:#fff;font-weight:bold;text-decoration:none">{{ $buttonLabel }}</a></p>
            <p style="font-size:14px;line-height:1.6">Questions? Call {{ $phone }}.</p>
        </td></tr>
        <tr><td style="padding:16px 28px;background:#102956;color:#dbe4f4;font-size:12px">{{ $companyName }} · Nairobi, Kenya</td></tr>
    </table>
</td></tr></table>
</body>
</html>
