@php($serviceName = $enquiry->service?->name ?? 'Not specified')
<!doctype html>
<html lang="en">
<body style="margin:0;background:#f3f5fa;font-family:Arial,sans-serif;color:#263451">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:28px 12px;background:#f3f5fa"><tr><td align="center">
    <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="max-width:600px;width:100%;background:#fff;border-radius:16px;overflow:hidden">
        <tr><td style="padding:22px 28px;background:#102956"><img src="{{ $logoUrl }}" alt="{{ $companyName }}" width="190" style="display:block;max-width:100%;height:auto"></td></tr>
        <tr><td style="padding:30px 28px">
            <p style="margin:0 0 8px;color:#e95421;font-size:12px;font-weight:bold;letter-spacing:1px;text-transform:uppercase">Website contact</p>
            <h1 style="margin:0 0 18px;color:#102956;font-size:26px">A new customer enquiry</h1>
            <p style="font-size:16px;line-height:1.6">This message has been saved in the admin inbox for follow-up.</p>
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:22px 0;border-collapse:collapse">
                @foreach(['Name' => $enquiry->name, 'Phone' => $enquiry->phone, 'Email' => $enquiry->email ?: 'Not provided', 'Service' => $serviceName, 'Subject' => $enquiry->subject] as $label => $value)
                    <tr><td style="padding:9px 0;border-bottom:1px solid #e6eaf0;color:#647086">{{ $label }}</td><td style="padding:9px 0;border-bottom:1px solid #e6eaf0">{{ $value }}</td></tr>
                @endforeach
            </table>
            <div style="padding:16px;border-radius:8px;background:#f5f7fb;white-space:pre-wrap;line-height:1.6">{{ $enquiry->message }}</div>
            <p style="margin:24px 0"><a href="{{ $actionUrl }}" style="display:inline-block;padding:13px 20px;border-radius:8px;background:#ee5425;color:#fff;font-weight:bold;text-decoration:none">Open enquiries</a></p>
        </td></tr>
        <tr><td style="padding:16px 28px;background:#102956;color:#dbe4f4;font-size:12px">{{ $companyName }} · Nairobi, Kenya · {{ $phone }}</td></tr>
    </table>
</td></tr></table>
</body>
</html>
