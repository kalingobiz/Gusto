<?php

namespace App\Http\Controllers\Kitchen;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DisplayController extends Controller
{
    public function __construct(private OrderService $orders) {}

    public function index()
    {
        $activeOrders = Order::whereIn('status', ['confirmed', 'in_progress', 'ready'])
            ->with([
                'restaurantTable:id,number',
                'items' => fn ($q) => $q->whereIn('kitchen_status', ['pending', 'cooking', 'ready'])
                    ->with('menuItem:id,name'),
            ])
            ->latest()
            ->get()
            ->filter(fn ($order) => $order->items->isNotEmpty());

        return Inertia::render('Kitchen/Display', [
            'orders' => $activeOrders->values(),
        ]);
    }

    public function updateStatus(Request $request, OrderItem $item)
    {
        $data = $request->validate([
            'kitchen_status' => 'required|in:cooking,ready,done',
        ]);

        match ($data['kitchen_status']) {
            'cooking' => $this->orders->markItemCooking($item, auth()->id()),
            'ready'   => $this->orders->markItemReady($item, auth()->id()),
            'done'    => $this->orders->markItemDone($item, auth()->id()),
        };

        return response()->json(['success' => true]);
    }

    public function pending()
    {
        $orders = Order::whereIn('status', ['confirmed', 'in_progress', 'ready'])
            ->with([
                'restaurantTable:id,number',
                'items' => fn ($q) => $q->whereIn('kitchen_status', ['pending', 'cooking', 'ready'])
                    ->with('menuItem:id,name'),
            ])
            ->latest()
            ->get()
            ->filter(fn ($order) => $order->items->isNotEmpty());

        return response()->json($orders->values());
    }
}
