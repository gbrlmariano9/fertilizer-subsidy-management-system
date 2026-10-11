<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Transaction::with(['merchant', 'items'])
            ->where('farmer_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return view('farmer.orders.index', compact('orders'));
    }

    public function show(Request $request, string $uuid)
    {
        $order = Transaction::with(['merchant', 'items'])
            ->where('uuid', $uuid)
            ->where('farmer_id', $request->user()->id)
            ->firstOrFail();

        return view('farmer.orders.show', compact('order'));
    }
}
