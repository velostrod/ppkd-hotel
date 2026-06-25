@extends('layouts.fo')

@section('header-title', 'Buat Reservasi Baru')

@section('content')
<div class="space-y-8 max-w-4xl mx-auto">
    <!-- 1. Check Date Range Availability (GET Form) -->
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
        <h3 class="text-base font-bold text-slate-800 mb-4">Cek Ketersediaan Kamar</h3>
        <form method="GET" action="{{ route('reservations.create') }}" class="grid grid-cols-1 md:grid-cols-2 gap-6 items-end">
            <div>
                <label for="checkin_date" class="block text-sm font-semibold text-slate-700 mb-1.5">Tanggal Check-In</label>
                <input type="date" id="checkin_date" name="checkin_date" value="{{ request('checkin_date', $checkin) }}" required min="{{ now()->format('Y-m-d') }}" onchange="this.form.submit()" class="w-full px-4 py-2 border border-slate-200 rounded-xl text-sm focus:ring-amber-500" />
            </div>

            <div>
                <label for="checkout_date" class="block text-sm font-semibold text-slate-700 mb-1.5">Tanggal Check-Out</label>
                <input type="date" id="checkout_date" name="checkout_date" value="{{ request('checkout_date', $checkout) }}" required min="{{ now()->addDay()->format('Y-m-d') }}" onchange="this.form.submit()" class="w-full px-4 py-2 border border-slate-200 rounded-xl text-sm focus:ring-amber-500" />
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
            <input type="hidden" id="hidden_checkin_date" name="checkin_date" value="{{ request('checkin_date', $checkin) }}" />
            <input type="hidden" id="hidden_checkout_date" name="checkout_date" value="{{ request('checkout_date', $checkout) }}" />

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
                        <button type="button" id="btnAddGuest"
                            class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-bold border border-slate-200 transition-colors"
                            title="Daftarkan Tamu Baru">
                            + Baru
                        </button>
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

            <!-- Room Features Panel (shown after room is selected) -->
            <div id="room_features_panel" class="hidden bg-blue-50 p-6 rounded-2xl border border-blue-100">
                <h4 class="text-sm font-bold text-blue-800 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Fitur Kamar
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="room_features_list"></div>
                <p id="room_features_desc" class="text-xs text-slate-500 mt-3 hidden"></p>
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
                <div>
                    <label for="discount" class="block text-sm font-semibold text-slate-700 mb-1.5">Nominal Diskon (Rp)</label>
                    <input type="number" id="discount" name="discount" value="{{ old('discount', 0) }}" min="0" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-amber-500" />
                </div>

                <div>
                    <label for="notes" class="block text-sm font-semibold text-slate-700 mb-1.5">Catatan Khusus / Permintaan Tamu</label>
                    <textarea id="notes" name="notes" rows="2" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-amber-500" placeholder="Contoh: Kamar bebas asap rokok, checkin malam, extra towel, dll.">{{ old('notes') }}</textarea>
                </div>
            </div>

            <!-- Informasi Pembayaran -->
            <div class="rounded-2xl overflow-hidden border border-amber-200 shadow-sm">
                <!-- Header band -->
                <div class="px-6 py-4 bg-gradient-to-r from-amber-500 to-orange-400 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-white leading-tight">Informasi Pembayaran Awal</h4>
                        <p class="text-xs text-orange-100 leading-tight">Wajib dilakukan untuk mengkonfirmasi reservasi</p>
                    </div>
                </div>

                <!-- Body -->
                <div class="p-6 bg-amber-50/60 grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="payment_type" class="block text-xs font-bold text-amber-800 uppercase tracking-wide mb-1.5">Tipe Pembayaran <span class="text-rose-500">*</span></label>
                        <select id="payment_type" name="payment_type" required
                            class="w-full px-4 py-2.5 bg-white border border-amber-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition">
                            <option value="full" {{ old('payment_type', 'full') == 'full' ? 'selected' : '' }}>Bayar di Awal (Lunas 100%)</option>
                            <option value="deposit" {{ old('payment_type') == 'deposit' ? 'selected' : '' }}>Downpayment</option>
                        </select>
                    </div>

                    <div>
                        <label for="payment_method_id" class="block text-xs font-bold text-amber-800 uppercase tracking-wide mb-1.5">Metode Pembayaran <span class="text-rose-500">*</span></label>
                        <select id="payment_method_id" name="payment_method_id" required
                            class="w-full px-4 py-2.5 bg-white border border-amber-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition">
                            <option value="">-- Pilih Metode --</option>
                            @foreach($paymentMethods as $method)
                                <option value="{{ $method->id }}" {{ old('payment_method_id') == $method->id ? 'selected' : '' }}>
                                    {{ $method->name }}
                                    @if($method->account_name || $method->account_number)
                                        — {{ $method->account_name }}
                                        @if($method->account_number)
                                            ({{ $method->account_number }})
                                        @endif
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Nominal Deposit — hanya tampil saat tipe = deposit -->
                    <div id="deposit_amount_wrapper" class="md:col-span-2 hidden">
                        <label for="deposit_amount" class="block text-xs font-bold text-amber-800 uppercase tracking-wide mb-1.5">Nominal Deposit <span class="text-rose-500">*</span></label>
                        <input type="number" id="deposit_amount" name="deposit_amount"
                            value="{{ old('deposit_amount') }}"
                            min="1" step="1000" placeholder="Contoh: 500000"
                            class="w-full px-4 py-2.5 bg-white border border-amber-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition" />
                        <p class="text-xs text-amber-700 mt-1.5">Sisa tagihan akan diselesaikan saat check-in.</p>
                    </div>
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

<!-- Modal Tamu Baru -->
<div id="modalAddGuest" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <!-- Backdrop -->
    <div id="modalBackdrop" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

    <!-- Panel -->
    <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden">
        <!-- Modal header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-800">Daftarkan Tamu Baru</h3>
            <button type="button" id="btnCloseModal" class="text-slate-400 hover:text-slate-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Error area -->
        <div id="guestModalErrors" class="hidden mx-6 mt-4 p-3 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-r-lg text-sm"></div>

        <!-- Form -->
        <form id="formAddGuest" class="p-6 space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="full_name" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-400" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1">No. KTP / Paspor <span class="text-rose-500">*</span></label>
                    <input type="text" name="id_number" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-400" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1">Nomor Telepon <span class="text-rose-500">*</span></label>
                    <input type="text" name="phone" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-400" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1">Kewarganegaraan <span class="text-rose-500">*</span></label>
                    <input type="text" name="nationality" value="Indonesia" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-400" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1">Jenis Kelamin <span class="text-rose-500">*</span></label>
                    <select name="gender" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                        <option value="male">Laki-laki</option>
                        <option value="female">Perempuan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1">Email</label>
                    <input type="email" name="email" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-400" />
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1">Alamat</label>
                    <textarea name="address" rows="2" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"></textarea>
                </div>
            </div>

            <div class="pt-2 flex justify-end gap-3">
                <button type="button" id="btnCancelModal" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Batal</button>
                <button type="submit" id="btnSaveGuest" class="px-5 py-2 text-sm font-bold text-white bg-amber-500 hover:bg-amber-400 rounded-xl shadow-sm transition-colors">
                    Simpan Tamu
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const ROOM_DATA = {
    @foreach($rooms as $room)
    @if($room->is_available_in_range)
    "{{ $room->id }}": {
        number: "{{ $room->room_number }}",
        type: "{{ $room->roomType->name }}",
        price: {{ $room->roomType->base_price }},
        capacity: {{ $room->roomType->capacity }},
        breakfast_included: {{ $room->roomType->breakfast_included ? 'true' : 'false' }},
        breakfast_price: {{ $room->roomType->breakfast_price ?? 0 }},
        extra_bed_allowed: {{ $room->roomType->extra_bed_allowed ? 'true' : 'false' }},
        extra_bed_price: {{ $room->roomType->extra_bed_price ?? 0 }},
        description: @json($room->roomType->description ?? ''),
    },
    @endif
    @endforeach
};

document.addEventListener('DOMContentLoaded', function() {
    // ── Room features panel ───────────────────────────────────
    const roomSelect      = document.getElementById('room_id');
    const featuresPanel   = document.getElementById('room_features_panel');
    const featuresList    = document.getElementById('room_features_list');
    const featuresDesc    = document.getElementById('room_features_desc');

    function formatRupiah(n) {
        return 'Rp ' + Number(n).toLocaleString('id-ID');
    }

    function featureItem(icon, label, highlight) {
        return `<div class="flex items-center gap-2 text-sm ${highlight ? 'text-blue-700 font-semibold' : 'text-slate-600'}">
            <span class="text-base leading-none">${icon}</span>
            <span>${label}</span>
        </div>`;
    }

    function updateRoomFeatures() {
        const id = roomSelect.value;
        const room = ROOM_DATA[id];
        if (!room) {
            featuresPanel.classList.add('hidden');
            return;
        }

        const items = [];
        items.push(featureItem('🏨', `Tipe: ${room.type}`, false));
        items.push(featureItem('👥', `Kapasitas: ${room.capacity} Orang`, false));
        items.push(featureItem('💰', `Harga: ${formatRupiah(room.price)} / malam`, false));

        if (room.breakfast_included) {
            items.push(featureItem('☕', 'Sarapan sudah termasuk', true));
        } else if (room.breakfast_price > 0) {
            items.push(featureItem('☕', `Sarapan tersedia (+${formatRupiah(room.breakfast_price)}/orang)`, false));
        }

        if (room.extra_bed_allowed) {
            const label = room.extra_bed_price > 0
                ? `Extra Bed tersedia (+${formatRupiah(room.extra_bed_price)}/malam)`
                : 'Extra Bed tersedia';
            items.push(featureItem('🛏️', label, false));
        }

        featuresList.innerHTML = items.join('');

        if (room.description) {
            featuresDesc.textContent = room.description;
            featuresDesc.classList.remove('hidden');
        } else {
            featuresDesc.classList.add('hidden');
        }

        featuresPanel.classList.remove('hidden');
    }

    roomSelect.addEventListener('change', updateRoomFeatures);
    updateRoomFeatures(); // run on load in case of old() value

    // ── Deposit toggle ────────────────────────────────────────
    const paymentTypeSelect = document.getElementById('payment_type');
    const depositWrapper    = document.getElementById('deposit_amount_wrapper');
    const depositSelect     = document.getElementById('deposit_amount');

    function toggleDepositField() {
        const isDeposit = paymentTypeSelect.value === 'deposit';
        depositWrapper.classList.toggle('hidden', !isDeposit);
        depositSelect.required = isDeposit;
        if (!isDeposit) depositSelect.value = '';
    }
    paymentTypeSelect.addEventListener('change', toggleDepositField);
    toggleDepositField();

    // ── Date sync ─────────────────────────────────────────────
    const checkinInput   = document.getElementById('checkin_date');
    const checkoutInput  = document.getElementById('checkout_date');
    const hiddenCheckin  = document.getElementById('hidden_checkin_date');
    const hiddenCheckout = document.getElementById('hidden_checkout_date');

    function syncDates() {
        if (hiddenCheckin)  hiddenCheckin.value  = checkinInput.value;
        if (hiddenCheckout) hiddenCheckout.value = checkoutInput.value;
    }

    function updateCheckoutMin() {
        if (checkinInput.value) {
            const d = new Date(checkinInput.value);
            d.setDate(d.getDate() + 1);
            const min = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
            checkoutInput.min = min;
            if (checkoutInput.value && checkoutInput.value <= checkinInput.value) checkoutInput.value = min;
        }
        syncDates();
    }

    if (checkinInput && checkoutInput) {
        updateCheckoutMin();
        checkinInput.addEventListener('change', updateCheckoutMin);
        checkoutInput.addEventListener('change', syncDates);
    }

    // Clean query params from URL bar after page renders
    if (window.location.search) {
        history.replaceState(null, '', window.location.pathname);
    }

    // ── Modal: daftarkan tamu baru ────────────────────────────
    const modal        = document.getElementById('modalAddGuest');
    const backdrop     = document.getElementById('modalBackdrop');
    const btnOpen      = document.getElementById('btnAddGuest');
    const btnClose     = document.getElementById('btnCloseModal');
    const btnCancel    = document.getElementById('btnCancelModal');
    const formGuest    = document.getElementById('formAddGuest');
    const errorBox     = document.getElementById('guestModalErrors');
    const guestSelect  = document.getElementById('guest_id');
    const btnSave      = document.getElementById('btnSaveGuest');

    function openModal()  { modal.classList.remove('hidden'); modal.classList.add('flex'); }
    function closeModal() { modal.classList.add('hidden');    modal.classList.remove('flex'); errorBox.classList.add('hidden'); formGuest.reset(); }

    btnOpen.addEventListener('click', openModal);
    btnClose.addEventListener('click', closeModal);
    btnCancel.addEventListener('click', closeModal);
    backdrop.addEventListener('click', closeModal);

    formGuest.addEventListener('submit', function(e) {
        e.preventDefault();
        btnSave.disabled = true;
        btnSave.textContent = 'Menyimpan...';
        errorBox.classList.add('hidden');

        const data = new FormData(formGuest);

        fetch('{{ route('guests.store') }}', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: data,
        })
        .then(res => res.json())
        .then(json => {
            if (json.success) {
                // Append to select & auto-select
                const opt = new Option(
                    `${json.guest.full_name} (${json.guest.id_number})`,
                    json.guest.id,
                    true, true
                );
                guestSelect.appendChild(opt);
                closeModal();
            } else {
                // Validation errors
                const msgs = Object.values(json.errors ?? {}).flat();
                errorBox.innerHTML = '<ul class="list-disc pl-4 space-y-0.5">' + msgs.map(m => `<li>${m}</li>`).join('') + '</ul>';
                errorBox.classList.remove('hidden');
            }
        })
        .catch(() => {
            errorBox.textContent = 'Terjadi kesalahan. Coba lagi.';
            errorBox.classList.remove('hidden');
        })
        .finally(() => {
            btnSave.disabled = false;
            btnSave.textContent = 'Simpan Tamu';
        });
    });
});
</script>
@endsection
