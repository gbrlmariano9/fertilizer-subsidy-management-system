@extends('layouts.app')

@section('title', 'Farmer Overview')

@section('content')
    <section class="workspace-hero farmer-home-hero">
        <div>
            <span class="workspace-kicker"><i class="bi bi-sunrise"></i> FARMER SERVICES</span>
            <h1>Welcome, {{ auth()->user()->name }}</h1>
            <p>Check your subsidy balance, scan a merchant QR, or review your recent orders.</p>
        </div>
        <span class="workspace-hero-decoration" aria-hidden="true"><i class="bi bi-flower1"></i></span>
    </section>

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

    <section class="farmer-scan-promo" aria-labelledby="farmer-scan-heading">
        <span class="farmer-scan-promo-icon"><i class="bi bi-qr-code-scan"></i></span>
        <div class="farmer-scan-promo-copy">
            <span class="page-eyebrow">READY TO SHOP?</span>
            <h2 id="farmer-scan-heading">Scan a merchant QR</h2>
            <p>Scan the order QR shared by your merchant to review and pay for your fertilizer.</p>
        </div>
        <a href="{{ route('farmer.scan') }}" class="btn btn-success farmer-scan-promo-action">
            Open scanner <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </section>

    <aside class="content-card farmer-tip">
        <span class="farmer-tip-icon"><i class="bi bi-lightbulb"></i></span>
        <div>
            <h2>Keep your beneficiary details handy</h2>
            <p>Your beneficiary ID helps merchants verify your subsidy when preparing an order.</p>
        </div>
    </aside>


@endsection
