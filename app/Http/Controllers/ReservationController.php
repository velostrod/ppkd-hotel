<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Reservation;
use App\Models\ReservationDetail;
use App\Models\HotelSetting;
use App\Models\Invoice;
use App\Helpers\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReservationController extends Controller
{
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
            ->where('is_active', true)
            ->get()
            ->map(function($room) use ($checkin, $checkout) {
                // Check if occupied or reserved in date range
                $isBooked = Reservation::where('room_id', $room->id)
                    ->whereNotIn('status', ['cancelled', 'checked_out'])
                    ->where('checkin_date', '<', $checkout)
                    ->where('checkout_date', '>', $checkin)
                    ->exists();
                $room->is_available_in_range = !$isBooked;
                return $room;
            });

        return view('reservations.create', compact('guests', 'rooms', 'checkin', 'checkout'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'guest_id' => 'required|exists:guests,id',
            'room_id' => 'required|exists:rooms,id',
            'checkin_date' => 'required|date|after_or_equal:today',
            'checkout_date' => 'required|date|after:checkin_date',
            'adults' => 'required|integer|min:1',
            'children' => 'required|integer|min:0',
            'breakfast' => 'nullable|boolean',
            'extra_bed' => 'nullable|boolean',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $checkin = $validated['checkin_date'];
        $checkout = $validated['checkout_date'];
        $room = Room::with('roomType')->find($validated['room_id']);

        // 1. Overlap Check
        $isBooked = Reservation::where('room_id', $room->id)
            ->whereNotIn('status', ['cancelled', 'checked_out'])
            ->where('checkin_date', '<', $checkout)
            ->where('checkout_date', '>', $checkin)
            ->exists();

        if ($isBooked) {
            return back()->withErrors(['room_id' => 'Kamar ini sudah terbooking pada tanggal tersebut.'])->withInput();
        }

        // Check room capacity
        if ($validated['adults'] > $room->roomType->capacity) {
            return back()->withErrors(['adults' => "Kapasitas kamar ini hanya untuk {$room->roomType->capacity} orang."])->withInput();
        }

        $settings = HotelSetting::first();
        $nights = Carbon::parse($checkin)->diffInDays(Carbon::parse($checkout));
        $nights = $nights > 0 ? $nights : 1;

        // 2. Pricing Calculations
        $roomCharge = $room->roomType->base_price * $nights;
        $subtotal = $roomCharge;

        $extraBedCharge = 0;
        if ($request->has('extra_bed') && $room->roomType->extra_bed_allowed) {
            $extraBedCharge = $room->roomType->extra_bed_price * $nights;
            $subtotal += $extraBedCharge;
        }

        $breakfastCharge = 0;
        // Check if breakfast is included automatically (base_price > threshold)
        $isBreakfastIncluded = $room->roomType->base_price >= ($settings->breakfast_threshold ?? 600000.00) 
            || $room->roomType->breakfast_included;

        if ($request->has('breakfast') && !$isBreakfastIncluded) {
            $breakfastCharge = $room->roomType->breakfast_price * $validated['adults'] * $nights;
            $subtotal += $breakfastCharge;
        }

        $discount = $request->input('discount', 0);
        $discountedSubtotal = max(0, $subtotal - $discount);

        $taxRate = $settings->tax_rate ?? 10.00;
        $serviceRate = $settings->service_charge_rate ?? 5.00;

        $serviceCharge = $discountedSubtotal * ($serviceRate / 100);
        $tax = ($discountedSubtotal + $serviceCharge) * ($taxRate / 100);
        $total = $discountedSubtotal + $serviceCharge + $tax;

        DB::beginTransaction();
        try {
            // Generate Booking Code: BK-ROOMNO-YYYYMMDD-XXXX
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
            $bookingCode = "{$prefix}-{$roomNo}-{$datePart}-{$seqPart}";

            // Create Reservation
            $reservation = Reservation::create([
                'reservation_code' => $bookingCode,
                'guest_id' => $validated['guest_id'],
                'room_id' => $validated['room_id'],
                'checkin_date' => $checkin,
                'checkout_date' => $checkout,
                'adults' => $validated['adults'],
                'children' => $validated['children'],
                'status' => 'confirmed',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'service_charge' => $serviceCharge,
                'total' => $total,
                'created_by' => auth()->id(),
            ]);

            // Save Reservation Details (Addons)
            if ($request->has('extra_bed') && $room->roomType->extra_bed_allowed) {
                ReservationDetail::create([
                    'reservation_id' => $reservation->id,
                    'type' => 'extra_bed',
                    'qty' => $nights,
                    'price' => $room->roomType->extra_bed_price,
                    'notes' => 'Extra Bed Service',
                ]);
            }

            if ($request->has('breakfast')) {
                ReservationDetail::create([
                    'reservation_id' => $reservation->id,
                    'type' => 'breakfast',
                    'qty' => $validated['adults'] * $nights,
                    'price' => $isBreakfastIncluded ? 0 : $room->roomType->breakfast_price,
                    'notes' => $isBreakfastIncluded ? 'Breakfast Included' : 'Addon Breakfast',
                ]);
            }

            if ($validated['notes']) {
                ReservationDetail::create([
                    'reservation_id' => $reservation->id,
                    'type' => 'special_request',
                    'qty' => 1,
                    'price' => 0,
                    'notes' => $validated['notes'],
                ]);
            }

            // Room status update to reserved (only if check-in is not today or status remains available until checkin)
            // PRD says: "Status kamar berubah menjadi reserved". So let's update room status to reserved.
            $room->update(['status' => 'reserved']);

            // Create Invoice
            $invPrefix = $settings->invoice_prefix ?? 'INV';
            $lastInv = Invoice::where('invoice_number', 'like', "{$invPrefix}-{$datePart}-%")
                ->orderBy('invoice_number', 'desc')
                ->first();

            $invSeq = 1;
            if ($lastInv) {
                $lastInvSeq = (int) substr($lastInv->invoice_number, -4);
                $invSeq = $lastInvSeq + 1;
            }
            $invSeqPart = str_pad($invSeq, 4, '0', STR_PAD_LEFT);
            $invoiceNumber = "{$invPrefix}-{$datePart}-{$invSeqPart}";

            Invoice::create([
                'invoice_number' => $invoiceNumber,
                'reservation_id' => $reservation->id,
                'invoice_date' => now()->format('Y-m-d'),
                'subtotal' => $subtotal,
                'tax' => $tax,
                'service_charge' => $serviceCharge,
                'discount' => $discount,
                'total_amount' => $total,
                'paid_amount' => 0,
                'balance_due' => $total,
                'status' => 'unpaid',
            ]);

            ActivityLogger::log('create', 'reservations', "Membuat reservasi baru: {$bookingCode} untuk kamar {$room->room_number}");
            
            DB::commit();
            return redirect()->route('reservations.index')->with('success', "Booking {$bookingCode} berhasil dibuat.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Reservation $reservation)
    {
        $reservation->load(['guest', 'room.roomType', 'details', 'checkin', 'checkout', 'invoice.payments.paymentMethod', 'charges.chargeType', 'roomInspections']);
        
        $latestInspection = $reservation->roomInspections->sortByDesc('created_at')->first();

        return view('reservations.show', compact('reservation', 'latestInspection'));
    }

    public function cancel(Reservation $reservation)
    {
        if (in_array($reservation->status, ['checked_in', 'checked_out', 'cancelled'])) {
            return back()->with('error', 'Status reservasi tidak dapat dibatalkan.');
        }

        DB::beginTransaction();
        try {
            $reservation->update(['status' => 'cancelled']);
            
            // Release room to available if it was reserved
            $room = $reservation->room;
            if ($room->status === 'reserved') {
                $room->update(['status' => 'available']);
            }

            ActivityLogger::log('update', 'reservations', "Membatalkan reservasi: {$reservation->reservation_code}");

            DB::commit();
            return redirect()->route('reservations.show', $reservation->id)->with('success', 'Reservasi berhasil dibatalkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }
}
