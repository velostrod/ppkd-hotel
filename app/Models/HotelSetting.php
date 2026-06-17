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
        'invoice_prefix',
        'booking_prefix',
    ];

    protected $casts = [
        'tax_rate' => 'decimal:2',
        'service_charge_rate' => 'decimal:2',
        'breakfast_threshold' => 'decimal:2',
    ];
}
