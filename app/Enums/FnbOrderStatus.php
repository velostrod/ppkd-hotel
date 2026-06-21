<?php

namespace App\Enums;

enum FnbOrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Preparing = 'preparing';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Confirmed => 'Confirmed',
            self::Preparing => 'Preparing',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
        };
    }

    /**
     * Statuses that indicate active/in-progress orders.
     */
    public static function activeStatuses(): array
    {
        return [
            self::Pending,
            self::Confirmed,
            self::Preparing,
        ];
    }

    /**
     * Statuses that indicate completed/archived orders.
     */
    public static function completedStatuses(): array
    {
        return [
            self::Delivered,
            self::Cancelled,
        ];
    }
}
