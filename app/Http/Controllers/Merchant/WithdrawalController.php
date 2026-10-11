<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WithdrawalController extends Controller
{
    public function index()
    {
        $merchant = auth()->user();

        $withdrawals = Withdrawal::where('merchant_id', $merchant->id)
            ->latest()
            ->paginate(10);

        return view('merchant.withdrawals.index', compact(
            'merchant',
            'withdrawals'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'payout_method' => [
                'required',
                'in:gcash,bank_transfer',
            ],
            'payout_account' => ['required', 'string', 'max:255'],
        ]);

        $withdrawal = DB::transaction(function () use ($validated) {
            $merchant = User::whereKey(auth()->id())
                ->lockForUpdate()
                ->firstOrFail();

            $amountCents = (int) round(
                (float) $validated['amount'] * 100
            );

            $balanceCents = (int) round(
                (float) $merchant->wallet_balance * 100
            );

            if ($amountCents > $balanceCents) {
                throw ValidationException::withMessages([
                    'amount' => 'Insufficient wallet balance.',
                ]);
            }

            // Reserve the requested amount immediately.
            $merchant->decrement(
                'wallet_balance',
                $validated['amount']
            );

            return Withdrawal::create([
                'merchant_id' => $merchant->id,
                'amount' => $validated['amount'],
                'payout_method' => $validated['payout_method'],
                'payout_account' => $validated['payout_account'],
                'status' => 'pending',
                'reference' => (string) Str::uuid(),
            ]);
        }, 3);

        return redirect()
            ->route('merchant.withdrawals.index')
            ->with(
                'success',
                'Withdrawal request submitted. Please wait for approval.'
            );
    }
}
