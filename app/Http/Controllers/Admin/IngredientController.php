<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\StockIntake;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class IngredientController extends Controller
{
    public function index(Request $request)
    {
        $query = Ingredient::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->get('filter') === 'low_stock') {
            $query->lowStock();
        }

        return Inertia::render('Ingredients/Index', [
            'ingredients' => $query->orderBy('name')->paginate(15)->through(fn ($i) => [
                'id' => $i->id,
                'name' => $i->name,
                'unit' => $i->unit,
                'current_stock' => $i->current_stock,
                'reorder_level' => $i->reorder_level,
                'cost_per_unit' => $i->cost_per_unit,
                'is_low_stock' => $i->isLowStock(),
            ])->withQueryString(),
            'filters' => $request->only(['search', 'filter']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'unit'          => 'required|string|max:50',
            'current_stock' => 'required|numeric|min:0',
            'reorder_level' => 'required|numeric|min:0',
            'cost_per_unit' => 'required|numeric|min:0',
        ]);

        Ingredient::create($data);

        return back()->with('success', 'Ingredient created.');
    }

    public function update(Request $request, Ingredient $ingredient)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'unit'          => 'required|string|max:50',
            'reorder_level' => 'required|numeric|min:0',
            'cost_per_unit' => 'required|numeric|min:0',
        ]);

        $ingredient->update($data);

        return back()->with('success', 'Ingredient updated.');
    }

    public function destroy(Ingredient $ingredient)
    {
        $ingredient->delete();
        return back()->with('success', 'Ingredient deleted.');
    }

    public function intake(Request $request)
    {
        $data = $request->validate([
            'ingredient_id' => 'required|exists:ingredients,id',
            'quantity'      => 'required|numeric|min:0.0001',
            'cost_per_unit' => 'required|numeric|min:0',
            'supplier'      => 'nullable|string|max:255',
            'reference'     => 'nullable|string|max:255',
            'intake_date'   => 'required|date',
            'notes'         => 'nullable|string',
        ]);

        $data['user_id'] = auth()->id();

        $ingredient = Ingredient::find($data['ingredient_id']);

        DB::transaction(function () use ($data, $ingredient) {
            $intake = StockIntake::create($data);

            $ingredient->increment('current_stock', $data['quantity']);

            $intake->stockMovements()->create([
                'ingredient_id' => $ingredient->id,
                'user_id' => auth()->id(),
                'quantity_change' => $data['quantity'],
                'balance_after' => $ingredient->fresh()->current_stock,
                'notes' => 'Intake from ' . ($data['supplier'] ?? 'Unknown Supplier'),
            ]);
        });

        return back()->with('success', 'Stock intake recorded.');
    }

    public function intakeHistory(Ingredient $ingredient)
    {
        return Inertia::render('Ingredients/IntakeHistory', [
            'ingredient' => $ingredient,
            'intakes'    => $ingredient->stockIntakes()
                ->with('user:id,name')
                ->latest()
                ->paginate(20),
        ]);
    }
}
