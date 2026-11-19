@extends('admin.layout')
@section('title', 'Staff Access')
@section('content')
<div class="admin-welcome"><div><div class="eyebrow">ADMINISTRATION</div><h1>Staff access</h1><p>Assign the right level of access to existing accounts.</p></div></div>
<section class="admin-panel staff-access-panel">
    <div class="staff-access-intro">
        <span class="staff-access-intro-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM20 8v6m3-3h-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
        <div><strong>Manage staff permissions</strong><p>Create accounts through your secure account provisioning process, then assign access here. Primary administrators always retain full access.</p></div>
        <span class="staff-access-count">{{ $staffMembers->total() }} {{ \Illuminate\Support\Str::plural('account', $staffMembers->total()) }}</span>
    </div>
    <div class="table-scroll staff-access-table-scroll"><table class="admin-data-table staff-access-table"><thead><tr><th>STAFF MEMBER</th><th>ACCESS LEVEL</th><th class="staff-access-actions-heading">PERMISSIONS</th></tr></thead><tbody>
        @foreach($staffMembers as $staffMember)
            <tr><td class="staff-member-cell"><span class="staff-member-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($staffMember->name, 0, 1)) }}</span><span><strong>{{ $staffMember->name }}</strong><small>{{ $staffMember->email }}</small></span></td>
                <td>@if($staffMember->is_admin)<span class="status-pill staff-primary-role"><span aria-hidden="true">✓</span> Primary administrator</span>@else<span class="status-pill">{{ str($staffMember->role ?? 'No admin access')->replace('_', ' ')->title() }}</span>@endif</td>
                <td>@unless($staffMember->is_admin)
                    <form method="post" action="{{ route('admin.staff.update', $staffMember) }}" class="staff-role-form">
                        @csrf @method('PATCH')
                        <label class="visually-hidden" for="staff-role-{{ $staffMember->id }}">Access for {{ $staffMember->name }}</label>
                        <select id="staff-role-{{ $staffMember->id }}" name="role"><option value="">No admin access</option>@foreach(['administrator' => 'Administrator', 'booking_manager' => 'Booking manager', 'content_editor' => 'Content editor'] as $value => $label)<option value="{{ $value }}" @selected($staffMember->role === $value)>{{ $label }}</option>@endforeach</select>
                        <button class="mini-button" type="submit">Save access</button>
                    </form>
                @else<span class="staff-access-locked">Full access protected</span>@endunless</td>
            </tr>
        @endforeach
    </tbody></table></div>
    @if($staffMembers->hasPages())<div class="pagination">{{ $staffMembers->links() }}</div>@endif
</section>
@endsection
