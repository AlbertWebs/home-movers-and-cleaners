@extends('admin.layout')
@section('title', 'Pricing')
@section('content')
<div class="admin-welcome"><div><div class="eyebrow">WEBSITE CONTENT</div><h1>Service pricing</h1><p>Manage starting prices, fixed prices and packages that require a quotation.</p></div></div>
@include('partials.errors')
<div class="admin-price-list">
    @foreach($services as $service)
        <section class="admin-panel admin-price-service">
            <header class="admin-price-service-heading">
                <div><h2>{{ $service->name }}</h2><p>{{ $service->summary }}</p></div>
                <span class="admin-price-package-count">{{ $service->packages->count() }} packages</span>
            </header>
            <div class="admin-price-packages">
                @foreach($service->packages as $package)
                <details class="admin-price-package">
                    <summary><span>{{ $package->name }}</span><strong>{{ $package->priceLabel() }}</strong></summary>
                    @include('admin.price-form', ['item' => $package])
                </details>
                @endforeach
            </div>
            <details class="admin-price-package admin-price-package-add">
                <summary><span>Add a package</span><strong aria-hidden="true">+</strong></summary>
                @include('admin.price-form', ['item' => new \App\Models\PricePackage])
            </details>
        </section>
    @endforeach
</div>
@endsection
