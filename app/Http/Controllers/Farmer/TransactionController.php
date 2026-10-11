<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $farmer = $request->user();
        $transactions = $farmer->farmerTransactions()
            ->with(['merchant:id,name', 'items'])
            ->latest()
            ->paginate(10);

        $transactionCount = $farmer->farmerTransactions()->count();
        $paidTotal = $farmer->farmerTransactions()
            ->where('status', 'paid')
            ->sum('total_amount');
        $pendingCount = $farmer->farmerTransactions()
            ->where('status', 'pending')
            ->count();

        return view('farmer.transactions.index', compact(
            'transactions',
            'transactionCount',
            'paidTotal',
            'pendingCount',
        ));
    }
}
