<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaundryRequest extends Model
{
    protected $fillable = [
        'reservation_id',
        'guest_id',
        'requested_by',
        'handled_by',
        'request_date',
        'status',
        'notes',
        'total_charge',
    ];

    protected $casts = [
        'request_date' => 'datetime',
        'total_charge' => 'decimal:2',
    ];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
