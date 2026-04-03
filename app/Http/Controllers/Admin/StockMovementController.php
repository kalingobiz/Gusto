<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function index(\App\Models\Ingredient $ingredient)
    {
        return \Inertia\Inertia::render('Ingredients/Movements', [
            'ingredient' => $ingredient,
            'movements' => $ingredient->stockMovements()
                ->with(['user', 'source'])
                ->latest()
                ->paginate(20)
        ]);
    }
}
