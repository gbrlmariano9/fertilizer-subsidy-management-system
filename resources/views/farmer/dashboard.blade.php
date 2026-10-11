@extends('layouts.app')

@section('title', 'Farmer Overview')

@section('content')
    <div class="page-intro mb-4">
        <span class="page-eyebrow">FARMER SERVICES</span>
        <h1 class="page-heading">Welcome, {{ auth()->user()->name }}</h1>
        <p class="mb-0">Here’s your subsidy overview and a quick look at your fertilizer orders.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-xl-4">
            <div class="content-card stat-card h-100">
                <span class="stat-icon"><i class="bi bi-person-vcard"></i></span>
                <span class="stat-label">Beneficiary ID</span>
                <strong class="stat-value">{{ auth()->user()->beneficiary_id ?? 'Not assigned' }}</strong>
                <span class="stat-caption">Your farmer account reference</span>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="content-card stat-card subsidy-stat h-100">
                <span class="stat-icon orange"><i class="bi bi-wallet2"></i></span>
                <span class="stat-label">Available subsidy</span>
                <strong class="stat-value"><span class="currency-prefix">₱</span>{{ number_format((float) auth()->user()->subsidy_balance, 2) }}</strong>
                <span class="stat-caption">Balance available to your account</span>
            </div>
        </div>
    </div>

    <section class="card farmer-orders-promo mb-3">
        <div class="card-body">
            <span class="promo-icon"><i class="bi bi-bag-check"></i></span>
            <div class="promo-copy">
                <h2 class="card-title">Your fertilizer orders</h2>
                <p class="card-text">Review order details, status, and payment information.</p>
            </div>
            <a href="{{ route('farmer.orders.index') }}" class="btn btn-primary">View orders <i class="bi bi-arrow-right ms-1"></i></a>
        </div>
    </section>

    <section class="card farmer-orders-promo mb-3">
        <div class="card-body">
            <a href="{{ route('farmer.scan') }}" class="btn btn-primary">
                Scan Merchant QR
            </a>
        </div>
    </section>

    <aside class="content-card farmer-tip">
        <span class="farmer-tip-icon"><i class="bi bi-lightbulb"></i></span>
        <div>
            <h2>Keep your beneficiary details handy</h2>
            <p>Your beneficiary ID helps merchants verify your subsidy when preparing an order.</p>
        </div>
    </aside>


@endsection
