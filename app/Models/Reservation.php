<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Reservation extends Model
{
    protected $fillable = [
        'reservation_code',
        'guest_id',
        'room_id',
        'checkin_date',
        'checkout_date',
        'adults',
        'children',
        'status',
        'subtotal',
        'discount',
        'tax',
        'service_charge',
        'total',
        'created_by',
    ];

    protected $casts = [
        'checkin_date' => 'date',
        'checkout_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(ReservationDetail::class);
    }

    public function checkin(): HasOne
    {
        return $this->hasOne(Checkin::class);
    }

    public function checkout(): HasOne
    {
        return $this->hasOne(Checkout::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function charges(): HasMany
    {
        return $this->hasMany(Charge::class);
    }

    public function housekeepingRequests(): HasMany
    {
        return $this->hasMany(HousekeepingRequest::class);
    }

    public function fnbOrders(): HasMany
    {
        return $this->hasMany(FnbOrder::class);
    }

    public function laundryRequests(): HasMany
    {
        return $this->hasMany(LaundryRequest::class);
    }

    public function roomInspections(): HasMany
    {
        return $this->hasMany(RoomInspection::class);
    }
}
