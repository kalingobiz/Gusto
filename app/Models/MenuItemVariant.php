<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItemVariant extends Model
{
    protected $fillable = ['menu_item_id', 'name', 'price_modifier', 'is_available'];

    protected $casts = [
        'price_modifier' => 'decimal:2',
        'is_available' => 'boolean',
    ];

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }
}
