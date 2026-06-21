<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Enums\ReservationStatus;
use App\Models\Charge;
use App\Models\ChargeType;
use App\Models\HotelSetting;
use App\Models\Invoice;
use App\Models\Reservation;
use App\Models\RoomInspection;

class BillingService
{
    /**
     * Calculate billing for a new reservation (at booking time).
     *
     * @return array{subtotal: float, discount: float, serviceCharge: float, tax: float, total: float, roomCharge: float, extraBedCharge: float, breakfastCharge: float}
     */
    public function calculateReservationBilling(
        float $roomBasePrice,
        int $nights,
        float $extraBedPrice,
        bool $hasExtraBed,
        float $breakfastPrice,
        bool $hasBreakfast,
        bool $isBreakfastIncluded,
        int $adults,
        float $discount,
        HotelSetting $settings
    ): array {
        $roomCharge = $roomBasePrice * $nights;
        $subtotal = $roomCharge;

        $extraBedCharge = 0;
        if ($hasExtraBed) {
            $extraBedCharge = $extraBedPrice * $nights;
            $subtotal += $extraBedCharge;
        }

        $breakfastCharge = 0;
        if ($hasBreakfast && !$isBreakfastIncluded) {
            $breakfastCharge = $breakfastPrice * $adults * $nights;
            $subtotal += $breakfastCharge;
        }

        $totals = $this->applyTaxAndService($subtotal, $discount, $settings);

        return array_merge($totals, [
            'roomCharge' => $roomCharge,
            'extraBedCharge' => $extraBedCharge,
            'breakfastCharge' => $breakfastCharge,
        ]);
    }

    /**
     * Calculate billing for a reservation extension.
     *
     * @return array{subtotal: float, discount: float, serviceCharge: float, tax: float, total: float}
     */
    public function calculateExtensionBilling(
        Reservation $reservation,
        int $newTotalNights,
        HotelSetting $settings
    ): array {
        $roomBasePrice = $reservation->room->roomType->base_price;
        $subtotal = $roomBasePrice * $newTotalNights;

        // Recalculate extra bed addon
        $extraBedDetail = $reservation->details()->where('type', 'extra_bed')->first();
        if ($extraBedDetail) {
            $subtotal += $extraBedDetail->price * $newTotalNights;
        }

        // Recalculate breakfast addon
        $breakfastDetail = $reservation->details()->where('type', 'breakfast')->first();
        if ($breakfastDetail) {
            $subtotal += $breakfastDetail->price * ($reservation->adults * $newTotalNights);
        }

        return $this->applyTaxAndService($subtotal, $reservation->discount, $settings);
    }

    /**
     * Compute the full billing breakdown for checkout.
     *
     * This is the SINGLE SOURCE OF TRUTH for all checkout billing calculations.
     * Used by showCheckout, processCheckout, and printInvoice.
     *
     * Formula:
     *   discountedSubtotal = (items + charges + penalty - discount)
     *   serviceCharge = discountedSubtotal * serviceRate%
     *   tax = (discountedSubtotal + serviceCharge) * taxRate%
     *   grandTotal = discountedSubtotal + serviceCharge + tax
     */
    public function computeCheckoutBilling(Reservation $reservation, HotelSetting $settings, bool $forDisplay = false): array
    {
        $originalCheckoutDate = $reservation->checkout_date->copy();
        // Use startOfDay() to ensure we calculate calendar nights accurately
        $originalNights = $reservation->checkin_date->copy()->startOfDay()->diffInDays($originalCheckoutDate->startOfDay());
        $originalNights = $originalNights > 0 ? $originalNights : 1;

        $today = now()->startOfDay();
        $hasInspection = $reservation->relationLoaded('roomInspections')
            ? $reservation->roomInspections->isNotEmpty()
            : RoomInspection::where('reservation_id', $reservation->id)->exists();

        // Early checkout = checking out before the checkout deadline on the scheduled date
        $checkoutDeadline = $originalCheckoutDate->copy()
            ->setTimeFromTimeString($settings->checkout_time ?? '12:00');
        $isEarlyCheckout = $reservation->status === ReservationStatus::CheckedIn->value
            && now()->lt($checkoutDeadline)
            && $hasInspection;

        // Determine payment type
        $roomPaid = 0;
        $depositHeld = 0;
        $depositReturned = 0;
        if ($reservation->invoice) {
            $roomPaid = $reservation->invoice->payments()
                ->where('status', 'success')
                ->where('type', PaymentType::Room->value)
                ->sum('amount');
            $depositHeld = $reservation->invoice->payments()
                ->where('status', 'success')
                ->where('type', PaymentType::Deposit->value)
                ->sum('amount');
            $depositReturned = $reservation->invoice->payments()
                ->where('status', 'success')
                ->where('type', PaymentType::DepositReturn->value)
                ->sum('amount');
        }

        // Determine effective nights
        $effectiveNights = $originalNights;
        if ($isEarlyCheckout) {
            $effectiveNights = $reservation->checkin_date->copy()->startOfDay()->diffInDays(now()->startOfDay());
            $effectiveNights = $effectiveNights > 0 ? $effectiveNights : 1;
        }

        // --- Item-level breakdown ---
        $roomBasePrice = $reservation->room->roomType->base_price;
        $roomTotal = $roomBasePrice * $effectiveNights;

        $extraBedDetail = $reservation->details->firstWhere('type', 'extra_bed');
        $extraBedQty = $extraBedDetail ? $effectiveNights : 0;
        $extraBedPrice = $extraBedDetail ? $extraBedDetail->price : 0;
        $extraBedTotal = $extraBedPrice * $extraBedQty;

        $breakfastDetail = $reservation->details->firstWhere('type', 'breakfast');
        $breakfastQty = $breakfastDetail ? ($reservation->adults * $effectiveNights) : 0;
        $breakfastPrice = $breakfastDetail ? $breakfastDetail->price : 0;
        $breakfastTotal = $breakfastPrice * $breakfastQty;

        // Base subtotal = room + addons only (no charges, no penalty)
        $baseSubtotal = $roomTotal + $extraBedTotal + $breakfastTotal;

        // Additional charges (laundry, fnb, damage — already persisted)
        $additionalCharges = $reservation->charges->sum('amount');

        // --- Penalty calculation ---
        $penaltyAmount = 0;
        $penaltyDesc = '';
        $earlyCheckoutMsg = null;

        $taxRate = $settings->tax_rate ?? 10.00;
        $serviceRate = $settings->service_charge_rate ?? 5.00;

        if ($isEarlyCheckout) {
            // Apply 1-night penalty if checking out significantly early
            if ($effectiveNights + 1 < $originalNights) {
                $penaltyAmount = $roomBasePrice;
                $penaltyDesc = 'Penalti Early Checkout (1 Malam)';
                $earlyCheckoutMsg = 'Early Checkout terdeteksi. Dikenakan penalti 1 malam. Kelebihan bayar dapat direfund.';
            } else {
                $earlyCheckoutMsg = 'Early Checkout terdeteksi. Tidak dikenakan penalti.';
            }
        }

        // --- Compute totals ---
        $itemsSubtotal = $baseSubtotal + $additionalCharges + $penaltyAmount;
        $discount = $reservation->discount;
        $discountedSubtotal = max(0, $itemsSubtotal - $discount);

        $serviceCharge = round($discountedSubtotal * ($serviceRate / 100));
        $tax = round(($discountedSubtotal + $serviceCharge) * ($taxRate / 100));
        $grandTotal = $discountedSubtotal + $serviceCharge + $tax;

        // Deposit absorbs as much of the room balance as it can.
        // Only what deposit cannot cover is the actual shortfall tamu must pay.
        $depositAvailable = max(0, $depositHeld - $depositReturned);
        $roomBalance = $grandTotal - $roomPaid;
        $depositAbsorbed = min($depositAvailable, max(0, $roomBalance));
        $depositToReturn = max(0, $depositAvailable - $depositAbsorbed);
        $depositShortfall = max(0, $roomBalance - $depositAbsorbed);

        $paid = $roomPaid + $depositAbsorbed;
        $balance = $grandTotal - $paid; // equals depositShortfall when positive

        // --- Override model attributes in-memory for display ---
        if ($forDisplay && $isEarlyCheckout) {
            $reservation->checkout_date = now();
            $reservation->subtotal = $baseSubtotal;
            if ($extraBedDetail) $extraBedDetail->qty = $extraBedQty;
            if ($breakfastDetail) $breakfastDetail->qty = $breakfastQty;
        }

        return [
            'effectiveNights'   => $effectiveNights,
            'originalNights'    => $originalNights,
            'isEarlyCheckout'   => $isEarlyCheckout,
            'earlyCheckoutMsg'  => $earlyCheckoutMsg,
            'baseSubtotal'      => $baseSubtotal,
            'penaltyAmount'     => $penaltyAmount,
            'penaltyDesc'       => $penaltyDesc,
            'itemsSubtotal'     => $itemsSubtotal,
            'discount'          => $discount,
            'serviceCharge'     => $serviceCharge,
            'tax'               => $tax,
            'grandTotal'        => $grandTotal,
            'roomPaid'          => $roomPaid,
            'paid'              => $paid,
            'balance'           => $balance,
            'depositHeld'       => $depositHeld,
            'depositReturned'   => $depositReturned,
            'depositToReturn'   => $depositToReturn,
            'depositShortfall'  => $depositShortfall,
        ];
    }

    /**
     * Sync invoice totals from billing data.
     */
    public function syncInvoiceTotals(Invoice $invoice, array $billing): void
    {
        $payStatus = PaymentStatus::fromAmounts($billing['paid'], $billing['grandTotal']);

        $invoice->update([
            'subtotal'       => $billing['itemsSubtotal'],
            'tax'            => $billing['tax'],
            'service_charge' => $billing['serviceCharge'],
            'total_amount'   => $billing['grandTotal'],
            'paid_amount'    => $billing['paid'],
            'balance_due'    => $billing['balance'],
            'status'         => $payStatus->value,
        ]);
    }

    /**
     * Update invoice after a room payment is recorded.
     * Only sums type=room payments — deposit payments do not reduce room balance.
     */
    public function recalculateInvoiceAfterPayment(Invoice $invoice): void
    {
        $roomPaid = $invoice->payments()->where('status', 'success')->where('type', PaymentType::Room->value)->sum('amount');
        $depositHeld = $invoice->payments()->where('status', 'success')->where('type', PaymentType::Deposit->value)->sum('amount');
        $depositReturned = $invoice->payments()->where('status', 'success')->where('type', PaymentType::DepositReturn->value)->sum('amount');
        $depositAvailable = max(0, $depositHeld - $depositReturned);
        $roomBalance = max(0, (float) $invoice->total_amount - $roomPaid);
        $depositAbsorbed = min($depositAvailable, $roomBalance);
        $paid = $roomPaid + $depositAbsorbed;
        $balance = max(0, (float) $invoice->total_amount - $paid);
        $payStatus = PaymentStatus::fromAmounts($paid, $invoice->total_amount);

        $invoice->update([
            'paid_amount' => $paid,
            'balance_due' => $balance,
            'status'      => $payStatus->value,
        ]);
    }

    /**
     * Calculate occupancy rate for a date range.
     * Extracted from ReportController where it was duplicated.
     */
    public function calculateOccupancyRate(string $start, string $end, int $totalRooms): array
    {
        $totalDays = \Carbon\Carbon::parse($start)->diffInDays(\Carbon\Carbon::parse($end)) + 1;
        $totalRoomNights = $totalRooms * $totalDays;

        $occupiedRoomNights = 0;

        $activeReservations = Reservation::where('status', '!=', 'cancelled')
            ->where('checkin_date', '<=', $end)
            ->where('checkout_date', '>=', $start)
            ->get();

        foreach ($activeReservations as $res) {
            $resStart = \Carbon\Carbon::parse(max($res->checkin_date->format('Y-m-d'), $start));
            $resEnd = \Carbon\Carbon::parse(min($res->checkout_date->format('Y-m-d'), $end));
            $nights = $resStart->diffInDays($resEnd);
            $occupiedRoomNights += max(0, $nights);
        }

        $occupancyRate = $totalRoomNights > 0
            ? round(($occupiedRoomNights / $totalRoomNights) * 100, 2)
            : 0;

        return [
            'occupiedRoomNights' => $occupiedRoomNights,
            'totalRoomNights'    => $totalRoomNights,
            'occupancyRate'      => $occupancyRate,
        ];
    }

    /**
     * Apply tax and service charge to a subtotal.
     * This is the shared formula used by all billing paths.
     *
     * @return array{subtotal: float, discount: float, serviceCharge: float, tax: float, total: float}
     */
    private function applyTaxAndService(float $subtotal, float $discount, HotelSetting $settings): array
    {
        $taxRate = $settings->tax_rate ?? 10.00;
        $serviceRate = $settings->service_charge_rate ?? 5.00;

        $discountedSubtotal = max(0, $subtotal - $discount);
        $serviceCharge = $discountedSubtotal * ($serviceRate / 100);
        $tax = ($discountedSubtotal + $serviceCharge) * ($taxRate / 100);
        $total = $discountedSubtotal + $serviceCharge + $tax;

        return [
            'subtotal'      => $subtotal,
            'discount'      => $discount,
            'serviceCharge' => $serviceCharge,
            'tax'           => $tax,
            'total'         => $total,
        ];
    }
}
