<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\HotelSetting;
use App\Helpers\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // ==========================================
    // USER/STAFF MANAGEMENT
    // ==========================================

    public function users()
    {
        $users = User::with('role')->paginate(10);
        $roles = Role::all();
        return view('admin.users.index', compact('users', 'roles'));
    }

    public function storeUser(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'],
            'status' => $validated['status'],
        ]);

        ActivityLogger::log('create', 'users', "Membuat akun staff baru: {$user->name} ({$user->email})");

        return redirect()->route('admin.users')->with('success', 'User berhasil ditambahkan.');
    }

    public function updateUser(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role_id' => $validated['role_id'],
            'status' => $validated['status'],
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        ActivityLogger::log('update', 'users', "Mengubah data akun staff: {$user->name} ({$user->email})");

        return redirect()->route('admin.users')->with('success', 'User berhasil diperbarui.');
    }

    public function toggleUserStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);

        ActivityLogger::log('update', 'users', "Mengubah status akun staff {$user->name} menjadi {$newStatus}");

        return back()->with('success', "Status user {$user->name} berhasil diubah.");
    }

    // ==========================================
    // ROOM TYPE CRUD
    // ==========================================

    public function roomTypes()
    {
        $roomTypes = RoomType::paginate(10);
        return view('admin.room_types.index', compact('roomTypes'));
    }

    public function storeRoomType(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'capacity' => 'required|integer|min:1',
            'breakfast_included' => 'boolean',
            'breakfast_price' => 'required|numeric|min:0',
            'extra_bed_allowed' => 'boolean',
            'extra_bed_price' => 'required|numeric|min:0',
        ]);

        $validated['breakfast_included'] = $request->has('breakfast_included');
        $validated['extra_bed_allowed'] = $request->has('extra_bed_allowed');
        $validated['is_active'] = true;

        $roomType = RoomType::create($validated);

        ActivityLogger::log('create', 'room_types', "Membuat tipe kamar baru: {$roomType->name}");

        return redirect()->route('admin.room-types')->with('success', 'Tipe kamar berhasil ditambahkan.');
    }

    public function updateRoomType(Request $request, RoomType $roomType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'capacity' => 'required|integer|min:1',
            'breakfast_included' => 'boolean',
            'breakfast_price' => 'required|numeric|min:0',
            'extra_bed_allowed' => 'boolean',
            'extra_bed_price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['breakfast_included'] = $request->has('breakfast_included');
        $validated['extra_bed_allowed'] = $request->has('extra_bed_allowed');
        $validated['is_active'] = $request->has('is_active');

        $roomType->update($validated);

        ActivityLogger::log('update', 'room_types', "Mengubah tipe kamar: {$roomType->name}");

        return redirect()->route('admin.room-types')->with('success', 'Tipe kamar berhasil diperbarui.');
    }

    // ==========================================
    // ROOM CRUD
    // ==========================================

    public function rooms()
    {
        $rooms = Room::with('roomType')->paginate(10);
        $roomTypes = RoomType::where('is_active', true)->get();
        return view('admin.rooms.index', compact('rooms', 'roomTypes'));
    }

    public function storeRoom(Request $request)
    {
        $validated = $request->validate([
            'room_number' => 'required|string|max:20|unique:rooms',
            'room_type_id' => 'required|exists:room_types,id',
            'floor' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $validated['status'] = 'available';
        $validated['is_active'] = true;

        $room = Room::create($validated);

        ActivityLogger::log('create', 'rooms', "Membuat data kamar fisik baru: {$room->room_number}");

        return redirect()->route('admin.rooms')->with('success', 'Kamar berhasil ditambahkan.');
    }

    public function updateRoom(Request $request, Room $room)
    {
        $validated = $request->validate([
            'room_number' => 'required|string|max:20|unique:rooms,room_number,' . $room->id,
            'room_type_id' => 'required|exists:room_types,id',
            'floor' => 'required|integer|min:1',
            'status' => 'required|in:available,reserved,occupied,dirty,cleaning,inspected,maintenance,out_of_order',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $room->update($validated);

        ActivityLogger::log('update', 'rooms', "Mengubah data kamar fisik: {$room->room_number}");

        return redirect()->route('admin.rooms')->with('success', 'Kamar berhasil diperbarui.');
    }

    // ==========================================
    // HOTEL SETTINGS
    // ==========================================

    public function settings()
    {
        $settings = HotelSetting::first();
        return view('admin.settings.edit', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $settings = HotelSetting::first();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'required|string|max:50',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'service_charge_rate' => 'required|numeric|min:0|max:100',
            'breakfast_threshold' => 'required|numeric|min:0',
            'invoice_prefix' => 'required|string|max:10',
            'booking_prefix' => 'required|string|max:10',
            'checkin_time' => 'required|date_format:H:i',
            'checkout_time' => 'required|date_format:H:i',
        ]);

        $settings->update($validated);

        ActivityLogger::log('update', 'hotel_settings', "Memperbarui konfigurasi sistem hotel.");

        return redirect()->route('admin.settings')->with('success', 'Pengaturan hotel berhasil diperbarui.');
    }
}
