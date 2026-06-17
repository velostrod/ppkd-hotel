<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HousekeepingRequest extends Model
{
    protected $fillable = [
        'reservation_id',
        'room_id',
        'requested_by',
        'assigned_to',
        'request_type',
        'priority',
        'status',
        'request_time',
        'completed_time',
        'notes',
    ];

    protected $casts = [
        'request_time' => 'datetime',
        'completed_time' => 'datetime',
    ];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function items(): HasMany
    {
        return $this->hasMany(HousekeepingRequestItem::class);
    }
}
