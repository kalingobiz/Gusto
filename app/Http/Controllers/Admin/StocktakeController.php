<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StocktakeController extends Controller
{
    public function index()
    {
        return \Inertia\Inertia::render('Stocktakes/Index', [
            'stocktakes' => \App\Models\Stocktake::with('user')->latest()->paginate(15)
        ]);
    }

    public function create()
    {
        $ingredients = \App\Models\Ingredient::orderBy('name')->get(['id', 'name', 'unit', 'current_stock', 'cost_per_unit']);
        return \Inertia\Inertia::render('Stocktakes/Create', [
            'ingredients' => $ingredients
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'notes' => 'nullable|string',
            'items' => 'required|array',
            'items.*.ingredient_id' => 'required|exists:ingredients,id',
            'items.*.actual_quantity' => 'required|numeric|min:0',
        ]);

        $stocktake = \App\Models\Stocktake::create([
            'user_id' => auth()->id(),
            'notes' => $request->notes,
            'status' => 'draft',
        ]);

        foreach ($request->items as $item) {
            $ingredient = \App\Models\Ingredient::find($item['ingredient_id']);
            $expected = $ingredient->current_stock;
            $actual = $item['actual_quantity'];
            
            $stocktake->items()->create([
                'ingredient_id' => $ingredient->id,
                'expected_quantity' => $expected,
                'actual_quantity' => $actual,
                'difference' => $actual - $expected,
                'unit_cost' => $ingredient->cost_per_unit,
            ]);
        }

        return redirect()->route('admin.stocktakes.show', $stocktake->id)
            ->with('success', 'Stock audit drafted successfully.');
    }

    public function show(\App\Models\Stocktake $stocktake)
    {
        $stocktake->load(['items.ingredient', 'user']);
        return \Inertia\Inertia::render('Stocktakes/Show', [
            'stocktake' => $stocktake
        ]);
    }

    public function complete(\App\Models\Stocktake $stocktake)
    {
        if ($stocktake->status !== 'draft') {
            return back()->with('error', 'Only draft stock audits can be completed.');
        }

        $stocktake->load('items.ingredient');

        DB::transaction(function () use ($stocktake) {
            foreach ($stocktake->items as $item) {
                if ($item->difference != 0) {
                    $ingredient = $item->ingredient;

                    $adjustment = \App\Models\StockAdjustment::create([
                        'ingredient_id' => $ingredient->id,
                        'user_id' => auth()->id(),
                        'type' => 'audit_correction',
                        'quantity' => $item->difference,
                        'notes' => 'Correction from Audit ' . $stocktake->reference_number
                    ]);

                    $ingredient->increment('current_stock', $item->difference);

                    $adjustment->stockMovements()->create([
                        'ingredient_id' => $ingredient->id,
                        'user_id' => auth()->id(),
                        'quantity_change' => $item->difference,
                        'balance_after' => $ingredient->fresh()->current_stock,
                        'notes' => 'Audit Correction: ' . $stocktake->reference_number,
                    ]);
                }
            }

            $stocktake->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        });

        return back()->with('success', 'Stock audit completed and inventory adjusted.');
    }
}
