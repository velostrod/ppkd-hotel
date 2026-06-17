<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Helpers\ActivityLogger;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    public function index(Request $request)
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'id_number' => 'required|string|max:50',
            'nationality' => 'required|string|max:100',
            'gender' => 'required|in:male,female',
            'notes' => 'nullable|string',
        ]);

        $guest = Guest::create($validated);

        ActivityLogger::log('create', 'guests', "Mendaftarkan tamu baru: {$guest->full_name} (ID: {$guest->id_number})");

        return redirect()->route('guests.index')->with('success', 'Tamu berhasil didaftarkan.');
    }

    public function edit(Guest $guest)
    {
        return view('guests.edit', compact('guest'));
    }

    public function update(Request $request, Guest $guest)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'id_number' => 'required|string|max:50',
            'nationality' => 'required|string|max:100',
            'gender' => 'required|in:male,female',
            'notes' => 'nullable|string',
        ]);

        $guest->update($validated);

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
