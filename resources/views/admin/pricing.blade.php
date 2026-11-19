@extends('admin.layout')
@section('title', 'Pricing')
@section('content')
<div class="admin-welcome"><div><div class="eyebrow">WEBSITE CONTENT</div><h1>Service pricing</h1><p>Manage starting prices, fixed prices and packages that require a quotation.</p></div></div>
@include('partials.errors')
<div class="admin-price-list">
    @foreach($services as $service)
        <section class="admin-panel">
            <h2>{{ $service->name }}</h2>
            @foreach($service->packages as $package)
                <details><summary>{{ $package->name }} · {{ $package->priceLabel() }}</summary>
                    @include('admin.price-form', ['item' => $package])
                </details>
            @endforeach
            <details><summary>Add a package</summary>
                @include('admin.price-form', ['item' => new \App\Models\PricePackage])
            </details>
        </section>
    @endforeach
</div>
@endsection
