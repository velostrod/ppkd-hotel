<?php

use App\Models\User;
use App\Models\Reservation;
use App\Models\RoomInspection;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->foUser = User::whereHas('role', function ($q) {
        $q->where('name', 'front_office');
    })->first();
});

function createDummyReservationAndInvoice($status = 'checked_in') {
    $guest = \App\Models\Guest::create([
        'full_name' => 'John Doe',
        'id_number' => '1234567890',
        'phone' => '08123456789',
        'email' => 'john@example.com',
        'nationality' => 'Indonesia',
    ]);

    $room = \App\Models\Room::first();
    $foStaff = User::first();

    $reservation = Reservation::create([
        'reservation_code' => 'BK-101-20260616-0001',
        'guest_id' => $guest->id,
        'room_id' => $room->id,
        'checkin_date' => now()->format('Y-m-d'),
        'checkout_date' => now()->addDays(2)->format('Y-m-d'),
        'adults' => 2,
        'children' => 0,
        'status' => $status,
        'subtotal' => 700000.00,
        'discount' => 0.00,
        'tax' => 70000.00,
        'service_charge' => 35000.00,
        'total' => 805000.00,
        'created_by' => $foStaff->id,
    ]);

    \App\Models\Invoice::create([
        'invoice_number' => 'INV-20260616-0001',
        'reservation_id' => $reservation->id,
        'invoice_date' => now()->format('Y-m-d'),
        'subtotal' => 805000.00,
        'tax' => 70000.00,
        'service_charge' => 35000.00,
        'discount' => 0.00,
        'total_amount' => 805000.00,
        'paid_amount' => 0.00,
        'balance_due' => 805000.00,
        'status' => 'unpaid',
    ]);

    return $reservation;
}

test('front office can view reservation and request room inspection, and the button becomes disabled', function () {
    // 1. Create a dummy reservation that is checked_in
    $reservation = createDummyReservationAndInvoice('checked_in');

    // 2. Act as FO user and access the page
    $response = $this->actingAs($this->foUser)
        ->get(route('reservations.show', $reservation->id));

    $response->assertStatus(200);
    // Button "Minta Room Inspection" is present
    $response->assertSee('Minta Room Inspection');
    $response->assertDontSee('Inspeksi Kamar (Pending)');

    // 3. Request room inspection
    $responseRequest = $this->actingAs($this->foUser)
        ->post(route('checkouts.request-inspection', $reservation->id));

    $responseRequest->assertRedirect();

    // 4. Reload page and check if button is disabled (Pending)
    $responseReload = $this->actingAs($this->foUser)
        ->get(route('reservations.show', $reservation->id));

    $responseReload->assertSee('Inspeksi Kamar (Pending)');
    $responseReload->assertDontSee('Minta Room Inspection');

    // 5. Complete room inspection
    $inspection = RoomInspection::where('reservation_id', $reservation->id)->first();
    $inspection->update(['status' => 'completed']);

    // 6. Reload page and check if button is disabled (Selesai)
    $responseReload2 = $this->actingAs($this->foUser)
        ->get(route('reservations.show', $reservation->id));

    $responseReload2->assertSee('Inspeksi Kamar (Selesai)');
    $responseReload2->assertDontSee('Minta Room Inspection');
});

test('front office can access checkout page without undefined relationship crash', function () {
    // 1. Create a dummy reservation that is checked_in
    $reservation = createDummyReservationAndInvoice('checked_in');

    // 2. Act as FO user and access checkout invoice page
    $response = $this->actingAs($this->foUser)
        ->get(route('checkouts.invoice', $reservation->id));

    // 3. Assert the page loads successfully (status 200) without RelationNotFoundException crash
    $response->assertStatus(200);
});

test('checked out reservation does not block the room for new bookings', function () {
    // 1. Create a dummy reservation that is checked_out
    $reservation = createDummyReservationAndInvoice('checked_out');

    // 2. Act as FO user and check room availability list
    $response = $this->actingAs($this->foUser)
        ->get(route('reservations.create', [
            'checkin_date' => $reservation->checkin_date->format('Y-m-d'),
            'checkout_date' => $reservation->checkout_date->format('Y-m-d'),
        ]));

    $response->assertStatus(200);
    
    // The room should be available, so it should NOT show "TERBOOKING / NOT AVAILABLE"
    $response->assertDontSee("Kamar {$reservation->room->room_number} - {$reservation->room->roomType->name} (TERBOOKING / NOT AVAILABLE)");
});

test('operational summary report metrics are accurate and synced', function () {
    // 1. Create one checked_out reservation and one cancelled reservation
    $checkoutRes = createDummyReservationAndInvoice('checked_out');
    
    // Create a cancelled reservation
    $guest2 = \App\Models\Guest::create([
        'full_name' => 'Jane Smith',
        'id_number' => '0987654321',
        'phone' => '08765432109',
        'email' => 'jane@example.com',
        'nationality' => 'Indonesia',
    ]);
    $room2 = \App\Models\Room::where('room_number', '102')->first();
    
    $cancelledRes = Reservation::create([
        'reservation_code' => 'BK-102-20260616-0002',
        'guest_id' => $guest2->id,
        'room_id' => $room2->id,
        'checkin_date' => now()->format('Y-m-d'),
        'checkout_date' => now()->addDays(2)->format('Y-m-d'),
        'adults' => 2,
        'children' => 0,
        'status' => 'cancelled',
        'subtotal' => 700000.00,
        'discount' => 0.00,
        'tax' => 70000.00,
        'service_charge' => 35000.00,
        'total' => 805000.00,
        'created_by' => User::first()->id,
    ]);

    \App\Models\Invoice::create([
        'invoice_number' => 'INV-20260616-0002',
        'reservation_id' => $cancelledRes->id,
        'invoice_date' => now()->format('Y-m-d'),
        'subtotal' => 805000.00,
        'tax' => 70000.00,
        'service_charge' => 35000.00,
        'discount' => 0.00,
        'total_amount' => 805000.00,
        'paid_amount' => 0.00,
        'balance_due' => 805000.00,
        'status' => 'unpaid',
    ]);

    // 2. Act as admin and view Laporan Summary
    $adminUser = User::whereHas('role', function ($q) {
        $q->where('name', 'admin');
    })->first();

    $response = $this->actingAs($adminUser)
        ->get(route('reports.summary', [
            'start_date' => now()->subDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(1)->format('Y-m-d'),
        ]));

    $response->assertStatus(200);

    // Verify correct summary values:
    // Total Reservasi Dibuat should count both (since both have checkin_date in range) -> 2
    // Total Checkout Selesai -> 1 (the checkout one)
    // Total Reservasi Batal -> 1 (the cancelled one)
    // Invoice Belum Lunas -> 1 (only the checkout one should be counted as unpaid, because the cancelled one is filtered out!)
    $response->assertSeeText('Total Reservasi Dibuat');
    
    // Fetch variables sent to view
    $data = $response->original->getData();
    expect($data['reservationsCount'])->toBe(2);
    expect($data['checkoutsCount'])->toBe(1);
    expect($data['cancelledCount'])->toBe(1);
    expect($data['invoiceUnpaid'])->toBe(1);
});

test('reservation lists and reports display the final invoice total if available', function () {
    // 1. Create a dummy reservation with invoice and status checked_out
    $reservation = createDummyReservationAndInvoice('checked_out');
    
    // Change total amount on invoice to be different from reservation total
    $reservation->invoice->update([
        'total_amount' => 1091475.00,
    ]);

    // 2. Act as FO user and check reservations index page
    $response = $this->actingAs($this->foUser)
        ->get(route('reservations.index'));

    $response->assertStatus(200);
    // Should display the invoice total amount instead of reservation total
    $response->assertSee('Rp 1.091.475');
    $response->assertDontSee('Rp 805.000'); // Reservation total was 805000

    // 3. Act as Admin user and check reservations report page
    $adminUser = User::whereHas('role', function ($q) {
        $q->where('name', 'admin');
    })->first();

    $responseReport = $this->actingAs($adminUser)
        ->get(route('reports.reservations', [
            'start_date' => now()->subDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(1)->format('Y-m-d'),
        ]));

    $responseReport->assertStatus(200);
    // Should display the invoice total amount
    $responseReport->assertSee('Rp 1.091.475');
    $responseReport->assertDontSee('Rp 805.000');
});



