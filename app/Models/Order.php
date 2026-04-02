<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'restaurant_table_id', 'table_session_id', 'user_id',
        'status', 'source', 'subtotal', 'tax', 'total', 'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function restaurantTable()
    {
        return $this->belongsTo(RestaurantTable::class, 'restaurant_table_id');
    }

    public function tableSession()
    {
        return $this->belongsTo(TableSession::class, 'table_session_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isActive(): bool
    {
        return !in_array($this->status, ['paid', 'voided']);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['paid', 'voided']);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function recalculateTotals(): void
    {
        $subtotal = $this->items()
            ->where('kitchen_status', '!=', 'voided')
            ->sum('line_total');

        $tax = round($subtotal * 0, 2);
        $this->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $subtotal + $tax,
        ]);
    }
}
