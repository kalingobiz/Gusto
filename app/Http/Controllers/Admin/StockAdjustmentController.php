<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'ingredient_id' => 'required|exists:ingredients,id',
            'type' => 'required|string|max:50',
            'quantity' => 'required|numeric', // can be negative or positive
            'notes' => 'nullable|string'
        ]);

        $data['user_id'] = auth()->id();

        $ingredient = \App\Models\Ingredient::find($data['ingredient_id']);

        DB::transaction(function () use ($data, $ingredient) {
            $adjustment = \App\Models\StockAdjustment::create($data);

            $ingredient->increment('current_stock', $data['quantity']);

            $adjustment->stockMovements()->create([
                'ingredient_id' => $ingredient->id,
                'user_id' => auth()->id(),
                'quantity_change' => $data['quantity'],
                'balance_after' => $ingredient->fresh()->current_stock,
                'notes' => "Adjustment ({$data['type']}): " . ($data['notes'] ?? ''),
            ]);
        });

        return back()->with('success', 'Stock adjusted successfully.');
    }
}
