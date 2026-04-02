<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantTable;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function __construct(private OrderService $orders) {}

    public function index()
    {
        return Inertia::render('Tables/Index', [
            'tables' => RestaurantTable::with(['activeSession.orders' => function ($q) {
                $q->active()->with('items.menuItem');
            }])->orderBy('number')->get(),
        ]);
    }

    public function create(Request $request)
    {
        $tableId = $request->get('table_id');
        $table = RestaurantTable::findOrFail($tableId);

        return Inertia::render('Orders/Create', [
            'table'      => $table,
            'categories' => \App\Models\Category::with('menuItems.variants')
                ->active()
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'table_token' => 'required|string',
            'items'       => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity'     => 'required|integer|min:1',
            'items.*.variant_id'   => 'nullable|exists:menu_item_variants,id',
            'items.*.notes'        => 'nullable|string|max:255',
            'notes'       => 'nullable|string|max:500',
        ]);

        $order = $this->orders->placeOrder(
            $data['table_token'],
            $data['items'],
            auth()->id(),
            'staff',
            $data['notes'] ?? null
        );

        return redirect()->route('orders.show', $order->id)->with('success', 'Order placed.');
    }

    public function show(Order $order)
    {
        return Inertia::render('Orders/Show', [
            'order' => $order->load([
                'restaurantTable',
                'items.menuItem',
                'items.variant',
                'items.voidLog.user',
                'payment',
                'user:id,name',
            ]),
        ]);
    }

    public function markServed(Order $order)
    {
        $this->orders->markServed($order, auth()->id());
        return back()->with('success', 'Order marked as served.');
    }

    public function voidItem(Request $request, Order $order, OrderItem $item)
    {
        $data = $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        if ($item->order_id !== $order->id) {
            abort(403);
        }

        $this->orders->voidItem($item, $data['reason'], auth()->id(), auth()->id());

        return back()->with('success', 'Item voided.');
    }
}
