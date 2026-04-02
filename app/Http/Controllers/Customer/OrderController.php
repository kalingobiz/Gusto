<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\RestaurantTable;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function __construct(private OrderService $orders) {}

    public function menu(string $tableToken)
    {
        $table = RestaurantTable::where('token', $tableToken)->firstOrFail();

        $categories = Category::with(['menuItems' => fn ($q) => $q->available()->with('variants')])
            ->active()
            ->get()
            ->filter(fn ($c) => $c->menuItems->isNotEmpty());

        return Inertia::render('Customer/Menu', [
            'table'      => $table->only('id', 'number', 'capacity'),
            'tableToken' => $tableToken,
            'categories' => $categories->values(),
        ]);
    }

    public function place(Request $request, string $tableToken)
    {
        $data = $request->validate([
            'items'                  => 'required|array|min:1',
            'items.*.menu_item_id'   => 'required|exists:menu_items,id',
            'items.*.quantity'       => 'required|integer|min:1|max:20',
            'items.*.variant_id'     => 'nullable|exists:menu_item_variants,id',
            'items.*.notes'          => 'nullable|string|max:200',
            'notes'                  => 'nullable|string|max:500',
        ]);

        $order = $this->orders->placeOrder(
            $tableToken,
            $data['items'],
            null,
            'customer_qr',
            $data['notes'] ?? null
        );

        return Inertia::render('Customer/OrderConfirmed', [
            'order' => $order->load(['items.menuItem', 'restaurantTable']),
        ]);
    }
}
