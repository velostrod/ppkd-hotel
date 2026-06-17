<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomInspection extends Model
{
    protected $fillable = [
        'room_id',
        'reservation_id',
        'inspected_by',
        'inspection_date',
        'room_condition',
        'damage_found',
        'damage_cost',
        'notes',
        'status',
    ];

    protected $casts = [
        'inspection_date' => 'datetime',
        'damage_found' => 'boolean',
        'damage_cost' => 'decimal:2',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspected_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RoomInspectionItem::class);
    }
}
