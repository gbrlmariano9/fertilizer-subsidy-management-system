@extends('layouts.app')

@section('title', 'Profile settings')

@section('content')
    <div class="page-intro mb-4">
        <span class="page-eyebrow">ACCOUNT</span>
        <h1 class="page-heading">Profile settings</h1>
        <p class="mb-0">Manage your personal details and account security.</p>
    </div>

    <section class="profile-summary-card">
        <span class="profile-summary-avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
        <span class="profile-summary-copy">
            <strong>{{ auth()->user()->name }}</strong>
            <small>{{ auth()->user()->email }}</small>
        </span>
        <span class="profile-role-chip"><i class="bi bi-patch-check"></i>{{ ucfirst(auth()->user()->role) }} account</span>
    </section>

    <div class="profile-sections">
        <section class="content-card profile-panel profile-information-panel">
            @include('profile.partials.update-profile-information-form')
        </section>
        <section class="content-card profile-panel profile-password-panel">
            @include('profile.partials.update-password-form')
        </section>
        <section class="content-card profile-panel profile-danger-panel">
            @include('profile.partials.delete-user-form')
        </section>
    </div>
@endsection
