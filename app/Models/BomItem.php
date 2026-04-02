<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BomItem extends Model
{
    protected $fillable = ['menu_item_id', 'ingredient_id', 'quantity_per_serving'];

    protected $casts = ['quantity_per_serving' => 'decimal:4'];

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
}
