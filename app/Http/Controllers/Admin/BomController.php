<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BomItem;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BomController extends Controller
{
    public function index()
    {
        return Inertia::render('Bom/Index', [
            'categories'  => Category::with('menuItems.bomItems.ingredient')
                ->orderBy('sort_order')
                ->get(),
            'ingredients' => Ingredient::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'menu_item_id'        => 'required|exists:menu_items,id',
            'ingredient_id'       => 'required|exists:ingredients,id',
            'quantity_per_serving' => 'required|numeric|min:0.0001',
        ]);

        BomItem::updateOrCreate(
            ['menu_item_id' => $data['menu_item_id'], 'ingredient_id' => $data['ingredient_id']],
            ['quantity_per_serving' => $data['quantity_per_serving']]
        );

        return back()->with('success', 'BOM item saved.');
    }

    public function destroy(BomItem $bom)
    {
        $bom->delete();
        return back()->with('success', 'BOM item removed.');
    }

    public function bulkUpdate(Request $request, MenuItem $menuItem)
    {
        $request->validate([
            'items'                     => 'required|array',
            'items.*.ingredient_id'     => 'required|exists:ingredients,id',
            'items.*.quantity_per_serving' => 'required|numeric|min:0.0001',
        ]);

        BomItem::where('menu_item_id', $menuItem->id)->delete();

        foreach ($request->items as $item) {
            BomItem::create([
                'menu_item_id'         => $menuItem->id,
                'ingredient_id'        => $item['ingredient_id'],
                'quantity_per_serving' => $item['quantity_per_serving'],
            ]);
        }

        return back()->with('success', 'BOM updated.');
    }
}
