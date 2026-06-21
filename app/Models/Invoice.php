<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'reservation_id',
        'invoice_date',
        'subtotal',
        'tax',
        'service_charge',
        'discount',
        'total_amount',
        'paid_amount',
        'balance_due',
        'status',
        'deposit_amount',
        'deposit_returned',
        'deposit_status',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'deposit_returned' => 'decimal:2',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Get the total of all successful room payments (excludes deposit and deposit_return).
     */
    public function getSuccessfulPaymentsTotalAttribute(): float
    {
        return (float) $this->payments()->where('status', 'success')->where('type', PaymentType::Room->value)->sum('amount');
    }

    // ==========================================
    // METHODS
    // ==========================================

    /**
     * Recalculate balance and update status based on current payments.
     */
    public function recalculateBalance(): void
    {
        $paid = $this->successfulPaymentsTotal;
        $balance = max(0, $this->total_amount - $paid);
        $status = PaymentStatus::fromAmounts($paid, $this->total_amount);

        $this->update([
            'paid_amount' => $paid,
            'balance_due' => $balance,
            'status'      => $status->value,
        ]);
    }
}
