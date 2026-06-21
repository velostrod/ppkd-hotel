<?php

namespace App\Enums;

enum ReservationStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case CheckedIn = 'checked_in';
    case CheckedOut = 'checked_out';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Confirmed => 'Confirmed',
            self::CheckedIn => 'Checked In',
            self::CheckedOut => 'Checked Out',
            self::Cancelled => 'Cancelled',
        };
    }

    /**
     * Statuses that block room availability (overlap check).
     */
    public static function blockingStatuses(): array
    {
        return [
            self::Pending,
            self::Confirmed,
            self::CheckedIn,
        ];
    }

    /**
     * Statuses that cannot be cancelled.
     */
    public static function nonCancellableStatuses(): array
    {
        return [
            self::CheckedIn,
            self::CheckedOut,
            self::Cancelled,
        ];
    }
}
