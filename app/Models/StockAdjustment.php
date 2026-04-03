<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    protected $fillable = [
        'ingredient_id', 'user_id', 'type', 'quantity', 'notes'
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function stockMovements()
    {
        return $this->morphMany(StockMovement::class, 'source');
    }
}
