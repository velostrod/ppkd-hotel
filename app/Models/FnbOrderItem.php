<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FnbOrderItem extends Model
{
    protected $fillable = [
        'fnb_order_id',
        'food_item_id',
        'qty',
        'price',
        'subtotal',
        'notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function fnbOrder(): BelongsTo
    {
        return $this->belongsTo(FnbOrder::class);
    }

    public function foodItem(): BelongsTo
    {
        return $this->belongsTo(FoodItem::class);
    }
}
