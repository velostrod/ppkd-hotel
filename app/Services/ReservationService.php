<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Enums\ReservationStatus;
use App\Enums\RoomStatus;
use App\Helpers\ActivityLogger;
use App\Models\HotelSetting;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\ReservationDetail;
use App\Models\Room;
use Illuminate\Support\Facades\DB;

class ReservationService
{
    public function __construct(
        private readonly BillingService $billingService,
    ) {}

    /**
     * Generate a unique booking code: BK-ROOMNO-YYYYMMDD-XXXX
     */
    public function generateBookingCode(Room $room, HotelSetting $settings): string
    {
        $prefix = $settings->booking_prefix ?? 'BK';
        $datePart = now()->format('Ymd');
        $roomNo = $room->room_number;
        $searchPattern = "{$prefix}-{$roomNo}-{$datePart}-%";

        $lastRes = Reservation::where('reservation_code', 'like', $searchPattern)
            ->orderBy('reservation_code', 'desc')
            ->lockForUpdate()
            ->first();

        $seq = 1;
        if ($lastRes) {
            $lastSeq = (int) substr($lastRes->reservation_code, -4);
            $seq = $lastSeq + 1;
        }

        $seqPart = str_pad($seq, 4, '0', STR_PAD_LEFT);
        return "{$prefix}-{$roomNo}-{$datePart}-{$seqPart}";
    }

    /**
     * Generate a unique invoice number: INV-YYYYMMDD-XXXX
     */
    public function generateInvoiceNumber(HotelSetting $settings): string
    {
        $prefix = $settings->invoice_prefix ?? 'INV';
        $datePart = now()->format('Ymd');

        $lastInv = Invoice::where('invoice_number', 'like', "{$prefix}-{$datePart}-%")
            ->orderBy('invoice_number', 'desc')
            ->lockForUpdate()
            ->first();

        $seq = 1;
        if ($lastInv) {
            $lastSeq = (int) substr($lastInv->invoice_number, -4);
            $seq = $lastSeq + 1;
        }

        $seqPart = str_pad($seq, 4, '0', STR_PAD_LEFT);
        return "{$prefix}-{$datePart}-{$seqPart}";
    }

    /**
     * Check if a room is available for the given date range.
     */
    public function isRoomAvailable(int $roomId, string $checkin, string $checkout, ?int $excludeReservationId = null): bool
    {
        $blocking = array_map(fn($s) => $s->value, ReservationStatus::blockingStatuses());

        $query = Reservation::where('room_id', $roomId)
            ->whereIn('status', $blocking)
            ->where('checkin_date', '<', $checkout)
            ->where('checkout_date', '>', $checkin);

        if ($excludeReservationId) {
            $query->where('id', '!=', $excludeReservationId);
        }

        return !$query->exists();
    }

    /**
     * Create a full reservation with invoice and initial payment.
     *
     * @param array $data Validated request data
     * @return Reservation
     * @throws \Exception
     */
    public function createReservation(array $data, Room $room, HotelSetting $settings): Reservation
    {
        $nights = \Carbon\Carbon::parse($data['checkin_date'])->diffInDays(\Carbon\Carbon::parse($data['checkout_date']));
        $nights = $nights > 0 ? $nights : 1;

        $isBreakfastIncluded = $room->roomType->base_price >= ($settings->breakfast_threshold ?? 600000.00)
            || $room->roomType->breakfast_included;

        // Calculate billing
        $billing = $this->billingService->calculateReservationBilling(
            roomBasePrice: $room->roomType->base_price,
            nights: $nights,
            extraBedPrice: $room->roomType->extra_bed_price,
            hasExtraBed: !empty($data['extra_bed']) && $room->roomType->extra_bed_allowed,
            breakfastPrice: $room->roomType->breakfast_price,
            hasBreakfast: !empty($data['breakfast']),
            isBreakfastIncluded: $isBreakfastIncluded,
            adults: $data['adults'],
            discount: $data['discount'] ?? 0,
            settings: $settings,
        );

        // Generate codes
        $bookingCode = $this->generateBookingCode($room, $settings);
        $invoiceNumber = $this->generateInvoiceNumber($settings);

        // Create Reservation
        $reservation = Reservation::create([
            'reservation_code' => $bookingCode,
            'guest_id' => $data['guest_id'],
            'room_id' => $data['room_id'],
            'checkin_date' => $data['checkin_date'],
            'checkout_date' => $data['checkout_date'],
            'adults' => $data['adults'],
            'children' => $data['children'],
            'status' => ReservationStatus::Confirmed->value,
            'subtotal' => $billing['subtotal'],
            'discount' => $billing['discount'],
            'tax' => $billing['tax'],
            'service_charge' => $billing['serviceCharge'],
            'total' => $billing['total'],
            'created_by' => auth()->id(),
        ]);

        // Save Reservation Details (Addons)
        $this->createReservationDetails($reservation, $data, $room, $nights, $isBreakfastIncluded);

        // Update room status to reserved
        $room->update(['status' => RoomStatus::Reserved->value]);

        // Create Invoice
        $invoice = Invoice::create([
            'invoice_number' => $invoiceNumber,
            'reservation_id' => $reservation->id,
            'invoice_date' => now()->format('Y-m-d'),
            'subtotal' => $billing['subtotal'],
            'tax' => $billing['tax'],
            'service_charge' => $billing['serviceCharge'],
            'discount' => $billing['discount'],
            'total_amount' => $billing['total'],
            'paid_amount' => 0,
            'balance_due' => $billing['total'],
            'status' => PaymentStatus::Unpaid->value,
        ]);

        // Process Initial Payment
        $this->processInitialPayment($invoice, $data, $billing['total']);

        ActivityLogger::log('create', 'reservations', "Membuat reservasi baru: {$bookingCode} untuk kamar {$room->room_number} dengan tipe pembayaran {$data['payment_type']}");

        return $reservation;
    }

    /**
     * Create reservation detail records (extra bed, breakfast, special request).
     */
    private function createReservationDetails(Reservation $reservation, array $data, Room $room, int $nights, bool $isBreakfastIncluded): void
    {
        if (!empty($data['extra_bed']) && $room->roomType->extra_bed_allowed) {
            ReservationDetail::create([
                'reservation_id' => $reservation->id,
                'type' => 'extra_bed',
                'qty' => $nights,
                'price' => $room->roomType->extra_bed_price,
                'notes' => 'Extra Bed Service',
            ]);
        }

        if (!empty($data['breakfast'])) {
            ReservationDetail::create([
                'reservation_id' => $reservation->id,
                'type' => 'breakfast',
                'qty' => $data['adults'] * $nights,
                'price' => $isBreakfastIncluded ? 0 : $room->roomType->breakfast_price,
                'notes' => $isBreakfastIncluded ? 'Breakfast Included' : 'Addon Breakfast',
            ]);
        }

        if (!empty($data['notes'])) {
            ReservationDetail::create([
                'reservation_id' => $reservation->id,
                'type' => 'special_request',
                'qty' => 1,
                'price' => 0,
                'notes' => $data['notes'],
            ]);
        }
    }

    /**
     * Process the initial payment (full or deposit).
     */
    private function processInitialPayment(Invoice $invoice, array $data, float $total): void
    {
        $paymentAmount = match ($data['payment_type']) {
            'full'    => $total,
            'deposit' => (float) ($data['deposit_amount'] ?? 0),
            default   => 0,
        };

        if ($paymentAmount <= 0) {
            return;
        }

        Payment::create([
            'invoice_id' => $invoice->id,
            'payment_method_id' => $data['payment_method_id'],
            'payment_date' => now(),
            'amount' => $paymentAmount,
            'reference_number' => 'Initial Payment ' . ucfirst($data['payment_type']),
            'notes' => 'Pembayaran awal saat booking',
            'status' => 'success',
            'type' => PaymentType::Room->value,
            'created_by' => auth()->id(),
        ]);

        $balance = max(0, $total - $paymentAmount);
        $payStatus = PaymentStatus::fromAmounts($paymentAmount, $total);

        $invoice->update([
            'paid_amount' => $paymentAmount,
            'balance_due' => $balance,
            'status'      => $payStatus->value,
        ]);
    }
}
