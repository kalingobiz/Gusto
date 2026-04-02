<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RestaurantTable extends Model
{
    protected $fillable = [
        'number', 'capacity', 'status', 'token', 'qr_code_path', 'location',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $table) {
            if (empty($table->token)) {
                $table->token = (string) Str::uuid();
            }
        });
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'restaurant_table_id');
    }

    public function sessions()
    {
        return $this->hasMany(TableSession::class, 'restaurant_table_id');
    }

    public function activeSession()
    {
        return $this->hasOne(TableSession::class, 'restaurant_table_id')
            ->whereNull('closed_at');
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }
}
