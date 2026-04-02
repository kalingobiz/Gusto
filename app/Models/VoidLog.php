<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoidLog extends Model
{
    protected $fillable = [
        'order_item_id', 'user_id', 'approved_by', 'reason', 'notes',
    ];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
