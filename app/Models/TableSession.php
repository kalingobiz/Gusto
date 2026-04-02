<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TableSession extends Model
{
    protected $fillable = ['restaurant_table_id', 'token', 'opened_at', 'closed_at'];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $session) {
            if (empty($session->token)) {
                $session->token = (string) Str::uuid();
            }
            if (empty($session->opened_at)) {
                $session->opened_at = now();
            }
        });
    }

    public function restaurantTable()
    {
        return $this->belongsTo(RestaurantTable::class, 'restaurant_table_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'table_session_id');
    }

    public function isActive(): bool
    {
        return $this->closed_at === null;
    }

    public function close(): void
    {
        $this->update(['closed_at' => now()]);
    }
}
