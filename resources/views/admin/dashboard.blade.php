@extends('admin.layout')
@section('title', 'Overview')
@section('content')
<div class="admin-welcome dashboard-welcome">
    <div><div class="eyebrow">WELCOME BACK</div><h1>Good to see you, {{ auth()->user()->name }}.</h1><p>Here’s what’s happening with your website, bookings, and enquiries.</p></div>
    <a class="button" href="{{ route('admin.bookings') }}"><span class="dashboard-button-icon" aria-hidden="true">▦</span> View bookings <span aria-hidden="true">→</span></a>
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
    <article data-icon="▦"><span>ALL BOOKINGS</span><strong>{{ $totalBookings }}</strong><small>Requests in this period</small></article>
    <article data-icon="➤"><span>NEW REQUESTS</span><strong>{{ $newBookings }}</strong><small>Waiting for a response</small></article>
    <article data-icon="✓"><span>CONFIRMED</span><strong>{{ $confirmedBookings }}</strong><small>Confirmed or scheduled</small></article>
    <article data-icon="☑"><span>COMPLETED</span><strong>{{ $completedBookings }}</strong><small>Services completed</small></article>
    <article data-icon="⊗"><span>CANCELLED</span><strong>{{ $cancelledBookings }}</strong><small>Cancelled requests</small></article>
    <article data-icon="✉"><span>UNREAD ENQUIRIES</span><strong>{{ $enquiries }}</strong><small>Messages to review</small></article>
</div>
<div class="dashboard-panels">
    <section class="admin-panel">
        <div class="panel-head"><div><h2><span class="dashboard-panel-icon" aria-hidden="true">▥</span> Booking trends</h2><p>Requests received by month</p></div></div>
        <div class="dashboard-chart" role="img" aria-label="Monthly booking requests">
            @php
                $chartMax = max(1, collect($monthlyTrend)->max('count'));
                $chartStep = 560 / max(count($monthlyTrend) - 1, 1);
                $chartPoints = collect($monthlyTrend)->map(fn ($month, $index) => ($index * $chartStep + 20).','. (140 - ($month['count'] / $chartMax * 108)))->implode(' ');
            @endphp
            <svg class="dashboard-line-chart" viewBox="0 0 600 180" preserveAspectRatio="none" aria-hidden="true">
                <defs><linearGradient id="booking-area" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="#eed322" stop-opacity=".24"/><stop offset="1" stop-color="#eed322" stop-opacity="0"/></linearGradient></defs>
                @foreach([0, 1, 2, 3] as $gridLine)
                    <line x1="20" x2="580" y1="{{ 32 + ($gridLine * 36) }}" y2="{{ 32 + ($gridLine * 36) }}" />
                    <text x="0" y="{{ 36 + ($gridLine * 36) }}">{{ max(0, $chartMax - $gridLine) }}</text>
                @endforeach
                <polygon points="20,140 {{ $chartPoints }} 580,140" />
                <polyline points="{{ $chartPoints }}" />
                @foreach($monthlyTrend as $index => $month)
                    <circle cx="{{ $index * $chartStep + 20 }}" cy="{{ 140 - ($month['count'] / $chartMax * 108) }}" r="4" />
                @endforeach
            </svg>
            @foreach($monthlyTrend as $month)
                <div class="dashboard-chart-column"><small>{{ $month['label'] }}</small></div>
            @endforeach
        </div>
    </section>
    <section class="admin-panel">
        <div class="panel-head"><div><h2><span class="dashboard-panel-icon" aria-hidden="true">⟳</span> Bookings by service</h2><p>Requests in the selected period</p></div></div>
        @php
            $serviceTotal = (int) $serviceBreakdown->sum('bookings_count');
            $serviceColors = ['#eed322', '#8ec5ff', '#4cc878', '#a45cff', '#e6b460'];
            $serviceOffset = 0;
            $serviceGradient = [];
            foreach ($serviceBreakdown as $index => $item) {
                $segment = $serviceTotal > 0 ? ($item->bookings_count / $serviceTotal) * 100 : 0;
                $serviceGradient[] = $serviceColors[$index % count($serviceColors)].' '.$serviceOffset.'% '.($serviceOffset + $segment).'%';
                $serviceOffset += $segment;
            }
        @endphp
        <div class="dashboard-service-chart">
            <div class="dashboard-donut" style="--service-gradient: conic-gradient({{ $serviceTotal > 0 ? implode(', ', $serviceGradient) : '#e8edf5 0 100%' }})"><strong>{{ $serviceTotal }}</strong><small>Bookings</small></div>
            <div class="dashboard-service-list">
        @forelse($serviceBreakdown as $item)
            @php $serviceIndex = $loop->index; $servicePercent = $serviceTotal > 0 ? round($item->bookings_count / $serviceTotal * 100) : 0; @endphp
            <div class="dashboard-service-row"><span><i style="--service-color: {{ $serviceColors[$serviceIndex % count($serviceColors)] }}"></i>{{ $item->service?->name ?? 'Service removed' }}</span><strong>{{ $item->bookings_count }}</strong><small>{{ $servicePercent }}%</small></div>
        @empty
            <div class="admin-empty"><p>No booking requests in this period.</p></div>
        @endforelse
            </div>
        </div>
    </section>
</div>
<div class="dashboard-panels dashboard-activity-panels">
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
</div>
<section class="admin-panel">
    <div class="panel-head"><div><h2>Recent requests</h2><p>The latest customer requests</p></div><a href="{{ route('admin.bookings') }}">All bookings <span aria-hidden="true">→</span></a></div>
    @if($bookings->isEmpty())
        <div class="admin-empty"><span aria-hidden="true">✳</span><h3>It starts here.</h3><p>New booking requests will appear here when customers send them.</p><a href="{{ route('home') }}" target="_blank" rel="noopener">View your website <span aria-hidden="true">↗</span></a></div>
    @else
        <div class="table-scroll dashboard-recent-table-scroll"><table class="admin-data-table dashboard-recent-table" aria-label="Recent booking requests"><thead><tr><th scope="col">REFERENCE</th><th scope="col">CUSTOMER</th><th scope="col">SERVICE</th><th scope="col">DATE REQUESTED</th><th scope="col">STATUS</th></tr></thead><tbody>
            @foreach($bookings as $booking)
                <tr><td class="ref-cell">{{ $booking->reference }}</td><td>{{ $booking->customer_name }}<small>{{ $booking->phone }}</small></td><td>{{ $booking->service?->name ?? 'Service removed' }}</td><td>{{ $booking->created_at->format('j M Y') }}</td><td><span class="status-pill">{{ $booking->status }}</span></td></tr>
            @endforeach
        </tbody></table></div>
    @endif
</section>
@endsection
