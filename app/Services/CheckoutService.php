<?php

namespace App\Services;

use App\Enums\HousekeepingRequestType;
use App\Enums\PaymentType;
use App\Enums\ReservationStatus;
use App\Enums\RoomStatus;
use App\Helpers\ActivityLogger;
use App\Helpers\CurrencyHelper;
use App\Models\Charge;
use App\Models\ChargeType;
use App\Models\Checkin;
use App\Models\Checkout;
use App\Models\HousekeepingRequest;
use App\Models\HotelSetting;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\RoomInspection;

class CheckoutService
{
    public function __construct(
        private readonly BillingService $billingService,
    ) {}

    /**
     * Process check-in: collect remaining room balance and security deposit.
     *
     * $checkinPayment keys:
     *   room_payment_method_id  (required if room_balance > 0)
     *   deposit_payment_method_id (required)
     *   deposit_amount (required)
     *   notes (nullable)
     */
    public function processCheckin(Reservation $reservation, array $checkinPayment): Checkin
    {
        $invoice = $reservation->invoice;

        // Pay remaining room balance if any
        if ($invoice && $invoice->balance_due > 0) {
            Payment::create([
                'invoice_id'         => $invoice->id,
                'payment_method_id'  => $checkinPayment['room_payment_method_id'],
                'payment_date'       => now(),
                'amount'             => $invoice->balance_due,
                'reference_number'   => 'Pelunasan saat Check-in',
                'notes'              => 'Pelunasan sisa tagihan kamar',
                'status'             => 'success',
                'type'               => PaymentType::Room->value,
                'created_by'         => auth()->id(),
            ]);

            $this->billingService->recalculateInvoiceAfterPayment($invoice);
        }

        // Collect security deposit
        $depositAmount = (float) $checkinPayment['deposit_amount'];
        if ($depositAmount > 0) {
            Payment::create([
                'invoice_id'         => $invoice->id,
                'payment_method_id'  => $checkinPayment['deposit_payment_method_id'],
                'payment_date'       => now(),
                'amount'             => $depositAmount,
                'reference_number'   => 'Deposit Jaminan',
                'notes'              => 'Security deposit saat check-in',
                'status'             => 'success',
                'type'               => PaymentType::Deposit->value,
                'created_by'         => auth()->id(),
            ]);

            $invoice->update([
                'deposit_amount' => $depositAmount,
                'deposit_status' => 'held',
            ]);
        }

        $checkin = Checkin::create([
            'reservation_id' => $reservation->id,
            'checked_in_at'  => now(),
            'front_office_id' => auth()->id(),
            'notes'          => $checkinPayment['notes'] ?? null,
        ]);

        $reservation->update(['status' => ReservationStatus::CheckedIn->value]);
        $reservation->room->update(['status' => RoomStatus::Occupied->value]);

        ActivityLogger::log('checkin', 'reservations', "Tamu check-in: {$reservation->reservation_code}, Kamar: {$reservation->room->room_number}");

        return $checkin;
    }

    /**
     * Return security deposit to the guest (full or partial).
     */
    public function processDepositReturn(Invoice $invoice, float $returnAmount, array $data): Payment
    {
        $payment = Payment::create([
            'invoice_id'         => $invoice->id,
            'payment_method_id'  => $data['payment_method_id'],
            'payment_date'       => now(),
            'amount'             => $returnAmount,
            'reference_number'   => $data['reference_number'] ?? 'Pengembalian Deposit',
            'notes'              => $data['notes'] ?? 'Pengembalian deposit jaminan',
            'status'             => 'success',
            'type'               => PaymentType::DepositReturn->value,
            'created_by'         => auth()->id(),
        ]);

        $totalReturned = (float) $invoice->deposit_returned + $returnAmount;
        $depositStatus = $totalReturned >= (float) $invoice->deposit_amount ? 'returned' : 'partially_returned';

        $invoice->update([
            'deposit_returned' => $totalReturned,
            'deposit_status'   => $depositStatus,
        ]);

        $reservation = $invoice->reservation;
        ActivityLogger::log('create', 'payments', "Pengembalian deposit Rp " . CurrencyHelper::formatIDR($returnAmount) . " untuk Booking {$reservation->reservation_code}");

        return $payment;
    }

    /**
     * Create a room inspection request for checkout.
     */
    public function requestInspection(Reservation $reservation): RoomInspection
    {
        return RoomInspection::create([
            'room_id'          => $reservation->room_id,
            'reservation_id'   => $reservation->id,
            'inspected_by'     => auth()->id(),
            'inspection_date'  => now(),
            'room_condition'   => 'good',
            'damage_found'     => false,
            'damage_cost'      => 0,
            'notes'            => 'Menunggu inspeksi dari Housekeeping',
            'status'           => 'pending',
        ]);
    }

    /**
     * Record a payment against an invoice.
     */
    public function processPayment(Invoice $invoice, array $data): Payment
    {
        $payment = Payment::create([
            'invoice_id'         => $invoice->id,
            'payment_method_id'  => $data['payment_method_id'],
            'payment_date'       => now(),
            'amount'             => $data['amount'],
            'reference_number'   => $data['reference_number'] ?? null,
            'notes'              => $data['notes'] ?? null,
            'status'             => 'success',
            'type'               => PaymentType::Room->value,
            'created_by'         => auth()->id(),
        ]);

        $this->billingService->recalculateInvoiceAfterPayment($invoice);

        $reservation = $invoice->reservation;
        ActivityLogger::log('create', 'payments', "Menerima pembayaran Rp " . CurrencyHelper::formatIDR($data['amount']) . " untuk Booking {$reservation->reservation_code}");

        return $payment;
    }

    /**
     * Process the final checkout.
     */
    public function processCheckout(Reservation $reservation, ?string $notes): Checkout
    {
        $reservation->load(['room.roomType', 'details', 'charges', 'invoice.payments']);
        $settings = HotelSetting::first();
        $billing = $this->billingService->computeCheckoutBilling($reservation, $settings, false);

        if ($billing['isEarlyCheckout']) {
            $this->persistEarlyCheckoutChanges($reservation, $billing);
        }

        $invoice = $reservation->invoice;
        if ($invoice) {
            $this->billingService->syncInvoiceTotals($invoice, $billing);
        }

        $checkout = Checkout::create([
            'reservation_id'   => $reservation->id,
            'checked_out_at'   => now(),
            'front_office_id'  => auth()->id(),
            'final_bill_total' => $billing['grandTotal'],
            'notes'            => $notes,
        ]);

        $reservation->update(['status' => ReservationStatus::CheckedOut->value]);
        $reservation->room->update(['status' => RoomStatus::Dirty->value]);

        HousekeepingRequest::create([
            'reservation_id' => $reservation->id,
            'room_id'        => $reservation->room_id,
            'requested_by'   => auth()->id(),
            'request_type'   => HousekeepingRequestType::CheckoutCleaning->value,
            'priority'       => 'high',
            'status'         => 'pending',
            'request_time'   => now(),
            'notes'          => 'Pembersihan otomatis setelah checkout tamu.',
        ]);

        ActivityLogger::log('checkout', 'reservations', "Tamu checkout: {$reservation->reservation_code}, Kamar berubah status menjadi dirty");

        return $checkout;
    }

    /**
     * Persist early checkout adjustments to the database.
     */
    private function persistEarlyCheckoutChanges(Reservation $reservation, array $billing): void
    {
        $reservation->update([
            'checkout_date'  => now()->format('Y-m-d'),
            'subtotal'       => $billing['baseSubtotal'],
            'total'          => $billing['grandTotal'],
            'tax'            => $billing['tax'],
            'service_charge' => $billing['serviceCharge'],
        ]);

        $extraBedDetail = $reservation->details()->where('type', 'extra_bed')->first();
        if ($extraBedDetail) {
            $extraBedDetail->update(['qty' => $billing['effectiveNights']]);
        }

        $breakfastDetail = $reservation->details()->where('type', 'breakfast')->first();
        if ($breakfastDetail) {
            $breakfastDetail->update(['qty' => $reservation->adults * $billing['effectiveNights']]);
        }

        if ($billing['penaltyAmount'] > 0) {
            $chargeType = ChargeType::firstOrCreate(
                ['name' => 'Penalti Early Checkout'],
                ['code' => 'PNTLY', 'base_amount' => 0, 'is_active' => true]
            );

            Charge::create([
                'reservation_id' => $reservation->id,
                'charge_type_id' => $chargeType->id,
                'amount'         => $billing['penaltyAmount'],
                'description'    => $billing['penaltyDesc'],
                'created_by'     => auth()->id(),
            ]);
        }
    }
}
