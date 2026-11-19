@php($serviceName = $enquiry->service?->name ?? 'Not specified')
A new website enquiry is saved in the admin inbox.

Name: {{ $enquiry->name }}
Phone: {{ $enquiry->phone }}
Email: {{ $enquiry->email ?: 'Not provided' }}
Service: {{ $serviceName }}
Subject: {{ $enquiry->subject }}

{{ $enquiry->message }}

{{ $actionUrl }}
