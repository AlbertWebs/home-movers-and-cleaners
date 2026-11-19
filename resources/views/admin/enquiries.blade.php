@extends('admin.layout')
@section('title', 'Enquiries')
@section('content')
<div class="admin-welcome"><div><div class="eyebrow">CUSTOMER MANAGEMENT</div><h1>Customer enquiries</h1><p>Track new messages, follow-ups, and archived requests from one inbox.</p></div></div>
<section class="admin-panel">
    <form method="get" action="{{ route('admin.enquiries') }}" class="booking-admin-filters">
        <label>Search<input name="search" value="{{ request('search') }}" placeholder="Name, email, phone, or subject"></label>
        <label>Status<select name="status"><option value="">All statuses</option>@foreach(['new' => 'New', 'in_progress' => 'In progress', 'responded' => 'Responded', 'archived' => 'Archived'] as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select></label>
        <button type="submit" class="button">Filter enquiries</button>
        <a class="text-link" href="{{ route('admin.enquiries.export', request()->only('search', 'status')) }}">Download filtered CSV</a>
    </form>
    <div class="table-scroll"><table><thead><tr><th>FROM</th><th>SERVICE</th><th>MESSAGE</th><th>RECEIVED</th><th>FOLLOW UP</th></tr></thead><tbody>
        @forelse($enquiries as $enquiry)
            <tr class="{{ $enquiry->is_read ? '' : 'enquiry-unread' }}">
                <td class="ref-cell">{{ $enquiry->name }} @unless($enquiry->is_read)<span class="status-pill">Unread</span>@endunless<small>{{ $enquiry->phone }} @if($enquiry->email) · {{ $enquiry->email }} @endif</small></td>
                <td>{{ $enquiry->service?->name ?? 'General enquiry' }}<small>{{ $enquiry->subject }}</small></td>
                <td class="message-cell">{{ $enquiry->message }}</td>
                <td>{{ $enquiry->created_at->timezone('Africa/Nairobi')->format('j M Y, H:i') }}</td>
                <td><form class="enquiry-admin-update" method="post" action="{{ route('admin.enquiries.update', $enquiry) }}">
                    @csrf @method('PATCH')
                    <label>Status<select name="status">@foreach(['new' => 'New', 'in_progress' => 'In progress', 'responded' => 'Responded', 'archived' => 'Archived'] as $value => $label)<option value="{{ $value }}" @selected($enquiry->status === $value)>{{ $label }}</option>@endforeach</select></label>
                    <input type="hidden" name="is_read" value="0"><label class="booking-inline-field"><input type="checkbox" name="is_read" value="1" @checked($enquiry->is_read)> Mark as read</label>
                    <label>Internal notes<textarea name="internal_notes" rows="2">{{ $enquiry->internal_notes }}</textarea></label>
                    <button class="mini-button" type="submit">Save follow-up</button>
                </form></td>
            </tr>
        @empty
            <tr><td colspan="5"><div class="admin-empty"><h3>No matching enquiries.</h3><p>Try another search, or new messages will appear here when customers contact you.</p></div></td></tr>
        @endforelse
    </tbody></table></div>
    <div class="pagination">{{ $enquiries->links() }}</div>
</section>
@endsection
