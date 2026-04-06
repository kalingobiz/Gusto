<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // For MySQL: migrate 'in_progress' data safely using string conversion
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'mysql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE order_items MODIFY COLUMN kitchen_status VARCHAR(50) NOT NULL DEFAULT 'pending'");
            \Illuminate\Support\Facades\DB::table('order_items')->where('kitchen_status', 'in_progress')->update(['kitchen_status' => 'cooking']);
        }

        Schema::table('order_items', function (Blueprint $table) {
            $table->enum('kitchen_status', ['pending', 'cooking', 'ready', 'done', 'voided'])
                  ->default('pending')
                  ->change();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->enum('kitchen_status', ['pending', 'in_progress', 'done', 'voided'])
                  ->default('pending')
                  ->change();
        });
    }
};
