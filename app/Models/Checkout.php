<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Checkout extends Model
{
    protected $fillable = [
        'reservation_id',
        'checked_out_at',
        'front_office_id',
        'final_bill_total',
        'notes',
    ];

    protected $casts = [
        'checked_out_at' => 'datetime',
        'final_bill_total' => 'decimal:2',
    ];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);

    public function frontOffice(): BelongsTo
    {
        return $this->belongsTo(User::class, 'front_office_id');
    }
}
