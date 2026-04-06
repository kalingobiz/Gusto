<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite does not support ALTER COLUMN for enums — update the check constraint or just allow the string values
        // For MySQL: alter the enum; for SQLite: column is already TEXT so values are unrestricted
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            // Step 1: Change to VARCHAR temporarily so we can update to values not yet in ENUM
            DB::statement("ALTER TABLE order_items MODIFY COLUMN kitchen_status VARCHAR(50) NOT NULL DEFAULT 'pending'");

            // Step 2: Map old 'in_progress' to 'cooking'
            DB::table('order_items')->where('kitchen_status', 'in_progress')->update(['kitchen_status' => 'cooking']);
            
            // Step 3: Set final production ENUM
            DB::statement("ALTER TABLE order_items MODIFY COLUMN kitchen_status ENUM('pending','cooking','ready','done','voided') NOT NULL DEFAULT 'pending'");
        }
        // SQLite stores as text — no migration needed, validation is handled at application level
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE order_items MODIFY COLUMN kitchen_status ENUM('pending','in_progress','done','voided') NOT NULL DEFAULT 'pending'");
        }
    }
};
