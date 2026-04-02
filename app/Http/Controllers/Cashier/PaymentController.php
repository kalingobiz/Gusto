<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private OrderService $orders) {}

    public function store(Request $request)
    {
        $data = $request->validate([
            'order_id'  => 'required|exists:orders,id',
            'method'    => 'required|in:cash,bank_transfer',
            'amount'    => 'required|numeric|min:0.01',
            'reference' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:100',
            'notes'     => 'nullable|string',
        ]);

        $order = Order::findOrFail($data['order_id']);

        if ($order->status === 'paid') {
            return back()->withErrors(['order' => 'Order is already paid.']);
        }

        $payment = $this->orders->recordPayment(
            $order,
            $data['method'],
            $data['amount'],
            auth()->id(),
            $data['reference'] ?? null,
            $data['bank_name'] ?? null
        );

        return redirect()->route('orders.show', $order->id)->with('success', 'Payment recorded.');
    }
}
