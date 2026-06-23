<?php

namespace App\Http\Controllers;

use App\Enums\ReservationStatus;
use App\Enums\RoomStatus;
use App\Http\Requests\StoreReservationRequest;
use App\Models\Guest;
use App\Models\Room;
use App\Models\HotelSetting;
use App\Models\PaymentMethod;
use App\Models\Reservation;
use App\Services\BillingService;
use App\Services\ReservationService;
use App\Helpers\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ReservationController extends Controller
{
    public function __construct(
        private readonly ReservationService $reservationService,
        private readonly BillingService $billingService,
    ) {}

    public function index(Request $request)
    {
        $query = Reservation::with(['guest', 'room.roomType', 'invoice']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('reservation_code', 'like', "%{$search}%")
                  ->orWhereHas('guest', function($g) use ($search) {
                      $g->where('full_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('room', function($r) use ($search) {
                      $r->where('room_number', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $reservations = $query->orderBy('created_at', 'desc')->paginate(10);
        return view('reservations.index', compact('reservations'));
    }

    public function create(Request $request)
    {
        $guests = Guest::orderBy('full_name')->get();
        
        $checkin = $request->input('checkin_date', now()->format('Y-m-d'));
        $checkout = $request->input('checkout_date', now()->addDay()->format('Y-m-d'));
        
        $rooms = Room::with('roomType')
            ->active()
            ->get()
            ->map(function($room) use ($checkin, $checkout) {
                $room->is_available_in_range = $room->isAvailableForDates($checkin, $checkout);
                return $room;
            });

        $paymentMethods = PaymentMethod::where('is_active', true)->get();

        return view('reservations.create', compact('guests', 'rooms', 'checkin', 'checkout', 'paymentMethods'));
    }

    public function store(StoreReservationRequest $request)
    {
        $validated = $request->validated();
        $room = Room::with('roomType')->find($validated['room_id']);

        // 1. Overlap Check
        if (!$this->reservationService->isRoomAvailable($room->id, $validated['checkin_date'], $validated['checkout_date'])) {
            return back()->withErrors(['room_id' => 'Kamar ini sudah terbooking pada tanggal tersebut.'])->withInput();
        }

        // Check room capacity
        if ($validated['adults'] > $room->roomType->capacity) {
            return back()->withErrors(['adults' => "Kapasitas kamar ini hanya untuk {$room->roomType->capacity} orang."])->withInput();
        }

        $settings = HotelSetting::first();

        DB::beginTransaction();
        try {
            $reservation = $this->reservationService->createReservation($validated, $room, $settings);

            DB::commit();
            return redirect()->route('reservations.index')->with('success', "Booking {$reservation->reservation_code} berhasil dibuat.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal membuat reservasi', ['exception' => $e]);
            return back()->with('error', 'Terjadi kesalahan sistem. Silakan coba lagi.')->withInput();
        }
    }

    public function show(Reservation $reservation)
    {
        $reservation->load(['guest', 'room.roomType', 'details', 'checkin', 'checkout', 'invoice.payments.paymentMethod', 'charges.chargeType', 'roomInspections']);

        $latestInspection = $reservation->roomInspections->sortByDesc('created_at')->first();

        $billing = null;
        if ($reservation->status === \App\Enums\ReservationStatus::CheckedIn->value) {
            $settings = HotelSetting::first();
            $billing = $this->billingService->computeCheckoutBilling($reservation, $settings, true);
        }

        return view('reservations.show', compact('reservation', 'latestInspection', 'billing'));
    }

    public function cancel(Reservation $reservation)
    {
        if (in_array($reservation->status, ReservationStatus::nonCancellableStatuses())) {
            return back()->with('error', 'Status reservasi tidak dapat dibatalkan.');
        }

        DB::beginTransaction();
        try {
            $reservation->update(['status' => ReservationStatus::Cancelled->value]);
            
            // Release room to available if it was reserved
            $room = $reservation->room;
            if ($room->status === RoomStatus::Reserved->value) {
                $room->update(['status' => RoomStatus::Available->value]);
            }

            ActivityLogger::log('update', 'reservations', "Membatalkan reservasi: {$reservation->reservation_code}");

            DB::commit();
            return redirect()->route('reservations.show', $reservation->id)->with('success', 'Reservasi berhasil dibatalkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    public function extend(Request $request, Reservation $reservation)
    {
        if (!in_array($reservation->status, [ReservationStatus::Confirmed->value, ReservationStatus::CheckedIn->value])) {
            return back()->with('error', 'Perpanjangan hanya dapat dilakukan untuk reservasi aktif atau terkonfirmasi.');
        }

        $validated = $request->validate([
            'extend_nights' => 'required|integer|min:1',
        ]);

        $additionalNights = (int) $validated['extend_nights'];
        
        $currentCheckoutDate = Carbon::parse($reservation->checkout_date);
        $newCheckoutDate = $currentCheckoutDate->copy()->addDays($additionalNights);

        // Check availability overlap
        if (!$this->reservationService->isRoomAvailable(
            $reservation->room_id,
            $currentCheckoutDate->format('Y-m-d'),
            $newCheckoutDate->format('Y-m-d'),
            $reservation->id
        )) {
            return back()->with('error', "Kamar #{$reservation->room->room_number} tidak tersedia untuk periode perpanjangan tersebut (sudah terbooking/diisi tamu lain).");
        }

        DB::beginTransaction();
        try {
            $checkin = Carbon::parse($reservation->checkin_date);
            $newNights = $checkin->diffInDays($newCheckoutDate);
            $newNights = $newNights > 0 ? $newNights : 1;

            $settings = HotelSetting::first();

            // Recalculate addon details
            $extraBedDetail = $reservation->details()->where('type', 'extra_bed')->first();
            if ($extraBedDetail) {
                $extraBedDetail->update(['qty' => $newNights]);
            }

            $breakfastDetail = $reservation->details()->where('type', 'breakfast')->first();
            if ($breakfastDetail) {
                $breakfastDetail->update(['qty' => $reservation->adults * $newNights]);
            }

            // Use BillingService for calculation
            $billing = $this->billingService->calculateExtensionBilling($reservation, $newNights, $settings);

            // Update Reservation
            $reservation->update([
                'checkout_date' => $newCheckoutDate->format('Y-m-d'),
                'subtotal' => $billing['subtotal'],
                'tax' => $billing['tax'],
                'service_charge' => $billing['serviceCharge'],
                'total' => $billing['total'],
            ]);

            // Update Invoice
            $invoice = $reservation->invoice;
            if ($invoice) {
                $additionalCharges = $reservation->charges()->sum('amount');
                $invoiceSubtotal = $billing['subtotal'] + $additionalCharges;

                $discount = $reservation->discount;
                $serviceRate = $settings->service_charge_rate ?? 5.00;
                $taxRate = $settings->tax_rate ?? 10.00;

                $discountedSubtotal = max(0, $invoiceSubtotal - $discount);
                $invoiceServiceCharge = $discountedSubtotal * ($serviceRate / 100);
                $invoiceTax = ($discountedSubtotal + $invoiceServiceCharge) * ($taxRate / 100);
                $invoiceTotal = $discountedSubtotal + $invoiceServiceCharge + $invoiceTax;

                $invoice->update([
                    'subtotal' => $invoiceSubtotal,
                    'tax' => $invoiceTax,
                    'service_charge' => $invoiceServiceCharge,
                    'total_amount' => $invoiceTotal,
                ]);

                $this->billingService->recalculateInvoiceAfterPayment($invoice);
            }

            ActivityLogger::log('update', 'reservations', "Memperpanjang masa menginap reservasi {$reservation->reservation_code} sebanyak {$additionalNights} malam. Checkout baru: {$newCheckoutDate->format('d/m/Y')}");

            DB::commit();
            return redirect()->route('reservations.show', $reservation->id)->with('success', "Masa menginap berhasil diperpanjang {$additionalNights} malam.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memperpanjang reservasi', ['exception' => $e]);
            return back()->with('error', 'Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }
}
