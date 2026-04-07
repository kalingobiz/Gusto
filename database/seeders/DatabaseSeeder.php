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
        // ── Initial Owner Admin ────────────────────────────────────────────────
        User::create([
            'name'     => 'Owner Admin',
            'email'    => 'admin@gusto.com',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);
    }
}
