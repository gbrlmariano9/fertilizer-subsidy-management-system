<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = Transaction::with([
            'farmer',
            'items',
        ])
            ->where('merchant_id', auth()->id())
            ->latest()
            ->paginate(15);

        return view('merchant.transactions.index', compact('transactions'));
    }
}
