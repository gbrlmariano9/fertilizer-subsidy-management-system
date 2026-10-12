@extends('layouts.app')

@section('title', 'Merchant Overview')

@section('content')
    <section class="workspace-hero merchant-home-hero">
        <div>
            <span class="workspace-kicker"><i class="bi bi-shop-window"></i> MERCHANT SERVICES</span>
            <h1>Welcome, {{ auth()->user()->name }}</h1>
            <p>Manage your stock and prepare fertilizer orders for verified beneficiaries.</p>
        </div>
        <a href="{{ route('merchant.pos.index') }}" class="btn btn-light workspace-hero-action">
            <i class="bi bi-plus-lg me-2"></i>Start a sale
        </a>
        <span class="workspace-hero-decoration" aria-hidden="true"><i class="bi bi-basket2"></i></span>
    </section>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-xl-4">
            <div class="content-card stat-card h-100">
                <span class="stat-icon"><i class="bi bi-shop"></i></span>
                <span class="stat-label">Merchant account</span>
                <strong class="stat-value">{{ auth()->user()->name }}</strong>
                <span class="stat-caption"><span class="account-status-dot"></span>Account active</span>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="content-card stat-card subsidy-stat h-100">
                <span class="stat-icon orange"><i class="bi bi-wallet2"></i></span>
                <span class="stat-label">Merchant wallet</span>
                <strong class="stat-value"><span class="currency-prefix">₱</span>{{ number_format((float) auth()->user()->wallet_balance, 2) }}</strong>
                <span class="stat-caption">Current wallet balance</span>
            </div>
        </div>
    </div>

    <div class="section-heading workspace-section-heading">
        <div><span class="page-eyebrow">YOUR WORKSPACE</span><h2>Quick access</h2><p>Choose a task to continue.</p></div>
    </div>
    <div class="row g-3 merchant-actions">
        <div class="col-12 col-lg-6">
            <a class="merchant-action-card" href="{{ route('merchant.pos.index') }}">
                <span class="merchant-action-icon"><i class="bi bi-cart3"></i></span>
                <span class="merchant-action-copy"><strong>Start a sale</strong><small>Verify a beneficiary and prepare an order.</small></span>
                <i class="bi bi-arrow-up-right merchant-action-arrow"></i>
            </a>
        </div>
        <div class="col-12 col-lg-6">
            <a class="merchant-action-card" href="{{ route('merchant.products.index') }}">
                <span class="merchant-action-icon orange"><i class="bi bi-box-seam"></i></span>
                <span class="merchant-action-copy"><strong>Manage inventory</strong><small>Update fertilizer products, prices, and stock.</small></span>
                <i class="bi bi-arrow-up-right merchant-action-arrow"></i>
            </a>
        </div>
        <div class="col-12 col-lg-6">
            <a class="merchant-action-card" href="{{ route('merchant.transactions.index') }}">
                <span class="merchant-action-icon"><i class="bi bi-receipt"></i></span>
                <span class="merchant-action-copy"><strong>View transactions</strong><small>Review orders and payment status.</small></span>
                <i class="bi bi-arrow-up-right merchant-action-arrow"></i>
            </a>
        </div>
        <div class="col-12 col-lg-6">
            <a class="merchant-action-card" href="{{ route('merchant.withdrawals.index') }}">
                <span class="merchant-action-icon orange"><i class="bi bi-cash-coin"></i></span>
                <span class="merchant-action-copy"><strong>Manage withdrawals</strong><small>Request a payout and track its progress.</small></span>
                <i class="bi bi-arrow-up-right merchant-action-arrow"></i>
            </a>
        </div>
    </div>
@endsection
