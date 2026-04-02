<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockIntake extends Model
{
    protected $fillable = [
        'ingredient_id', 'user_id', 'quantity', 'cost_per_unit',
        'supplier', 'reference', 'intake_date', 'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'cost_per_unit' => 'decimal:4',
        'intake_date' => 'date',
    ];

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
