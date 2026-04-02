<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Cashier;
use App\Http\Controllers\Customer;
use App\Http\Controllers\Kitchen;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// ─── Public: Customer QR Ordering ──────────────────────────────────────────
Route::prefix('order')->name('customer.')->middleware(['throttle:60,1'])->group(function () {
    Route::get('/{tableToken}', [Customer\OrderController::class, 'menu'])->name('menu');
    Route::post('/{tableToken}', [Customer\OrderController::class, 'place'])->name('place');
});

// ─── Authenticated ───────────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        $role = auth()->user()->role;
        if ($role === 'kitchen') {
            return redirect()->route('kitchen.index');
        }
        if ($role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('tables.index');
    })->name('dashboard');

    // ── Profile (Breeze) ──────────────────────────────────────────────────────
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Floor (Cashier & Admin) ───────────────────────────────────────────────
    Route::middleware(['role.check:admin,cashier'])->group(function () {
        Route::get('/', [Cashier\OrderController::class, 'index'])->name('tables.index');
        Route::get('/orders/new', [Cashier\OrderController::class, 'create'])->name('orders.create');
        Route::post('/orders', [Cashier\OrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}', [Cashier\OrderController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{order}/served', [Cashier\OrderController::class, 'markServed'])->name('orders.served');
        Route::post('/orders/{order}/void/{item}', [Cashier\OrderController::class, 'voidItem'])->name('orders.void-item');
        Route::post('/payments', [Cashier\PaymentController::class, 'store'])->name('payments.store');
    });

    // ── Kitchen Display ───────────────────────────────────────────────────────
    Route::middleware(['role.check:admin,cashier,kitchen'])->group(function () {
        Route::get('/kitchen', [Kitchen\DisplayController::class, 'index'])->name('kitchen.index');
        Route::patch('/kitchen/items/{item}', [Kitchen\DisplayController::class, 'updateStatus'])->name('kitchen.item.update');
        Route::get('/api/kitchen/orders', [Kitchen\DisplayController::class, 'pending'])->name('kitchen.pending');
    });

    // ── Admin Only ────────────────────────────────────────────────────────────
    Route::middleware(['role.check:admin'])->prefix('admin')->name('admin.')->group(function () {

        Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

        // Menu & Categories
        Route::get('/menu', [Admin\MenuController::class, 'index'])->name('menu.index');
        Route::get('/menu/create', [Admin\MenuController::class, 'create'])->name('menu.create');
        Route::post('/menu', [Admin\MenuController::class, 'store'])->name('menu.store');
        Route::get('/menu/{menu}/edit', [Admin\MenuController::class, 'edit'])->name('menu.edit');
        Route::put('/menu/{menu}', [Admin\MenuController::class, 'update'])->name('menu.update');
        Route::delete('/menu/{menu}', [Admin\MenuController::class, 'destroy'])->name('menu.destroy');

        Route::get('/categories', [Admin\MenuController::class, 'categories'])->name('categories.index');
        Route::post('/categories', [Admin\MenuController::class, 'storeCategory'])->name('categories.store');
        Route::put('/categories/{category}', [Admin\MenuController::class, 'updateCategory'])->name('categories.update');
        Route::delete('/categories/{category}', [Admin\MenuController::class, 'destroyCategory'])->name('categories.destroy');

        // Ingredients & Stock
        Route::get('/ingredients', [Admin\IngredientController::class, 'index'])->name('ingredients.index');
        Route::post('/ingredients', [Admin\IngredientController::class, 'store'])->name('ingredients.store');
        Route::put('/ingredients/{ingredient}', [Admin\IngredientController::class, 'update'])->name('ingredients.update');
        Route::delete('/ingredients/{ingredient}', [Admin\IngredientController::class, 'destroy'])->name('ingredients.destroy');
        Route::post('/ingredients/intake', [Admin\IngredientController::class, 'intake'])->name('ingredients.intake');
        Route::get('/ingredients/{ingredient}/history', [Admin\IngredientController::class, 'intakeHistory'])->name('ingredients.history');

        // BOM
        Route::get('/bom', [Admin\BomController::class, 'index'])->name('bom.index');
        Route::post('/bom', [Admin\BomController::class, 'store'])->name('bom.store');
        Route::delete('/bom/{bom}', [Admin\BomController::class, 'destroy'])->name('bom.destroy');
        Route::put('/bom/{menuItem}/bulk', [Admin\BomController::class, 'bulkUpdate'])->name('bom.bulk');

        // Tables
        Route::get('/tables', [Admin\TableController::class, 'index'])->name('tables.admin');
        Route::post('/tables', [Admin\TableController::class, 'store'])->name('tables.store');
        Route::put('/tables/{table}', [Admin\TableController::class, 'update'])->name('tables.update');
        Route::delete('/tables/{table}', [Admin\TableController::class, 'destroy'])->name('tables.destroy');
        Route::post('/tables/{table}/qr', [Admin\TableController::class, 'regenerateQr'])->name('tables.qr');

        // Staff
        Route::get('/users', [Admin\UserController::class, 'index'])->name('users.index');
        Route::post('/users', [Admin\UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [Admin\UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [Admin\UserController::class, 'destroy'])->name('users.destroy');

        // Reports
        Route::get('/reports/sales', [Admin\ReportController::class, 'sales'])->name('reports.sales');
        Route::get('/reports/bom-variance', [Admin\ReportController::class, 'bomVariance'])->name('reports.bom-variance');
        Route::get('/reports/voids', [Admin\ReportController::class, 'voids'])->name('reports.voids');
        Route::get('/reports/audit', [Admin\ReportController::class, 'audit'])->name('reports.audit');
    });
});

require __DIR__ . '/auth.php';
