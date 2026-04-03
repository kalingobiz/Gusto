<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    protected $fillable = [
        'name', 'unit', 'current_stock', 'reorder_level', 'cost_per_unit',
    ];

    protected $casts = [
        'current_stock' => 'decimal:4',
        'reorder_level' => 'decimal:4',
        'cost_per_unit' => 'decimal:4',
    ];

    public function bomItems()
    {
        return $this->hasMany(BomItem::class);
    }

    public function stockIntakes()
    {
        return $this->hasMany(StockIntake::class);
    }

    public function stockDeductions()
    {
        return $this->hasMany(StockDeduction::class);
    }
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function stockAdjustments()
    {
        return $this->hasMany(StockAdjustment::class);
    }

    public function stocktakeItems()
    {
        return $this->hasMany(StocktakeItem::class);
    }

    public function isLowStock(): bool
    {
        return $this->current_stock <= $this->reorder_level;
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('current_stock', '<=', 'reorder_level');
    }
}
