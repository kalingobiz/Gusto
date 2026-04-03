<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StocktakeItem extends Model
{
    protected $fillable = [
        'stocktake_id', 'ingredient_id', 'expected_quantity',
        'actual_quantity', 'difference', 'unit_cost'
    ];

    protected $casts = [
        'expected_quantity' => 'decimal:4',
        'actual_quantity' => 'decimal:4',
        'difference' => 'decimal:4',
        'unit_cost' => 'decimal:4',
    ];

    public function stocktake()
    {
        return $this->belongsTo(Stocktake::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
}
