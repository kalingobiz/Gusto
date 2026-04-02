<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'menu_item_id', 'variant_id',
        'quantity', 'unit_price', 'line_total',
        'kitchen_status', 'notes',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function variant()
    {
        return $this->belongsTo(MenuItemVariant::class, 'variant_id');
    }

    public function logs()
    {
        return $this->hasMany(OrderItemLog::class);
    }

    public function stockDeductions()
    {
        return $this->hasMany(StockDeduction::class);
    }

    public function voidLog()
    {
        return $this->hasOne(VoidLog::class);
    }

    public function isVoided(): bool
    {
        return $this->kitchen_status === 'voided';
    }

    protected static function booted(): void
    {
        static::creating(function (self $item) {
            $item->line_total = $item->unit_price * $item->quantity;
        });
    }
}
