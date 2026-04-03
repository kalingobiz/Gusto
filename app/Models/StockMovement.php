<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'ingredient_id', 'user_id', 'source_type', 'source_id',
        'quantity_change', 'balance_after', 'notes'
    ];

    protected $casts = [
        'quantity_change' => 'decimal:4',
        'balance_after' => 'decimal:4',
    ];

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function source()
    {
        return $this->morphTo();
    }
}
