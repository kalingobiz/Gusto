<?php

namespace Database\Seeders;

use App\Models\BomItem;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\MenuItem;
use App\Models\RestaurantTable;
use App\Models\User;
use App\Services\QrCodeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Staff Accounts ─────────────────────────────────────────────────────
        $admin = User::create([
            'name'     => 'Owner Admin',
            'email'    => 'admin@gusto.local',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        User::create([
            'name'     => 'Cashier One',
            'email'    => 'cashier@gusto.local',
            'password' => Hash::make('password'),
            'role'     => 'cashier',
        ]);

        User::create([
            'name'     => 'Kitchen Staff',
            'email'    => 'kitchen@gusto.local',
            'password' => Hash::make('password'),
            'role'     => 'kitchen',
        ]);

        // ── Categories ────────────────────────────────────────────────────────
        $food = Category::create(['name' => 'Food', 'color' => '#f59e0b', 'sort_order' => 1]);
        $bev  = Category::create(['name' => 'Beverages', 'color' => '#3b82f6', 'sort_order' => 2]);
        $dessert = Category::create(['name' => 'Desserts', 'color' => '#ec4899', 'sort_order' => 3]);

        // ── Ingredients ───────────────────────────────────────────────────────
        $flour  = Ingredient::create(['name' => 'All-Purpose Flour', 'unit' => 'kg', 'current_stock' => 20, 'reorder_level' => 5, 'cost_per_unit' => 1.50]);
        $beef   = Ingredient::create(['name' => 'Ground Beef', 'unit' => 'kg', 'current_stock' => 10, 'reorder_level' => 2, 'cost_per_unit' => 8.00]);
        $cheese = Ingredient::create(['name' => 'Cheddar Cheese', 'unit' => 'kg', 'current_stock' => 5, 'reorder_level' => 1, 'cost_per_unit' => 12.00]);
        $bun    = Ingredient::create(['name' => 'Burger Bun', 'unit' => 'pcs', 'current_stock' => 100, 'reorder_level' => 20, 'cost_per_unit' => 0.50]);
        $milk   = Ingredient::create(['name' => 'Full Cream Milk', 'unit' => 'litre', 'current_stock' => 15, 'reorder_level' => 3, 'cost_per_unit' => 1.20]);
        $coffee = Ingredient::create(['name' => 'Espresso Beans', 'unit' => 'kg', 'current_stock' => 3, 'reorder_level' => 0.5, 'cost_per_unit' => 20.00]);
        $cola   = Ingredient::create(['name' => 'Cola Syrup', 'unit' => 'litre', 'current_stock' => 10, 'reorder_level' => 2, 'cost_per_unit' => 3.50]);
        $potato = Ingredient::create(['name' => 'Potatoes', 'unit' => 'kg', 'current_stock' => 25, 'reorder_level' => 5, 'cost_per_unit' => 0.80]);
        $sugar  = Ingredient::create(['name' => 'Sugar', 'unit' => 'kg', 'current_stock' => 8, 'reorder_level' => 2, 'cost_per_unit' => 1.00]);
        $eggs   = Ingredient::create(['name' => 'Eggs', 'unit' => 'pcs', 'current_stock' => 60, 'reorder_level' => 12, 'cost_per_unit' => 0.30]);

        // ── Menu Items & BOM ──────────────────────────────────────────────────
        $burger = MenuItem::create([
            'category_id' => $food->id, 'name' => 'Classic Burger',
            'description' => 'Juicy beef patty with cheese and fresh toppings',
            'price' => 12.99, 'sort_order' => 1,
        ]);
        BomItem::create(['menu_item_id' => $burger->id, 'ingredient_id' => $beef->id, 'quantity_per_serving' => 0.2]);
        BomItem::create(['menu_item_id' => $burger->id, 'ingredient_id' => $bun->id, 'quantity_per_serving' => 1]);
        BomItem::create(['menu_item_id' => $burger->id, 'ingredient_id' => $cheese->id, 'quantity_per_serving' => 0.05]);

        $fries = MenuItem::create([
            'category_id' => $food->id, 'name' => 'French Fries',
            'description' => 'Crispy golden fries',
            'price' => 4.99, 'sort_order' => 2,
        ]);
        BomItem::create(['menu_item_id' => $fries->id, 'ingredient_id' => $potato->id, 'quantity_per_serving' => 0.3]);

        $pizza = MenuItem::create([
            'category_id' => $food->id, 'name' => 'Margherita Pizza',
            'description' => 'Classic tomato and mozzarella',
            'price' => 14.99, 'sort_order' => 3,
        ]);
        BomItem::create(['menu_item_id' => $pizza->id, 'ingredient_id' => $flour->id, 'quantity_per_serving' => 0.3]);
        BomItem::create(['menu_item_id' => $pizza->id, 'ingredient_id' => $cheese->id, 'quantity_per_serving' => 0.15]);

        $latte = MenuItem::create([
            'category_id' => $bev->id, 'name' => 'Latte',
            'description' => 'Espresso with steamed milk',
            'price' => 4.50, 'sort_order' => 1,
        ]);
        BomItem::create(['menu_item_id' => $latte->id, 'ingredient_id' => $coffee->id, 'quantity_per_serving' => 0.018]);
        BomItem::create(['menu_item_id' => $latte->id, 'ingredient_id' => $milk->id, 'quantity_per_serving' => 0.25]);

        $coke = MenuItem::create([
            'category_id' => $bev->id, 'name' => 'Cola',
            'description' => 'Chilled cola drink',
            'price' => 2.50, 'sort_order' => 2,
        ]);
        BomItem::create(['menu_item_id' => $coke->id, 'ingredient_id' => $cola->id, 'quantity_per_serving' => 0.3]);

        $cake = MenuItem::create([
            'category_id' => $dessert->id, 'name' => 'Chocolate Cake',
            'description' => 'Rich chocolate slice',
            'price' => 6.99, 'sort_order' => 1,
        ]);
        BomItem::create(['menu_item_id' => $cake->id, 'ingredient_id' => $flour->id, 'quantity_per_serving' => 0.1]);
        BomItem::create(['menu_item_id' => $cake->id, 'ingredient_id' => $sugar->id, 'quantity_per_serving' => 0.08]);
        BomItem::create(['menu_item_id' => $cake->id, 'ingredient_id' => $eggs->id, 'quantity_per_serving' => 2]);

        // ── Tables & QR Codes ─────────────────────────────────────────────────
        $qrService = app(QrCodeService::class);

        for ($i = 1; $i <= 10; $i++) {
            $table = RestaurantTable::create([
                'number'   => (string) $i,
                'capacity' => $i <= 4 ? 2 : ($i <= 8 ? 4 : 6),
                'location' => $i <= 5 ? 'Main Hall' : 'Terrace',
            ]);
            $qrService->generateForTable($table);
        }
    }
}
