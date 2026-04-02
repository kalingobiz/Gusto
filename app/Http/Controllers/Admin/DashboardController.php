<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\VoidLog;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $today = today();

        $todayRevenue = Order::where('status', 'paid')
            ->whereDate('created_at', $today)
            ->sum('total');

        $todayOrders = Order::whereDate('created_at', $today)->count();

        $activeOrders = Order::active()->count();

        $lowStockCount = Ingredient::lowStock()->count();

        $todayVoids = VoidLog::whereDate('created_at', $today)->count();

        $recentOrders = Order::with(['restaurantTable', 'items.menuItem'])
            ->whereDate('created_at', $today)
            ->latest()
            ->take(10)
            ->get();

        $topItems = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('menu_items', 'menu_items.id', '=', 'order_items.menu_item_id')
            ->where('orders.status', 'paid')
            ->whereDate('orders.created_at', $today)
            ->where('order_items.kitchen_status', '!=', 'voided')
            ->select('menu_items.name', DB::raw('SUM(order_items.quantity) as total_qty'), DB::raw('SUM(order_items.line_total) as total_revenue'))
            ->groupBy('menu_items.id', 'menu_items.name')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        $lowStockIngredients = Ingredient::lowStock()->take(5)->get();

        return Inertia::render('Dashboard', [
            'stats' => [
                'today_revenue' => $todayRevenue,
                'today_orders'  => $todayOrders,
                'active_orders' => $activeOrders,
                'low_stock'     => $lowStockCount,
                'today_voids'   => $todayVoids,
            ],
            'recentOrders'        => $recentOrders,
            'topItems'            => $topItems,
            'lowStockIngredients' => $lowStockIngredients,
        ]);
    }
}
