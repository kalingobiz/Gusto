<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockDeduction extends Model
{
    protected $fillable = [
        'order_item_id', 'ingredient_id', 'expected_qty', 'reversed', 'reversed_at',
    ];

    protected $casts = [
        'expected_qty' => 'decimal:4',
        'reversed' => 'boolean',
        'reversed_at' => 'datetime',
    ];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
    public function stockMovements()
    {
        return $this->morphMany(StockMovement::class, 'source');
    }
}
