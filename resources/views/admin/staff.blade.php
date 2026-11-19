@extends('admin.layout')
@section('title', 'Staff Access')
@section('content')
<div class="admin-welcome"><div><div class="eyebrow">ADMINISTRATION</div><h1>Staff access</h1><p>Assign the right level of access to existing accounts.</p></div></div>
<section class="admin-panel">
    <p class="small-note">This screen changes staff permissions. Create accounts through your existing secure account provisioning process. Primary administrators retain full access.</p>
    <div class="table-scroll"><table><thead><tr><th>STAFF MEMBER</th><th>ACCESS LEVEL</th><th></th></tr></thead><tbody>
        @foreach($staffMembers as $staffMember)
            <tr><td class="ref-cell">{{ $staffMember->name }}<small>{{ $staffMember->email }}</small></td>
                <td>@if($staffMember->is_admin)<span class="status-pill">Primary administrator</span>@else{{ str($staffMember->role ?? 'No admin access')->replace('_', ' ')->title() }}@endif</td>
                <td>@unless($staffMember->is_admin)
                    <form method="post" action="{{ route('admin.staff.update', $staffMember) }}" class="staff-role-form">
                        @csrf @method('PATCH')
                        <label class="visually-hidden" for="staff-role-{{ $staffMember->id }}">Access for {{ $staffMember->name }}</label>
                        <select id="staff-role-{{ $staffMember->id }}" name="role"><option value="">No admin access</option>@foreach(['administrator' => 'Administrator', 'booking_manager' => 'Booking manager', 'content_editor' => 'Content editor'] as $value => $label)<option value="{{ $value }}" @selected($staffMember->role === $value)>{{ $label }}</option>@endforeach</select>
                        <button class="mini-button" type="submit">Save access</button>
                    </form>
                @endunless</td>
            </tr>
        @endforeach
    </tbody></table></div>
    <div class="pagination">{{ $staffMembers->links() }}</div>
</section>
@endsection
