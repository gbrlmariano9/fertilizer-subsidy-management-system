@extends('layouts.app')

@section('title', 'Withdrawals')

@section('content')
    <div class="page-intro withdrawals-intro">
        <span class="page-eyebrow">MERCHANT WALLET</span>
        <h1 class="page-heading">Withdrawals</h1>
        <p class="mb-0">Request a payout and keep track of your withdrawal history.</p>
    </div>

    <section class="withdrawal-hero" aria-labelledby="withdrawal-balance-heading">
        <div class="withdrawal-hero-copy">
            <span class="withdrawal-hero-label"><i class="bi bi-wallet2"></i> Available wallet balance</span>
            <h2 id="withdrawal-balance-heading"><span>₱</span>{{ number_format((float) $merchant->wallet_balance, 2) }}</h2>
            <p>Available for your next withdrawal request.</p>
        </div>
        <div class="withdrawal-hero-art" aria-hidden="true">
            <span class="withdrawal-hero-orbit orbit-one"></span>
            <span class="withdrawal-hero-orbit orbit-two"></span>
            <i class="bi bi-bank2"></i>
            <span class="withdrawal-hero-spark"><i class="bi bi-plus-lg"></i></span>
        </div>
        <div class="withdrawal-hero-footnote">
            <i class="bi bi-shield-check"></i>
            <span>Requests are reviewed before payout.</span>
        </div>
    </section>

    @if ($errors->any())
        <div class="alert alert-danger withdrawal-errors" role="alert">
            <strong>Please check your request.</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="withdrawal-layout">
        <section class="content-card withdrawal-form-card">
            <div class="withdrawal-section-heading">
                <span class="withdrawal-heading-icon"><i class="bi bi-arrow-up-right"></i></span>
                <div>
                    <h2>Request a withdrawal</h2>
                    <p>Choose an amount and where you’d like to receive it.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('merchant.withdrawals.store') }}" class="withdrawal-form">
                @csrf

                <div class="mb-3">
                    <label for="amount" class="form-label">Withdrawal amount</label>
                    <div class="input-group amount-input-group">
                        <span class="input-group-text">₱</span>
                        <input id="amount" name="amount" type="number" min="0.01" step="0.01"
                            max="{{ $merchant->wallet_balance }}" value="{{ old('amount') }}"
                            class="form-control @error('amount') is-invalid @enderror"
                            placeholder="0.00" required aria-describedby="amount-help">
                    </div>
                    <div id="amount-help" class="form-text">Up to ₱{{ number_format((float) $merchant->wallet_balance, 2) }} available.</div>
                    @error('amount') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="payout_method" class="form-label">Payout method</label>
                    <select id="payout_method" name="payout_method" required
                        class="form-select @error('payout_method') is-invalid @enderror">
                        <option value="">Choose a payout method</option>
                        <option value="gcash" @selected(old('payout_method') === 'gcash')>GCash</option>
                        <option value="bank_transfer" @selected(old('payout_method') === 'bank_transfer')>Bank transfer</option>
                    </select>
                    @error('payout_method') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-4">
                    <label for="payout_account" class="form-label">Account details</label>
                    <input id="payout_account" name="payout_account" type="text" maxlength="255"
                        value="{{ old('payout_account') }}" required
                        class="form-control @error('payout_account') is-invalid @enderror"
                        placeholder="GCash number or bank account details">
                    <div class="form-text">Double-check these details before sending your request.</div>
                    @error('payout_account') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn btn-success withdrawal-submit">
                    Submit withdrawal request <i class="bi bi-arrow-right ms-1"></i>
                </button>
            </form>
        </section>

        <aside class="withdrawal-help-card">
            <span class="withdrawal-help-icon"><i class="bi bi-info-circle"></i></span>
            <h2>What happens next?</h2>
            <p>Your request is recorded as pending while it awaits review.</p>
            <div class="withdrawal-step">
                <span>1</span><div><strong>Request received</strong><small>Your selected amount is reserved from your wallet.</small></div>
            </div>
            <div class="withdrawal-step">
                <span>2</span><div><strong>Request reviewed</strong><small>Check the history below for the latest status.</small></div>
            </div>
            <div class="withdrawal-help-note"><i class="bi bi-clock-history"></i> You can follow each request in Withdrawal history.</div>
        </aside>
    </div>

    <section class="content-card withdrawal-history-card">
        <div class="withdrawal-history-heading">
            <div>
                <span class="page-eyebrow">ACTIVITY</span>
                <h2>Withdrawal history</h2>
                <p>Review your previous payout requests and their status.</p>
            </div>
            <span class="history-count"><i class="bi bi-receipt"></i> {{ $withdrawals->total() }} {{ \Illuminate\Support\Str::plural('request', $withdrawals->total()) }}</span>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0 withdrawal-table">
                <thead>
                    <tr>
                        <th scope="col">Reference</th>
                        <th scope="col">Amount</th>
                        <th scope="col">Payout method</th>
                        <th scope="col">Status</th>
                        <th scope="col">Requested</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($withdrawals as $withdrawal)
                        <tr>
                            <td><span class="withdrawal-reference">{{ $withdrawal->reference }}</span></td>
                            <td class="withdrawal-amount">₱{{ number_format((float) $withdrawal->amount, 2) }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $withdrawal->payout_method)) }}</td>
                            <td><span class="withdrawal-status status-{{ $withdrawal->status }}"><span></span>{{ ucfirst(str_replace('_', ' ', $withdrawal->status)) }}</span></td>
                            <td>{{ $withdrawal->created_at->format('M d, Y · h:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="withdrawal-empty-state">
                                    <span><i class="bi bi-journal-text"></i></span>
                                    <strong>No withdrawal requests yet</strong>
                                    <small>Your submitted requests will appear here.</small>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($withdrawals->hasPages())
            <div class="withdrawal-pagination">{{ $withdrawals->links() }}</div>
        @endif
    </section>
@endsection
