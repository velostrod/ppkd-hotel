<?php

namespace App\Enums;

enum RoomStatus: string
{
    case Available = 'available';
    case Reserved = 'reserved';
    case Occupied = 'occupied';
    case Dirty = 'dirty';
    case Cleaning = 'cleaning';
    case Inspected = 'inspected';
    case Maintenance = 'maintenance';
    case OutOfOrder = 'out_of_order';

    /**
     * Get human-readable label for display.
     */
    public function label(): string
    {
        return match ($this) {
            self::Available => 'Available',
            self::Reserved => 'Reserved',
            self::Occupied => 'Occupied',
            self::Dirty => 'Dirty',
            self::Cleaning => 'Cleaning',
            self::Inspected => 'Inspected',
            self::Maintenance => 'Maintenance',
            self::OutOfOrder => 'Out of Order',
        };
    }

    /**
     * Statuses that indicate the room cannot be sold.
     */
    public static function unavailableStatuses(): array
    {
        return [
            self::Reserved,
            self::Occupied,
            self::Dirty,
            self::Cleaning,
            self::Maintenance,
            self::OutOfOrder,
        ];
    }

    /**
     * Statuses that indicate active housekeeping work should be auto-completed.
     */
    public static function completionStatuses(): array
    {
        return [
            self::Available,
            self::Inspected,
            self::Occupied,
            self::Reserved,
        ];
    }
}
