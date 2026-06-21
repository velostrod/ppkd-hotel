<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Unpaid = 'unpaid';
    case Partial = 'partial';
    case Paid = 'paid';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Unpaid => 'Unpaid',
            self::Partial => 'Partial',
            self::Paid => 'Paid',
            self::Refunded => 'Refunded',
        };
    }

    /**
     * Determine payment status from paid and total amounts.
     */
    public static function fromAmounts(float $paid, float $total): self
    {
        if ($paid <= 0) {
            return self::Unpaid;
        }

        return $paid >= $total ? self::Paid : self::Partial;
    }
}
