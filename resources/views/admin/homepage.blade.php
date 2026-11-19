@extends('admin.layout')
@section('title', 'Homepage')
@section('content')
<div class="admin-welcome"><div><div class="eyebrow">WEBSITE CONTENT</div><h1>Homepage sections</h1><p>Edit section copy, visibility and order.</p></div></div>
@include('partials.errors')
@php($order = $content['order'] ?? array_keys($defaults))
<form class="admin-panel settings-form" method="post" action="{{ route('admin.homepage.update') }}" enctype="multipart/form-data">
    @csrf @method('PUT')
    @foreach($defaults as $section => $fields)
        <details class="settings-section">
            <summary>{{ $section === 'cta' ? 'Fresh start call to action' : ucfirst($section) }}</summary>
            @php($position = array_search($section, $order, true))
            <label>Section order<input type="number" name="position[{{ $section }}]" required min="1" max="99" value="{{ old('position.'.$section, $position !== false ? $position + 1 : count($order) + $loop->iteration) }}"></label>
            @unless($section === 'hero')
                <label class="booking-inline-field"><input type="checkbox" name="visible[{{ $section }}]" value="1" @checked(in_array($section, $order, true))> Show section</label>
            @endunless
            @foreach($fields as $key => $default)
                <label>{{ ucfirst(str_replace('_', ' ', $key)) }}<textarea name="copy[{{ $section }}][{{ $key }}]" rows="{{ strlen($default) > 80 ? 3 : 1 }}" maxlength="500" required>{{ old('copy.'.$section.'.'.$key, $content['copy'][$section.'.'.$key] ?? $default) }}</textarea></label>
            @endforeach
            @if(in_array($section, ['hero', 'about'], true))
                <label>Replace photo<input type="file" name="images[{{ $section }}]" accept="image/jpeg,image/png,image/webp"></label>
            @endif
            @if(in_array($section, ['hero', 'cta'], true))
                <a class="text-link" href="{{ route('admin.settings') }}">Edit {{ $section === 'hero' ? 'hero heading and tagline' : 'call to action text and buttons' }}</a>
            @elseif($section === 'services')
                <a class="text-link" href="{{ route('admin.services') }}">Edit service names, photos and visibility</a>
            @elseif($section === 'steps')
                <label>Replace team photo<input type="file" name="images[steps_team]" accept="image/jpeg,image/png,image/webp"></label>
                <label>Replace results photo<input type="file" name="images[steps_result]" accept="image/jpeg,image/png,image/webp"></label>
            @endif
        </details>
    @endforeach
    <button class="button" type="submit">Save homepage</button>
</form>
@endsection
