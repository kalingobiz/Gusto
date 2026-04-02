<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_item_id', 'user_id', 'action',
        'from_status', 'to_status', 'ip_address', 'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
