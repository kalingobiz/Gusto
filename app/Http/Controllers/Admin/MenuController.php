<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class MenuController extends Controller
{
    public function index()
    {
        return Inertia::render('Menu/Index', [
            'categories' => Category::with('menuItems.variants')
                ->orderBy('sort_order')
                ->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Menu/Form', [
            'categories' => Category::active()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id'  => 'required|exists:categories,id',
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'price'        => 'required|numeric|min:0',
            'is_available' => 'boolean',
            'sort_order'   => 'integer|min:0',
            'image'        => 'nullable|image|mimes:jpg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('menu', 'public');
        }

        unset($data['image']);
        MenuItem::create($data);

        return redirect()->route('admin.menu.index')->with('success', 'Menu item created.');
    }

    public function edit(MenuItem $menu)
    {
        return Inertia::render('Menu/Form', [
            'item'       => $menu->load('variants'),
            'categories' => Category::active()->get(),
        ]);
    }

    public function update(Request $request, MenuItem $menu)
    {
        $data = $request->validate([
            'category_id'  => 'required|exists:categories,id',
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'price'        => 'required|numeric|min:0',
            'is_available' => 'boolean',
            'sort_order'   => 'integer|min:0',
            'image'        => 'nullable|image|mimes:jpg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($menu->image_path) {
                Storage::disk('public')->delete($menu->image_path);
            }
            $data['image_path'] = $request->file('image')->store('menu', 'public');
        }

        unset($data['image']);
        $menu->update($data);

        return redirect()->route('admin.menu.index')->with('success', 'Menu item updated.');
    }

    public function destroy(MenuItem $menu)
    {
        if ($menu->image_path) {
            Storage::disk('public')->delete($menu->image_path);
        }
        $menu->delete();

        return redirect()->route('admin.menu.index')->with('success', 'Menu item deleted.');
    }

    public function categories()
    {
        return Inertia::render('Menu/Categories', [
            'categories' => Category::withCount('menuItems')->orderBy('sort_order')->get(),
        ]);
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'description' => 'nullable|string',
            'color'      => 'nullable|string|size:7',
            'sort_order' => 'integer|min:0',
            'is_active'  => 'boolean',
        ]);

        Category::create($data);

        return back()->with('success', 'Category created.');
    }

    public function updateCategory(Request $request, Category $category)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'description' => 'nullable|string',
            'color'      => 'nullable|string|size:7',
            'sort_order' => 'integer|min:0',
            'is_active'  => 'boolean',
        ]);

        $category->update($data);

        return back()->with('success', 'Category updated.');
    }

    public function destroyCategory(Category $category)
    {
        $category->delete();
        return back()->with('success', 'Category deleted.');
    }
}
