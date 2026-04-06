<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Bootstrap Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

Schema::disableForeignKeyConstraints();

Schema::dropIfExists('order_items_new');
Schema::create('order_items_new', function (Blueprint $table) {
    $table->id();
    // Assuming foreign references - recreating exactly as the original
    $table->foreignId('order_id')->constrained()->cascadeOnDelete();
    $table->foreignId('menu_item_id')->constrained()->cascadeOnDelete();
    $table->foreignId('variant_id')->nullable()->constrained('menu_item_variants')->nullOnDelete();
    $table->integer('quantity');
    $table->decimal('unit_price', 10, 2);
    $table->decimal('line_total', 10, 2);
    $table->enum('kitchen_status', ['pending', 'cooking', 'ready', 'done', 'voided'])->default('pending');
    $table->text('notes')->nullable();
    $table->timestamps();
});

DB::statement("
    INSERT INTO order_items_new (id, order_id, menu_item_id, variant_id, quantity, unit_price, line_total, kitchen_status, notes, created_at, updated_at)
    SELECT id, order_id, menu_item_id, variant_id, quantity, unit_price, line_total, 
           CASE WHEN kitchen_status = 'in_progress' THEN 'cooking' ELSE kitchen_status END, 
           notes, created_at, updated_at 
    FROM order_items
");
Schema::drop('order_items');
Schema::rename('order_items_new', 'order_items');

Schema::enableForeignKeyConstraints();

echo "Successfully rebuilt order_items with new CHECK constraint!\n";
