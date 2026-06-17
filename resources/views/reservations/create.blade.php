@extends('layouts.fo')

@section('header-title', 'Buat Reservasi Baru')

@section('content')
<div class="space-y-8 max-w-4xl mx-auto">
    <!-- 1. Check Date Range Availability (GET Form) -->
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
        <h3 class="text-base font-bold text-slate-800 mb-4">Cek Ketersediaan Kamar</h3>
        <form method="GET" action="{{ route('reservations.create') }}" class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
            <div>
                <label for="checkin_date" class="block text-sm font-semibold text-slate-700 mb-1.5">Tanggal Check-In</label>
                <input type="date" id="checkin_date" name="checkin_date" value="{{ request('checkin_date', $checkin) }}" required min="{{ now()->format('Y-m-d') }}" class="w-full px-4 py-2 border border-slate-200 rounded-xl text-sm focus:ring-amber-500" />
            </div>
            
            <div>
                <label for="checkout_date" class="block text-sm font-semibold text-slate-700 mb-1.5">Tanggal Check-Out</label>
                <input type="date" id="checkout_date" name="checkout_date" value="{{ request('checkout_date', $checkout) }}" required min="{{ now()->addDay()->format('Y-m-d') }}" class="w-full px-4 py-2 border border-slate-200 rounded-xl text-sm focus:ring-amber-500" />
            </div>
            
            <div>
                <button type="submit" class="w-full py-2.5 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-sm font-bold shadow-sm uppercase tracking-wider transition-colors">
                    Perbarui Rencana Menginap
                </button>
            </div>
        </form>
    </div>

    <!-- 2. Main Booking Form (POST Form) -->
    <div class="bg-white p-8 rounded-2xl border border-slate-100 shadow-sm">
        <div class="flex items-center justify-between mb-8 pb-4 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-800">Formulir Booking Kamar</h3>
            <a href="{{ route('reservations.index') }}" class="text-slate-400 hover:text-slate-600 text-sm flex items-center font-medium">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali
            </a>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-r-lg text-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('reservations.store') }}" class="space-y-8">
            @csrf
            
            <!-- Dates (Hidden to carry values from the checker) -->
            <input type="hidden" name="checkin_date" value="{{ request('checkin_date', $checkin) }}" />
            <input type="hidden" name="checkout_date" value="{{ request('checkout_date', $checkout) }}" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Guest Selection -->
                <div>
                    <label for="guest_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Pilih Tamu <span class="text-rose-500">*</span></label>
                    <div class="flex items-center space-x-2">
                        <select id="guest_id" name="guest_id" required class="flex-1 px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-amber-500">
                            <option value="">-- Cari / Pilih Tamu --</option>
                            @foreach($guests as $guest)
                                <option value="{{ $guest->id }}" {{ old('guest_id') == $guest->id ? 'selected' : '' }}>
                                    {{ $guest->full_name }} ({{ $guest->id_number }})
                                </option>
                            @endforeach
                        </select>
                        <a href="{{ route('guests.create') }}" target="_blank" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-bold border border-slate-200" title="Daftarkan Tamu Baru">+ Baru</a>
                    </div>
                </div>

                <!-- Room Selection -->
                <div>
                    <label for="room_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Pilih Kamar Tersedia <span class="text-rose-500">*</span></label>
                    <select id="room_id" name="room_id" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-amber-500">
                        <option value="">-- Pilih Kamar --</option>
                        @foreach($rooms as $room)
                            @if($room->is_available_in_range)
                                <option value="{{ $room->id }}" {{ old('room_id') == $room->id || request('room_id') == $room->id ? 'selected' : '' }}>
                                    Kamar {{ $room->room_number }} - {{ $room->roomType->name }} (Rp {{ number_format($room->roomType->base_price, 0, ',', '.') }}/malam, Kapasitas: {{ $room->roomType->capacity }} Pax)
                                </option>
                            @else
                                <option disabled class="text-slate-300">
                                    Kamar {{ $room->room_number }} - {{ $room->roomType->name }} (TERBOOKING / NOT AVAILABLE)
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <!-- Number of Adults -->
                <div>
                    <label for="adults" class="block text-sm font-semibold text-slate-700 mb-1.5">Jumlah Tamu Dewasa <span class="text-rose-500">*</span></label>
                    <input type="number" id="adults" name="adults" value="{{ old('adults', 1) }}" min="1" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-amber-500" />
                </div>

                <!-- Number of Children -->
                <div>
                    <label for="children" class="block text-sm font-semibold text-slate-700 mb-1.5">Jumlah Anak-anak</label>
                    <input type="number" id="children" name="children" value="{{ old('children', 0) }}" min="0" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-amber-500" />
                </div>
            </div>

            <!-- Services & Addons Block -->
            <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100">
                <h4 class="text-sm font-bold text-slate-800 mb-4">Layanan & Add-on Tambahan</h4>
                
                <div class="space-y-4">
                    <!-- Extra Bed Add-on -->
                    <label class="flex items-start cursor-pointer select-none">
                        <input type="checkbox" name="extra_bed" value="1" {{ old('extra_bed') ? 'checked' : '' }} class="rounded border-slate-300 text-amber-500 focus:ring-0 mt-1 w-4 h-4 mr-3" />
                        <div>
                            <span class="text-sm font-semibold text-slate-700">Extra Bed</span>
                            <p class="text-xs text-slate-500">Menyediakan ranjang ekstra di kamar dengan biaya tambahan harian.</p>
                        </div>
                    </label>

                    <!-- Breakfast Add-on -->
                    <label class="flex items-start cursor-pointer select-none">
                        <input type="checkbox" name="breakfast" value="1" {{ old('breakfast') ? 'checked' : '' }} class="rounded border-slate-300 text-amber-500 focus:ring-0 mt-1 w-4 h-4 mr-3" />
                        <div>
                            <span class="text-sm font-semibold text-slate-700">Breakfast (Sarapan Pagi)</span>
                            <p class="text-xs text-slate-500">Mendapatkan sarapan pagi. Gratis/Included jika tarif kamar di atas Rp 600.000,00.</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Discount & Notes -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Discount -->
                <div>
                    <label for="discount" class="block text-sm font-semibold text-slate-700 mb-1.5">Nominal Diskon (Rp)</label>
                    <input type="number" id="discount" name="discount" value="{{ old('discount', 0) }}" min="0" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-amber-500" />
                </div>

                <!-- Notes / Special Requests -->
                <div>
                    <label for="notes" class="block text-sm font-semibold text-slate-700 mb-1.5">Catatan Khusus / Permintaan Tamu</label>
                    <textarea id="notes" name="notes" rows="2" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-amber-500" placeholder="Contoh: Kamar bebas asap rokok, checkin malam, extra towel, dll.">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="pt-6 border-t border-slate-100 flex justify-end">
                <button type="submit" class="px-6 py-3 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl shadow-md transition-colors uppercase tracking-wider text-sm">
                    Konfirmasi Booking
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
