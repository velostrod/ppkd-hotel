<?php

namespace App\Enums;

enum LaundryStatus: string
{
    case Requested = 'requested';
    case PickedUp = 'picked_up';
    case Processing = 'processing';
    case Ready = 'ready';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Requested => 'Requested',
            self::PickedUp => 'Picked Up',
            self::Processing => 'Processing',
            self::Ready => 'Ready',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
        };
    }

    /**
     * Statuses that indicate active/pending laundry work.
     */
    public static function activeStatuses(): array
    {
        return [
            self::Requested,
            self::PickedUp,
            self::Processing,
            self::Ready,
        ];
    }
}
