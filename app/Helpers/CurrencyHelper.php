<?php

namespace App\Helpers;

class CurrencyHelper
{
    /**
     * Format a number as Indonesian Rupiah (IDR) without currency symbol.
     * Example: 1500000 → "1.500.000"
     */
    public static function formatIDR(float|int $amount): string
    {
        return number_format($amount, 0, ',', '.');
    }

    /**
     * Format a number as Indonesian Rupiah with "Rp" prefix.
     * Example: 1500000 → "Rp 1.500.000"
     */
    public static function formatIDRWithPrefix(float|int $amount): string
    {
        return 'Rp ' . self::formatIDR($amount);
    }
}
