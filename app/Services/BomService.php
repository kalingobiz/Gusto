<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\OrderItem;
use App\Models\StockDeduction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BomService
{
    public function deductStock(OrderItem $orderItem): void
    {
        $bomItems = $orderItem->menuItem->bomItems()->with('ingredient')->get();

        DB::transaction(function () use ($orderItem, $bomItems) {
            foreach ($bomItems as $bom) {
                $qty = $bom->quantity_per_serving * $orderItem->quantity;

                $deduction = StockDeduction::create([
                    'order_item_id' => $orderItem->id,
                    'ingredient_id' => $bom->ingredient_id,
                    'expected_qty'  => $qty,
                ]);

                $ingredient = Ingredient::find($bom->ingredient_id);
                $ingredient->decrement('current_stock', $qty);

                $deduction->stockMovements()->create([
                    'ingredient_id' => $ingredient->id,
                    'user_id' => null, // Processed by system
                    'quantity_change' => -$qty,
                    'balance_after' => $ingredient->fresh()->current_stock,
                    'notes' => 'Deduction for Order Item #' . $orderItem->id,
                ]);
            }
        });
    }

    public function reverseDeduction(OrderItem $orderItem): void
    {
        $deductions = StockDeduction::where('order_item_id', $orderItem->id)
            ->where('reversed', false)
            ->get();

        DB::transaction(function () use ($orderItem, $deductions) {
            foreach ($deductions as $deduction) {
                $ingredient = Ingredient::find($deduction->ingredient_id);
                $ingredient->increment('current_stock', $deduction->expected_qty);

                $deduction->update([
                    'reversed'    => true,
                    'reversed_at' => now(),
                ]);

                $deduction->stockMovements()->create([
                    'ingredient_id' => $ingredient->id,
                    'user_id' => auth()->id(),
                    'quantity_change' => $deduction->expected_qty,
                    'balance_after' => $ingredient->fresh()->current_stock,
                    'notes' => 'Void/Reversal for Order Item #' . $orderItem->id,
                ]);
            }
        });
    }

    public function getVarianceReport(Carbon $dateFrom, Carbon $dateTo): array
    {
        $rows = DB::table('ingredients as i')
            ->leftJoin('stock_deductions as sd', 'sd.ingredient_id', '=', 'i.id')
            ->leftJoin('order_items as oi', 'oi.id', '=', 'sd.order_item_id')
            ->leftJoin('orders as o', 'o.id', '=', 'oi.order_id')
            ->leftJoin('stock_intakes as si', function ($join) use ($dateFrom, $dateTo) {
                $join->on('si.ingredient_id', '=', 'i.id')
                    ->whereBetween('si.intake_date', [$dateFrom->toDateString(), $dateTo->toDateString()]);
            })
            ->select(
                'i.id',
                'i.name',
                'i.unit',
                'i.current_stock',
                'i.reorder_level',
                DB::raw('COALESCE(SUM(CASE WHEN o.status = "paid" AND sd.reversed = 0 AND o.created_at BETWEEN ? AND ? THEN sd.expected_qty ELSE 0 END), 0) as theoretical_used'),
                DB::raw('COALESCE(SUM(si.quantity), 0) as total_intake')
            )
            ->addBinding([$dateFrom, $dateTo], 'select')
            ->groupBy('i.id', 'i.name', 'i.unit', 'i.current_stock', 'i.reorder_level')
            ->orderBy('i.name')
            ->get();

        return $rows->map(function ($row) {
            $variance = $row->theoretical_used > 0
                ? round((($row->total_intake - $row->current_stock) - $row->theoretical_used), 4)
                : 0;

            $variance_pct = $row->theoretical_used > 0
                ? round(($variance / $row->theoretical_used) * 100, 2)
                : 0;

            return [
                'id'              => $row->id,
                'name'            => $row->name,
                'unit'            => $row->unit,
                'current_stock'   => $row->current_stock,
                'reorder_level'   => $row->reorder_level,
                'theoretical_used' => $row->theoretical_used,
                'total_intake'    => $row->total_intake,
                'variance'        => $variance,
                'variance_pct'    => $variance_pct,
                'is_flagged'      => abs($variance_pct) > 5,
                'is_low_stock'    => $row->current_stock <= $row->reorder_level,
            ];
        })->toArray();
    }
}
