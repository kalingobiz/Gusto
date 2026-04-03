<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stocktake extends Model
{
    protected $fillable = [
        'reference_number', 'user_id', 'status', 'completed_at', 'notes'
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(StocktakeItem::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->reference_number)) {
                $model->reference_number = 'ST-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
            }
        });
    }
}
