<?php

namespace App\Http\Controllers;

use App\Enums\ReservationStatus;
use App\Enums\RoomStatus;
use App\Http\Requests\ProcessCheckinRequest;
use App\Http\Requests\ProcessPaymentRequest;
use App\Models\HotelSetting;
use App\Models\PaymentMethod;
use App\Models\Reservation;
use App\Models\RoomInspection;
use App\Services\BillingService;
use App\Services\CheckoutService;
use App\Helpers\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckinCheckoutController extends Controller
{
    public function __construct(
        private readonly CheckoutService $checkoutService,
        private readonly BillingService $billingService,
    ) {}

    // ==========================================
    // CHECK-IN FLOW
    // ==========================================

    public function showCheckin(Reservation $reservation)
    {
        if ($reservation->status !== ReservationStatus::Confirmed->value) {
            return redirect()->route('reservations.show', $reservation->id)->with('error', 'Reservasi harus berstatus confirmed untuk check-in.');
        }

        $reservation->load(['guest', 'room.roomType', 'invoice.payments']);
        $paymentMethods = PaymentMethod::where('is_active', true)->get();
        $settings = HotelSetting::first();

        // Room balance: only room-type payments count
        $invoice = $reservation->invoice;
        $roomPaid = $invoice
            ? $invoice->payments()->where('status', 'success')->where('type', 'room')->sum('amount')
            : 0;
        $roomBalance = $invoice ? max(0, (float) $invoice->total_amount - $roomPaid) : 0;
        $depositRequired = (float) ($settings->security_deposit_amount ?? 0);

        return view('checkins.create', compact('reservation', 'paymentMethods', 'roomBalance', 'depositRequired'));
    }

    public function processCheckin(ProcessCheckinRequest $request, Reservation $reservation)
    {
        if ($reservation->status !== ReservationStatus::Confirmed->value) {
            return redirect()->route('reservations.show', $reservation->id)->with('error', 'Reservasi harus berstatus confirmed.');
        }

        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $this->checkoutService->processCheckin($reservation, $validated);

            DB::commit();
            return redirect()->route('reservations.show', $reservation->id)->with('success', 'Check-in berhasil diproses.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // ==========================================
    // CHECKOUT FLOW
    // ==========================================

    public function requestInspection(Reservation $reservation)
    {
        if ($reservation->status !== ReservationStatus::CheckedIn->value) {
            return back()->with('error', 'Tamu harus berstatus checked_in untuk checkout.');
        }

        $exists = RoomInspection::where('reservation_id', $reservation->id)
            ->where('status', 'pending')
            ->exists();

        if ($exists) {
            return back()->with('info', 'Inspeksi kamar sedang berlangsung.');
        }

        DB::beginTransaction();
        try {
            $this->checkoutService->requestInspection($reservation);

            ActivityLogger::log('create', 'room_inspections', "Meminta inspeksi kamar {$reservation->room->room_number} untuk checkout");

            DB::commit();
            return back()->with('success', 'Permintaan inspeksi kamar dikirim ke Housekeeping.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal meminta inspeksi kamar.');
        }
    }

    // ==========================================
    // SHOW CHECKOUT (Invoice Preview)
    // ==========================================

    public function showCheckout(Reservation $reservation)
    {
        if ($reservation->status !== ReservationStatus::CheckedIn->value) {
            return redirect()->route('reservations.show', $reservation->id)->with('error', 'Checkout hanya bisa untuk reservasi aktif (checked_in).');
        }

        $reservation->load(['guest', 'room.roomType', 'details', 'invoice.payments.paymentMethod', 'charges.chargeType']);
        $paymentMethods = PaymentMethod::where('is_active', true)->get();

        $inspection = RoomInspection::where('reservation_id', $reservation->id)
            ->orderBy('created_at', 'desc')
            ->first();

        $settings = HotelSetting::first();
        $billing = $this->billingService->computeCheckoutBilling($reservation, $settings, true);

        $invoice = $reservation->invoice;
        if ($invoice) {
            $this->billingService->syncInvoiceTotals($invoice, $billing);
        }

        return view('checkouts.invoice', compact('reservation', 'paymentMethods', 'inspection', 'billing'));
    }

    // ==========================================
    // PROCESS PAYMENT (room balance at checkout)
    // ==========================================

    public function processPayment(ProcessPaymentRequest $request, Reservation $reservation)
    {
        $invoice = $reservation->invoice;
        if (!$invoice) {
            return back()->with('error', 'Invoice tidak ditemukan.');
        }

        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $this->checkoutService->processPayment($invoice, $validated);

            DB::commit();
            return back()->with('success', 'Pembayaran berhasil dicatat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mencatat pembayaran: ' . $e->getMessage());
        }
    }

    // ==========================================
    // RETURN DEPOSIT
    // ==========================================

    public function returnDeposit(Request $request, Reservation $reservation)
    {
        $invoice = $reservation->invoice;
        if (!$invoice) {
            return back()->with('error', 'Invoice tidak ditemukan.');
        }

        $validated = $request->validate([
            'payment_method_id' => 'required|exists:payment_methods,id',
            'return_amount'     => 'required|numeric|min:1',
            'notes'             => 'nullable|string|max:500',
        ]);

        $depositHeld = $invoice->payments()->where('status', 'success')->where('type', 'deposit')->sum('amount');
        $depositReturned = $invoice->payments()->where('status', 'success')->where('type', 'deposit_return')->sum('amount');
        $maxReturn = max(0, $depositHeld - $depositReturned);

        if ((float) $validated['return_amount'] > $maxReturn) {
            return back()->with('error', "Jumlah pengembalian melebihi deposit yang tersisa (Rp " . number_format($maxReturn, 0, ',', '.') . ").");
        }

        DB::beginTransaction();
        try {
            $this->checkoutService->processDepositReturn($invoice, (float) $validated['return_amount'], $validated);

            DB::commit();
            return back()->with('success', 'Pengembalian deposit berhasil dicatat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mencatat pengembalian deposit: ' . $e->getMessage());
        }
    }

    // ==========================================
    // PROCESS CHECKOUT (Final)
    // ==========================================

    public function processCheckout(Request $request, Reservation $reservation)
    {
        if ($reservation->status !== ReservationStatus::CheckedIn->value) {
            return back()->with('error', 'Reservasi harus berstatus checked_in.');
        }

        $invoice = $reservation->invoice;
        if ($invoice && $invoice->balance_due > 0) {
            return back()->with('error', 'Tagihan kamar belum lunas. Silakan lakukan pembayaran terlebih dahulu.');
        }

        $pendingInspection = RoomInspection::where('reservation_id', $reservation->id)
            ->where('status', 'pending')
            ->exists();

        if ($pendingInspection) {
            return back()->with('error', 'Kamar belum selesai diinspeksi oleh Housekeeping.');
        }

        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $this->checkoutService->processCheckout($reservation, $validated['notes']);

            DB::commit();
            return redirect()->route('reservations.show', $reservation->id)->with('success', 'Checkout berhasil diproses.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses checkout: ' . $e->getMessage());
        }
    }

    // ==========================================
    // PRINT INVOICE
    // ==========================================

    public function printInvoice(Reservation $reservation)
    {
        $reservation->load(['guest', 'room.roomType', 'details', 'invoice.payments.paymentMethod', 'charges.chargeType']);
        $settings = HotelSetting::first();

        $billing = null;
        if ($reservation->status === ReservationStatus::CheckedIn->value) {
            $billing = $this->billingService->computeCheckoutBilling($reservation, $settings, true);
        }

        return view('checkouts.print', compact('reservation', 'settings', 'billing'));
    }
}
