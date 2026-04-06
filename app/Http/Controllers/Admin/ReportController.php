<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\OrderItemLog;
use App\Models\VoidLog;
use App\Services\BomService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function __construct(private BomService $bom) {}

    public function sales(Request $request)
    {
        $from = Carbon::parse($request->get('from', today()->startOfMonth()));
        $to   = Carbon::parse($request->get('to', today()->endOfDay()));

        $summary = Order::where('status', 'paid')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('COUNT(*) as order_count, SUM(total) as revenue, SUM(tax) as tax_collected')
            ->first();

        $byDay = Order::where('status', 'paid')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as orders, SUM(total) as revenue')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        $byItem = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('menu_items', 'menu_items.id', '=', 'order_items.menu_item_id')
            ->join('categories', 'categories.id', '=', 'menu_items.category_id')
            ->where('orders.status', 'paid')
            ->where('order_items.kitchen_status', '!=', 'voided')
            ->whereBetween('orders.created_at', [$from, $to])
            ->select(
                'menu_items.id',
                'menu_items.name',
                'categories.name as category',
                DB::raw('SUM(order_items.quantity) as total_qty'),
                DB::raw('SUM(order_items.line_total) as total_revenue')
            )
            ->groupBy('menu_items.id', 'menu_items.name', 'categories.name')
            ->orderByDesc('total_revenue')
            ->get();

        $byPaymentMethod = DB::table('payments')
            ->join('orders', 'orders.id', '=', 'payments.order_id')
            ->whereBetween('payments.created_at', [$from, $to])
            ->selectRaw('payments.method, COUNT(*) as count, SUM(payments.amount) as total')
            ->groupBy('payments.method')
            ->get();

        return Inertia::render('Reports/Sales', [
            'from'            => $from->toDateString(),
            'to'              => $to->toDateString(),
            'summary'         => $summary,
            'byDay'           => $byDay,
            'byItem'          => $byItem,
            'byPaymentMethod' => $byPaymentMethod,
        ]);
    }

    public function bomVariance(Request $request)
    {
        $from = Carbon::parse($request->get('from', today()->startOfMonth()));
        $to   = Carbon::parse($request->get('to', today()->endOfDay()));

        $data = $this->bom->getVarianceReport($from, $to);

        return Inertia::render('Reports/BomVariance', [
            'from'        => $from->toDateString(),
            'to'          => $to->toDateString(),
            'ingredients' => $data,
            'flagged'     => collect($data)->where('is_flagged', true)->count(),
        ]);
    }

    public function voids(Request $request)
    {
        $from = Carbon::parse($request->get('from', today()->startOfMonth()));
        $to   = Carbon::parse($request->get('to', today()->endOfDay()));

        $voids = VoidLog::with([
            'orderItem.menuItem',
            'orderItem.order.restaurantTable',
            'user:id,name',
            'approvedBy:id,name',
        ])
        ->whereBetween('created_at', [$from, $to])
        ->latest()
        ->paginate(50);

        $byUser = VoidLog::join('users', 'users.id', '=', 'void_logs.user_id')
            ->whereBetween('void_logs.created_at', [$from, $to])
            ->selectRaw('users.name, COUNT(*) as void_count')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('void_count')
            ->get();

        return Inertia::render('Reports/Voids', [
            'from'   => $from->toDateString(),
            'to'     => $to->toDateString(),
            'voids'  => $voids,
            'byUser' => $byUser,
        ]);
    }

    public function audit(Request $request)
    {
        $from = Carbon::parse($request->get('from', today()));
        $to   = Carbon::parse($request->get('to', today()->endOfDay()));

        $logs = OrderItemLog::with([
            'orderItem.menuItem',
            'orderItem.order.restaurantTable',
            'user:id,name',
        ])
        ->whereBetween('created_at', [$from, $to])
        ->latest('created_at')
        ->paginate(100);

        return Inertia::render('Reports/Audit', [
            'from' => $from->toDateString(),
            'to'   => $to->toDateString(),
            'logs' => $logs,
        ]);
    }

    public function stock()
    {
        $ingredients = Ingredient::orderBy('name')->get();
        $lowCount      = $ingredients->filter(fn ($i) => $i->current_stock > 0 && $i->current_stock <= $i->low_stock_threshold)->count();
        $criticalCount = $ingredients->filter(fn ($i) => $i->current_stock <= 0)->count();

        return Inertia::render('Reports/Stock', [
            'ingredients'   => $ingredients,
            'lowCount'      => $lowCount,
            'criticalCount' => $criticalCount,
        ]);
    }

    public function itemPerformance(Request $request)
    {
        $from = Carbon::parse($request->get('from', today()->startOfMonth()));
        $to   = Carbon::parse($request->get('to', today()->endOfDay()));

        $items = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('menu_items', 'menu_items.id', '=', 'order_items.menu_item_id')
            ->join('categories', 'categories.id', '=', 'menu_items.category_id')
            ->where('orders.status', 'paid')
            ->where('order_items.kitchen_status', '!=', 'voided')
            ->whereBetween('orders.created_at', [$from, $to])
            ->select(
                'menu_items.id',
                'menu_items.name',
                'categories.name as category',
                DB::raw('SUM(order_items.quantity) as total_qty'),
                DB::raw('SUM(order_items.line_total) as total_revenue')
            )
            ->groupBy('menu_items.id', 'menu_items.name', 'categories.name')
            ->orderByDesc('total_revenue')
            ->get();

        $totalRevenue = $items->sum('total_revenue');

        return Inertia::render('Reports/ItemPerformance', [
            'from'         => $from->toDateString(),
            'to'           => $to->toDateString(),
            'items'        => $items,
            'totalRevenue' => $totalRevenue,
        ]);
    }

    public function hourlySales(Request $request)
    {
        $from = Carbon::parse($request->get('from', today()->startOfMonth()));
        $to   = Carbon::parse($request->get('to', today()->endOfDay()));

        $driver = DB::getDriverName();
        $hourExpr = $driver === 'sqlite'
            ? "CAST(strftime('%H', created_at) AS INTEGER)"
            : 'HOUR(created_at)';

        $byHour = DB::table('orders')
            ->where('status', 'paid')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw("$hourExpr as hour, COUNT(*) as orders, SUM(total) as revenue")
            ->groupBy(DB::raw($hourExpr))
            ->orderBy('hour')
            ->get();

        $peakHour     = $byHour->sortByDesc('revenue')->first();
        $totalOrders  = $byHour->sum('orders');
        $totalRevenue = $byHour->sum('revenue');

        return Inertia::render('Reports/HourlySales', [
            'from'         => $from->toDateString(),
            'to'           => $to->toDateString(),
            'byHour'       => $byHour->values(),
            'peakHour'     => $peakHour,
            'totalOrders'  => $totalOrders,
            'totalRevenue' => $totalRevenue,
        ]);
    }
}
