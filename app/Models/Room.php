<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use App\Enums\RoomStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $fillable = [
        'room_number',
        'room_type_id',
        'floor',
        'status',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function housekeepingRequests(): HasMany
    {
        return $this->hasMany(HousekeepingRequest::class);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Filter to only available rooms.
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', RoomStatus::Available->value);
    }

    /**
     * Filter to only active rooms.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    // ==========================================
    // METHODS
    // ==========================================

    /**
     * Check if the room is available for the given date range.
     */
    public function isAvailableForDates(string $checkin, string $checkout, ?int $excludeReservationId = null): bool
    {
        $blocking = array_map(fn($s) => $s->value, ReservationStatus::blockingStatuses());

        $query = $this->reservations()
            ->whereIn('status', $blocking)
            ->where('checkin_date', '<', $checkout)
            ->where('checkout_date', '>', $checkin);

        if ($excludeReservationId) {
            $query->where('id', '!=', $excludeReservationId);
        }

        return !$query->exists();
    }
}
