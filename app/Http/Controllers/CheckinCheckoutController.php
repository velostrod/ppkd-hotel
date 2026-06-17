<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Checkin;
use App\Models\Checkout;
use App\Models\Room;
use App\Models\RoomInspection;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Charge;
use App\Models\ChargeType;
use App\Models\HotelSetting;
use App\Helpers\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckinCheckoutController extends Controller
{
    // ==========================================
    // CHECK-IN FLOW
    // ==========================================
    
    public function showCheckin(Reservation $reservation)
    {
        if ($reservation->status !== 'confirmed') {
            return redirect()->route('reservations.show', $reservation->id)->with('error', 'Reservasi harus berstatus confirmed untuk check-in.');
        }
        return view('checkins.create', compact('reservation'));
    }

    public function processCheckin(Request $request, Reservation $reservation)
    {
        if ($reservation->status !== 'confirmed') {
            return redirect()->route('reservations.show', $reservation->id)->with('error', 'Reservasi harus berstatus confirmed.');
        }

        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Create checkin record
            Checkin::create([
                'reservation_id' => $reservation->id,
                'checked_in_at' => now(),
                'front_office_id' => auth()->id(),
                'notes' => $validated['notes'],
            ]);

            // Update reservation status
            $reservation->update(['status' => 'checked_in']);

            // Update room status
            $reservation->room->update(['status' => 'occupied']);

            ActivityLogger::log('checkin', 'reservations', "Tamu check-in: {$reservation->reservation_code}, Kamar: {$reservation->room->room_number}");

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
        if ($reservation->status !== 'checked_in') {
            return back()->with('error', 'Tamu harus berstatus checked_in untuk checkout.');
        }

        // Check if there is already a pending inspection
        $exists = RoomInspection::where('reservation_id', $reservation->id)
            ->where('status', 'pending')
            ->exists();

        if ($exists) {
            return back()->with('info', 'Inspeksi kamar sedang berlangsung.');
        }

        DB::beginTransaction();
        try {
            RoomInspection::create([
                'room_id' => $reservation->room_id,
                'reservation_id' => $reservation->id,
                'inspected_by' => auth()->id(), // Temporary inspector assigned, will be updated by HK
                'inspection_date' => now(),
                'room_condition' => 'good',
                'damage_found' => false,
                'damage_cost' => 0,
                'notes' => 'Menunggu inspeksi dari Housekeeping',
                'status' => 'pending',
            ]);

            ActivityLogger::log('create', 'room_inspections', "Meminta inspeksi kamar {$reservation->room->room_number} untuk checkout");

            DB::commit();
            return back()->with('success', 'Permintaan inspeksi kamar dikirim ke Housekeeping.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal meminta inspeksi kamar.');
        }
    }

    public function showCheckout(Reservation $reservation)
    {
        if ($reservation->status !== 'checked_in') {
            return redirect()->route('reservations.show', $reservation->id)->with('error', 'Checkout hanya bisa untuk reservasi aktif (checked_in).');
        }

        $reservation->load(['guest', 'room.roomType', 'details', 'invoice.payments.paymentMethod', 'charges.chargeType']);
        $paymentMethods = PaymentMethod::where('is_active', true)->get();
        
        // Find inspection results
        $inspection = RoomInspection::where('reservation_id', $reservation->id)
            ->orderBy('created_at', 'desc')
            ->first();

        // Calculate and refresh invoice total based on current charges (Fnb, Laundry, Damage, Extra Bed)
        $subtotal = $reservation->subtotal; // Room rates + extra bed + breakfast from reservation
        
        // Add service charges (Laundry, FnB, Damage) dynamically
        $additionalCharges = $reservation->charges()->sum('amount');
        $currentSubtotal = $subtotal + $additionalCharges;
        
        $settings = HotelSetting::first();
        $discount = $reservation->discount;
        $discountedSubtotal = max(0, $currentSubtotal - $discount);
        
        $taxRate = $settings->tax_rate ?? 10.00;
        $serviceRate = $settings->service_charge_rate ?? 5.00;

        $serviceCharge = $discountedSubtotal * ($serviceRate / 100);
        $tax = ($discountedSubtotal + $serviceCharge) * ($taxRate / 100);
        $total = $discountedSubtotal + $serviceCharge + $tax;

        // Sync to Invoice record
        $invoice = $reservation->invoice;
        if ($invoice) {
            $paid = $invoice->payments()->where('status', 'success')->sum('amount');
            $balance = max(0, $total - $paid);
            
            $payStatus = 'unpaid';
            if ($paid > 0) {
                $payStatus = $balance <= 0 ? 'paid' : 'partial';
            }

            $invoice->update([
                'subtotal' => $currentSubtotal,
                'tax' => $tax,
                'service_charge' => $serviceCharge,
                'total_amount' => $total,
                'paid_amount' => $paid,
                'balance_due' => $balance,
                'status' => $payStatus,
            ]);
        }

        return view('checkouts.invoice', compact('reservation', 'paymentMethods', 'inspection', 'total', 'serviceCharge', 'tax'));
    }

    public function processPayment(Request $request, Reservation $reservation)
    {
        $invoice = $reservation->invoice;
        if (!$invoice) {
            return back()->with('error', 'Invoice tidak ditemukan.');
        }

        $validated = $request->validate([
            'payment_method_id' => 'required|exists:payment_methods,id',
            'amount' => 'required|numeric|min:1|max:' . $invoice->balance_due,
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            Payment::create([
                'invoice_id' => $invoice->id,
                'payment_method_id' => $validated['payment_method_id'],
                'payment_date' => now(),
                'amount' => $validated['amount'],
                'reference_number' => $validated['reference_number'],
                'notes' => $validated['notes'],
                'status' => 'success',
                'created_by' => auth()->id(),
            ]);

            // Refresh invoice status
            $paid = $invoice->payments()->where('status', 'success')->sum('amount');
            $balance = max(0, $invoice->total_amount - $paid);
            $payStatus = 'unpaid';
            if ($paid > 0) {
                $payStatus = $balance <= 0 ? 'paid' : 'partial';
            }

            $invoice->update([
                'paid_amount' => $paid,
                'balance_due' => $balance,
                'status' => $payStatus,
            ]);

            ActivityLogger::log('create', 'payments', "Menerima pembayaran Rp " . number_class_format($validated['amount']) . " untuk Booking {$reservation->reservation_code}");

            DB::commit();
            return back()->with('success', 'Pembayaran berhasil dicatat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mencatat pembayaran: ' . $e->getMessage());
        }
    }

    public function processCheckout(Request $request, Reservation $reservation)
    {
        if ($reservation->status !== 'checked_in') {
            return back()->with('error', 'Reservasi harus berstatus checked_in.');
        }

        $invoice = $reservation->invoice;
        if ($invoice && $invoice->balance_due > 0) {
            return back()->with('error', 'Tagihan belum lunas. Silakan lakukan pembayaran terlebih dahulu.');
        }

        // Verify if room inspection has been completed
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
            // Save checkout details
            Checkout::create([
                'reservation_id' => $reservation->id,
                'checked_out_at' => now(),
                'front_office_id' => auth()->id(),
                'final_bill_total' => $invoice ? $invoice->total_amount : $reservation->total,
                'notes' => $validated['notes'],
            ]);

            // Update reservation status
            $reservation->update(['status' => 'checked_out']);

            // Update room status to dirty (must be cleaned and inspected before available)
            $reservation->room->update(['status' => 'dirty']);

            // Create cleaning request automatically for Housekeeping
            \App\Models\HousekeepingRequest::create([
                'reservation_id' => $reservation->id,
                'room_id' => $reservation->room_id,
                'requested_by' => auth()->id(),
                'request_type' => 'checkout_cleaning',
                'priority' => 'high',
                'status' => 'pending',
                'request_time' => now(),
                'notes' => 'Pembersihan otomatis setelah checkout tamu.',
            ]);

            ActivityLogger::log('checkout', 'reservations', "Tamu checkout: {$reservation->reservation_code}, Kamar berubah status menjadi dirty");

            DB::commit();
            return redirect()->route('reservations.show', $reservation->id)->with('success', 'Checkout berhasil diproses. Kamar ditandai DIRTY dan antrean cleaning dikirim ke Housekeeping.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses checkout: ' . $e->getMessage());
        }
    }

    public function printInvoice(Reservation $reservation)
    {
        $reservation->load(['guest', 'room.roomType', 'details', 'invoice.payments.paymentMethod', 'charges.chargeType']);
        $settings = HotelSetting::first();
        return view('checkouts.print', compact('reservation', 'settings'));
    }
}

// Simple Helper for Formatting IDR Currency inside namespace if needed, or fallback
if (!function_exists('number_class_format')) {
    function number_class_format($number) {
        return number_format($number, 0, ',', '.');
    }
}
