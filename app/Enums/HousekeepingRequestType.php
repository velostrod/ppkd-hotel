<?php

namespace App\Enums;

enum HousekeepingRequestType: string
{
    case StayoverCleaning = 'stayover_cleaning';
    case CheckoutCleaning = 'checkout_cleaning';
    case DeepCleaning = 'deep_cleaning';
    case Maintenance = 'maintenance';
    case LinenReplacement = 'linen_replacement';

    public function label(): string
    {
        return match ($this) {
            self::StayoverCleaning => 'Stayover Cleaning',
            self::CheckoutCleaning => 'Checkout Cleaning',
            self::DeepCleaning => 'Deep Cleaning',
            self::Maintenance => 'Maintenance',
            self::LinenReplacement => 'Linen Replacement',
        };
    }

    /**
     * After completion, what should the room status become?
     */
    public function completedRoomStatus(): RoomStatus
    {
        return match ($this) {
            self::CheckoutCleaning => RoomStatus::Inspected,
            self::StayoverCleaning, self::LinenReplacement => RoomStatus::Occupied,
            self::DeepCleaning, self::Maintenance => RoomStatus::Available,
        };
    }
}
