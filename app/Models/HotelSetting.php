<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelSetting extends Model
{
    protected $fillable = [
        'name',
        'address',
        'phone',
        'tax_rate',
        'service_charge_rate',
        'breakfast_threshold',
        'security_deposit_amount',
        'invoice_prefix',
        'booking_prefix',
        'checkin_time',
        'checkout_time',
    ];

    protected $casts = [
        'tax_rate' => 'decimal:2',
        'service_charge_rate' => 'decimal:2',
        'breakfast_threshold' => 'decimal:2',
        'security_deposit_amount' => 'decimal:2',
    ];
}
