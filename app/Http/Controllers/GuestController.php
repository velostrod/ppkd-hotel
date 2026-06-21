<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGuestRequest;
use App\Models\Guest;
use App\Helpers\ActivityLogger;

class GuestController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $query = Guest::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('id_number', 'like', "%{$search}%");
            });
        }

        $guests = $query->orderBy('full_name')->paginate(10);
        return view('guests.index', compact('guests'));
    }

    public function create()
    {
        return view('guests.create');
    }

    public function store(StoreGuestRequest $request)
    {
        $guest = Guest::create($request->validated());

        ActivityLogger::log('create', 'guests', "Mendaftarkan tamu baru: {$guest->full_name} (ID: {$guest->id_number})");

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'guest'   => ['id' => $guest->id, 'full_name' => $guest->full_name, 'id_number' => $guest->id_number],
            ]);
        }

        return redirect()->route('guests.index')->with('success', 'Tamu berhasil didaftarkan.');
    }

    public function edit(Guest $guest)
    {
        return view('guests.edit', compact('guest'));
    }

    public function update(StoreGuestRequest $request, Guest $guest)
    {
        $guest->update($request->validated());

        ActivityLogger::log('update', 'guests', "Mengubah data tamu: {$guest->full_name} (ID: {$guest->id_number})");

        return redirect()->route('guests.index')->with('success', 'Data tamu berhasil diperbarui.');
    }

    public function destroy(Guest $guest)
    {
        if ($guest->reservations()->exists()) {
            return redirect()->route('guests.index')->with('error', 'Tamu tidak dapat dihapus karena memiliki riwayat reservasi.');
        }

        $name = $guest->full_name;
        $guest->delete();

        ActivityLogger::log('delete', 'guests', "Menghapus data tamu: {$name}");

        return redirect()->route('guests.index')->with('success', 'Data tamu berhasil dihapus.');
    }
}
