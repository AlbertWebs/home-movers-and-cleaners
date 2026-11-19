@extends('admin.layout')
@section('title', 'Services')
@section('content')
<div class="admin-welcome"><div><div class="eyebrow">WEBSITE CONTENT</div><h1>Services</h1><p>Manage the catalog, service images, detailed content, FAQs and search metadata.</p></div></div>
@include('partials.errors')
<details class="admin-panel service-editor" open><summary>Create a service</summary>
    @include('admin.service-form', ['item' => new \App\Models\Service])
</details>
@foreach($services as $item)
    <details class="admin-panel service-editor"><summary><span>{{ $item->name }}</span><small class="service-editor-status {{ $item->is_active ? 'is-published' : 'is-hidden' }}">{{ $item->is_active ? 'Published' : 'Hidden' }}</small></summary>
        @include('admin.service-form')
    </details>
@endforeach
@endsection
