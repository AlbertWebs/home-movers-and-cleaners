@extends('admin.layout')
@section('title', 'Overview')
@section('content')
<div class="admin-welcome">
    <div><div class="eyebrow">YOUR HOME SERVICES, AT A GLANCE</div><h1>Good to see you, {{ auth()->user()->name }}.</h1><p>Review requests, upcoming work, and recent activity.</p></div>
    <a class="button" href="{{ route('admin.bookings') }}">View bookings <span aria-hidden="true">↗</span></a>
</div>
<form class="dashboard-range" method="get" action="{{ route('admin.dashboard') }}">
    <label for="dashboard-range">Date range</label>
    <select id="dashboard-range" name="range">
        @foreach(['30' => 'Last 30 days', '90' => 'Last 90 days', '180' => 'Last 6 months', '365' => 'Last 12 months'] as $days => $label)
            <option value="{{ $days }}" @selected((string) $range === $days)>{{ $label }}</option>
        @endforeach
    </select>
    <button class="mini-button" type="submit">Apply</button>
</form>
<div class="stat-grid">
    <article><span>ALL BOOKINGS</span><strong>{{ $totalBookings }}</strong><small>Requests in this period</small></article>
    <article><span>NEW REQUESTS</span><strong>{{ $newBookings }}</strong><small>Waiting for a response</small></article>
    <article><span>CONFIRMED</span><strong>{{ $confirmedBookings }}</strong><small>Confirmed or scheduled</small></article>
    <article><span>COMPLETED</span><strong>{{ $completedBookings }}</strong><small>Services completed</small></article>
    <article><span>CANCELLED</span><strong>{{ $cancelledBookings }}</strong><small>Cancelled requests</small></article>
    <article><span>UNREAD ENQUIRIES</span><strong>{{ $enquiries }}</strong><small>Messages to review</small></article>
</div>
<div class="dashboard-panels">
    <section class="admin-panel">
        <div class="panel-head"><div><h2>Booking trends</h2><p>Requests received by month</p></div></div>
        <div class="dashboard-chart" role="img" aria-label="Monthly booking requests">
            @foreach($monthlyTrend as $month)
                <div class="dashboard-chart-column"><strong>{{ $month['count'] }}</strong><span style="--bar-height: {{ $month['height'] }}%"></span><small>{{ $month['label'] }}</small></div>
            @endforeach
        </div>
    </section>
    <section class="admin-panel">
        <div class="panel-head"><div><h2>Bookings by service</h2><p>Requests in the selected period</p></div></div>
        @forelse($serviceBreakdown as $item)
            <div class="dashboard-service-row"><span>{{ $item->service?->name ?? 'Service removed' }}</span><strong>{{ $item->bookings_count }}</strong></div>
        @empty
            <div class="admin-empty"><p>No booking requests in this period.</p></div>
        @endforelse
    </section>
</div>
<section class="admin-panel">
    <div class="panel-head"><div><h2>Upcoming appointments</h2><p>Confirmed work on the calendar</p></div><a href="{{ route('admin.bookings', ['period' => 'month']) }}">Open calendar <span aria-hidden="true">→</span></a></div>
    @if($upcomingBookings->isEmpty())
        <div class="admin-empty"><p>There are no confirmed appointments coming up.</p></div>
    @else
        <div class="table-scroll"><table><thead><tr><th>DATE AND TIME</th><th>REFERENCE</th><th>CUSTOMER</th><th>SERVICE</th><th>STATUS</th></tr></thead><tbody>
            @foreach($upcomingBookings as $booking)
                <tr><td>{{ $booking->preferred_date?->format('j M Y') }}<small>{{ $booking->preferred_time }}</small></td><td class="ref-cell">{{ $booking->reference }}</td><td>{{ $booking->customer_name }}<small>{{ $booking->phone }}</small></td><td>{{ $booking->service?->name ?? 'Service removed' }}</td><td><span class="status-pill">{{ $booking->status }}</span></td></tr>
            @endforeach
        </tbody></table></div>
    @endif
</section>
<section class="admin-panel">
    <div class="panel-head"><div><h2>Recent enquiries</h2><p>Customer messages waiting in your inbox</p></div><a href="{{ route('admin.enquiries') }}">Open enquiry inbox <span aria-hidden="true">→</span></a></div>
    @forelse($recentEnquiries as $enquiry)
        <div class="dashboard-service-row"><span><strong>{{ $enquiry->subject }}</strong><small>{{ $enquiry->name }} · {{ $enquiry->created_at->format('j M Y') }}</small></span><span class="status-pill">{{ str($enquiry->status)->replace('_', ' ')->title() }}</span></div>
    @empty
        <div class="admin-empty"><p>No enquiries have been received.</p></div>
    @endforelse
</section>
<section class="admin-panel">
    <div class="panel-head"><div><h2>Recent requests</h2><p>The latest customer requests</p></div><a href="{{ route('admin.bookings') }}">All bookings <span aria-hidden="true">→</span></a></div>
    @if($bookings->isEmpty())
        <div class="admin-empty"><span aria-hidden="true">✳</span><h3>It starts here.</h3><p>New booking requests will appear here when customers send them.</p><a href="{{ route('home') }}" target="_blank" rel="noopener">View your website <span aria-hidden="true">↗</span></a></div>
    @else
        <div class="table-scroll"><table><thead><tr><th>REFERENCE</th><th>CUSTOMER</th><th>SERVICE</th><th>DATE REQUESTED</th><th>STATUS</th></tr></thead><tbody>
            @foreach($bookings as $booking)
                <tr><td class="ref-cell">{{ $booking->reference }}</td><td>{{ $booking->customer_name }}<small>{{ $booking->phone }}</small></td><td>{{ $booking->service?->name ?? 'Service removed' }}</td><td>{{ $booking->created_at->format('j M Y') }}</td><td><span class="status-pill">{{ $booking->status }}</span></td></tr>
            @endforeach
        </tbody></table></div>
    @endif
</section>
@endsection
