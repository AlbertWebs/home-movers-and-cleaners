@extends('admin.layout')
@section('title', 'Bookings')
@section('content')
<div class="admin-welcome booking-page-heading"><div><div class="eyebrow">CUSTOMER MANAGEMENT</div><h1>Booking requests</h1><p>Review requests, update their status, and set your available hours in Nairobi time.</p></div><span class="booking-total-badge">{{ number_format($bookings->total()) }} {{ \Illuminate\Support\Str::plural('request', $bookings->total()) }}</span></div>
@include('partials.errors')

<details class="admin-panel booking-admin-settings" @if($errors->has('opening') || $errors->has('closing') || $errors->has('dates.*')) open @endif>
    <summary>Availability and email settings</summary>
    <form class="settings-form" method="post" action="{{ route('admin.bookings.schedule') }}">
        @csrf @method('PATCH')
        <input type="hidden" name="enabled" value="0">
        <label class="booking-inline-field"><input type="checkbox" name="enabled" value="1" @checked(old('enabled', $schedule['enabled']))> Accept online bookings</label>
        <fieldset class="booking-weekdays"><legend>Working days</legend>
            @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $index => $day)
                <label class="booking-inline-field"><input type="checkbox" name="weekdays[]" value="{{ $index + 1 }}" @checked(in_array($index + 1, old('weekdays', $schedule['weekdays'])))> {{ $day }}</label>
            @endforeach
        </fieldset>
        <div class="form-row">
            <label>Opening time<input name="opening" type="time" required value="{{ old('opening', $schedule['opening']) }}"></label>
            <label>Closing time<input name="closing" type="time" required value="{{ old('closing', $schedule['closing']) }}"></label>
        </div>
        <div class="form-row">
            <label>Slot interval, minutes<input name="interval" type="number" min="15" max="240" required value="{{ old('interval', $schedule['interval']) }}"></label>
            <label>Concurrent bookings<input name="capacity" type="number" min="1" max="20" required value="{{ old('capacity', $schedule['capacity']) }}"></label>
            <label>Minimum notice, hours<input name="notice" type="number" min="0" max="336" required value="{{ old('notice', $schedule['notice']) }}"></label>
        </div>
        <label>Blocked dates and holidays<textarea name="blocked_dates" rows="3" placeholder="YYYY-MM-DD, one date per line">{{ old('blocked_dates', implode(PHP_EOL, $schedule['blocked_dates'])) }}</textarea></label>
        <fieldset class="booking-weekdays"><legend>Service durations in minutes</legend>
            @foreach($services as $service)
                <label>{{ $service->name }}<input type="number" name="service_rules[{{ $service->id }}][duration]" min="15" max="480" placeholder="{{ $schedule['interval'] }}" value="{{ old('service_rules.'.$service->id.'.duration', $schedule['service_rules'][$service->id]['duration'] ?? '') }}"></label>
            @endforeach
        </fieldset>
        <div class="form-row">
            <label>Booking notification email<input name="booking_recipient" type="email" value="{{ old('booking_recipient', $siteSettings['booking_recipient'] ?? '') }}"></label>
            <label>Enquiry notification email<input name="enquiry_recipient" type="email" value="{{ old('enquiry_recipient', $siteSettings['enquiry_recipient'] ?? '') }}"></label>
        </div>
        <button class="button" type="submit">Save schedule</button>
    </form>
</details>

<section class="admin-panel">
    <form method="get" action="{{ route('admin.bookings') }}" class="booking-admin-filters">
        <label>Search<input name="search" value="{{ request('search') }}" placeholder="Reference, name or phone"></label>
        <label>Status<select name="status"><option value="">All statuses</option>@foreach($statuses as $status)<option @selected(request('status') === $status)>{{ $status }}</option>@endforeach</select></label>
        <label>Service<select name="service_id"><option value="">All services</option>@foreach($services as $service)<option value="{{ $service->id }}" @selected(request('service_id') == $service->id)>{{ $service->name }}</option>@endforeach</select></label>
        <label>Booking date<input name="date" type="date" value="{{ request('date') }}"></label>
        <button type="submit" class="button">Filter bookings</button>
    </form>
    <div class="booking-view-actions">
        <a class="mini-button" href="{{ route('admin.bookings', request()->only('search', 'status', 'service_id', 'date')) }}">List view</a>
        <a class="mini-button" href="{{ route('admin.bookings', request()->only('search', 'status', 'service_id') + ['period' => 'month', 'anchor' => request('anchor', now('Africa/Nairobi')->toDateString())]) }}">Calendar view</a>
        <a class="text-link" href="{{ route('admin.bookings.export', request()->only('search', 'status', 'service_id', 'date')) }}">Download filtered CSV</a>
    </div>
    @if($calendarStart)
        @php
            $calendarPeriod = request('period', 'month');
            $calendarAnchor = \Carbon\CarbonImmutable::parse(request('anchor', now('Africa/Nairobi')->toDateString()), 'Africa/Nairobi');
            $previousAnchor = match ($calendarPeriod) { 'day' => $calendarAnchor->subDay(), 'week' => $calendarAnchor->subWeek(), default => $calendarAnchor->subMonth() };
            $nextAnchor = match ($calendarPeriod) { 'day' => $calendarAnchor->addDay(), 'week' => $calendarAnchor->addWeek(), default => $calendarAnchor->addMonth() };
            $calendarDaysCount = $calendarStart->diffInDays($calendarEnd) + 1;
            $calendarLeadingBlanks = $calendarPeriod === 'month' ? $calendarStart->dayOfWeekIso - 1 : 0;
        @endphp
        <section class="booking-admin-calendar" aria-label="Booking calendar">
            <div class="booking-calendar-header">
                <a class="mini-button" href="{{ route('admin.bookings', request()->only('search', 'status', 'service_id') + ['period' => $calendarPeriod, 'anchor' => $previousAnchor->toDateString()]) }}" aria-label="Previous {{ $calendarPeriod }}">‹</a>
                <h2>{{ $calendarPeriod === 'day' ? $calendarStart->format('j F Y') : ($calendarPeriod === 'week' ? $calendarStart->format('j M').' to '.$calendarEnd->format('j M Y') : $calendarStart->format('F Y')) }}</h2>
                <a class="mini-button" href="{{ route('admin.bookings', request()->only('search', 'status', 'service_id') + ['period' => $calendarPeriod, 'anchor' => $nextAnchor->toDateString()]) }}" aria-label="Next {{ $calendarPeriod }}">›</a>
                <div class="booking-period-switch" aria-label="Calendar period">
                    @foreach(['day' => 'Day', 'week' => 'Week', 'month' => 'Month'] as $periodValue => $periodLabel)
                        <a class="mini-button {{ $calendarPeriod === $periodValue ? 'is-current' : '' }}" href="{{ route('admin.bookings', request()->only('search', 'status', 'service_id') + ['period' => $periodValue, 'anchor' => $calendarAnchor->toDateString()]) }}" @if($calendarPeriod === $periodValue) aria-current="page" @endif>{{ $periodLabel }}</a>
                    @endforeach
                </div>
            </div>
            <div class="booking-month-grid {{ $calendarPeriod !== 'month' ? 'is-compact' : '' }}">
                @if($calendarPeriod === 'month')
                    @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $weekday)
                        <strong class="booking-weekday">{{ $weekday }}</strong>
                    @endforeach
                    @for($blank = 0; $blank < $calendarLeadingBlanks; $blank++)<span class="booking-day-empty" aria-hidden="true"></span>@endfor
                @endif
                @for($dayIndex = 0; $dayIndex < $calendarDaysCount; $dayIndex++)
                    @php $calendarDay = $calendarStart->addDays($dayIndex); $dayBookings = $calendarDays->get($calendarDay->toDateString(), collect()); @endphp
                    <article class="booking-day-cell {{ $dayBookings->isNotEmpty() ? 'has-bookings' : '' }}">
                        <time datetime="{{ $calendarDay->toDateString() }}">{{ $calendarPeriod === 'month' ? $calendarDay->format('j') : $calendarDay->format('l, j F') }}</time>
                        @forelse($dayBookings as $dayBooking)
                            <a class="booking-calendar-event" href="#booking-{{ $dayBooking->id }}"><strong>{{ $dayBooking->preferred_time ?: 'Time pending' }}</strong> {{ $dayBooking->customer_name }}<small>{{ $dayBooking->service?->name }} · {{ $dayBooking->status }}</small></a>
                        @empty
                            @if($calendarPeriod !== 'month')<p class="booking-day-empty-label">No bookings</p>@endif
                        @endforelse
                    </article>
                @endfor
            </div>
        </section>
    @endif
    <div class="table-scroll booking-table-scroll"><table class="admin-data-table booking-data-table">
        <thead><tr><th>Reference and customer</th><th>Service</th><th>Booking details</th><th>Status and scheduling</th></tr></thead>
        <tbody>
        @forelse($bookings as $booking)
            <tr id="booking-{{ $booking->id }}">
                <td class="ref-cell" data-label="Reference and customer"><strong>{{ $booking->reference }}</strong><small>{{ $booking->customer_name }} · {{ $booking->phone }}</small><small>{{ $booking->email }}</small></td>
                <td data-label="Service"><strong class="booking-service-name">{{ $booking->service?->name ?? 'Service removed' }}</strong><small>{{ $booking->package_name ?: 'No package selected' }}</small><small>{{ $booking->listed_price ? 'KSh '.number_format($booking->listed_price) : 'Request a Quote' }}</small></td>
                <td data-label="Booking details">
                    {{ $booking->location }}
                    @if($booking->destination)<small>{{ $booking->pickup_location }} → {{ $booking->destination }}</small>@endif
                    @if($booking->preferred_date)<small>{{ $booking->preferred_date->format('j M Y') }} {{ $booking->preferred_time }}</small>@endif
                    @if($booking->instructions)<small>{{ $booking->instructions }}</small>@endif
                    @if($booking->status_history)
                        <details><summary>Status history</summary>
                            @foreach(array_reverse($booking->status_history) as $entry)
                                <small>{{ $entry['status'] }} · {{ \Carbon\CarbonImmutable::parse($entry['at'])->timezone('Africa/Nairobi')->format('j M Y H:i') }}</small>
                            @endforeach
                        </details>
                    @endif
                </td>
                <td data-label="Status and scheduling">
                    <form class="booking-admin-update" data-booking-status="{{ \Illuminate\Support\Str::slug($booking->status) }}" method="post" action="{{ route('admin.bookings.update', $booking) }}">
                        @csrf @method('PATCH')
                        <label>Status<select name="status">@foreach($statuses as $status)<option @selected($booking->status === $status)>{{ $status }}</option>@endforeach</select></label>
                        @if(preg_match('/^\d{2}:\d{2}$/', $booking->preferred_time ?? ''))
                            <label>Date<input name="preferred_date" type="date" value="{{ $booking->preferred_date?->toDateString() }}"></label>
                            <label>Time<input name="preferred_time" type="time" value="{{ $booking->preferred_time }}"></label>
                        @else
                            <label>New date<input name="preferred_date" type="date"></label>
                            <label>New time<input name="preferred_time" type="time"></label>
                        @endif
                        <label>Internal notes<textarea name="internal_notes" rows="2">{{ $booking->internal_notes }}</textarea></label>
                        <button class="mini-button" type="submit">Save booking</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4"><div class="admin-empty"><h3>No matching booking requests.</h3><p>Try another filter or check back when new requests come in.</p></div></td></tr>
        @endforelse
        </tbody>
    </table></div>
    <div class="pagination">{{ $bookings->links() }}</div>
</section>
@endsection
