<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomInspectionItem extends Model
{
    protected $fillable = [
        'room_inspection_id',
        'item_name',
        'condition',
        'charge_amount',
        'notes',
    ];

    protected $casts = [
        'charge_amount' => 'decimal:2',
    ];

    public function roomInspection(): BelongsTo
    {
        return $this->belongsTo(RoomInspection::class);
    }
}
