@extends('layouts.fo')

@section('header-title', 'Pemesanan Food & Beverage')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 max-w-7xl mx-auto">
    <!-- Menu Catalog (Left 7 Cols) -->
    <div class="lg:col-span-7 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-6">
        <h3 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-3 font-semibold">Katalog Menu Makanan & Minuman</h3>

        <!-- Tab Categories -->
        <div class="space-y-8">
            @foreach($foodItems->groupBy('foodCategory.name') as $category => $items)
                <div>
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">{{ $category }}</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($items as $item)
                            <div class="border border-slate-100 p-4 rounded-xl flex justify-between items-start hover:border-amber-200 transition-colors bg-slate-50/50">
                                <div>
                                    <span class="font-bold text-slate-800 text-sm block">{{ $item->name }}</span>
                                    <span class="text-xs text-slate-400 block mt-1 leading-snug">{{ $item->description }}</span>
                                    <span class="font-bold text-amber-600 text-sm block mt-2">Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                                </div>
                                <button type="button"
                                        onclick="addToCart({{ $item->id }}, '{{ $item->name }}', {{ $item->price }})"
                                        class="px-3 py-1 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-lg text-xs transition-transform hover:scale-105 uppercase">
                                    Tambah
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Order Summary & Details (Right 5 Cols) -->
    <div class="lg:col-span-5 space-y-6">
        <!-- Input Form -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-6">
            <h3 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-3">Keranjang Belanja Tamu</h3>

            <form method="POST" action="{{ route('services.fnb.store') }}" class="space-y-6" id="fnb-order-form">
                @csrf

                <div>
                    <label for="reservation_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Kamar Tamu Aktif</label>
                    <select id="reservation_id" name="reservation_id" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-amber-500">
                        <option value="">-- Pilih Kamar --</option>
                        @foreach($activeReservations as $res)
                            <option value="{{ $res->id }}" {{ old('reservation_id') == $res->id ? 'selected' : '' }}>
                                Kamar #{{ $res->room->room_number }} - {{ $res->guest->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Cart List Container -->
                <div class="border-t border-b border-slate-100 py-4">
                    <p class="text-xs text-slate-400 font-semibold uppercase mb-3">Pesanan Terpilih</p>
                    <div id="cart-items" class="space-y-3 max-h-60 overflow-y-auto pr-2">
                        <p class="text-sm text-slate-400 italic text-center py-4" id="empty-cart-text">Belum ada item ditambahkan.</p>
                    </div>

                    <div class="flex justify-between items-center mt-4 pt-3 border-t border-slate-100">
                        <span class="text-sm font-bold text-slate-700">Total Harga:</span>
                        <span class="text-base font-bold text-amber-600" id="cart-total-display">Rp 0</span>
                    </div>
                </div>

                <div>
                    <label for="notes" class="block text-sm font-semibold text-slate-700 mb-1.5">Catatan Tambahan (Khusus)</label>
                    <textarea id="notes" name="notes" rows="2" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-amber-500" placeholder="Contoh: Sambal dipisah, jus tanpa es batu, dll."></textarea>
                </div>

                <button type="submit" class="w-full py-3 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl shadow-md transition-colors uppercase tracking-wider text-xs">
                    Kirim Pesanan ke Dapur
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    let cart = {};

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(String(str)));
        return div.innerHTML;
    }

    function addToCart(id, name, price) {
        if (cart[id]) {
            cart[id].qty += 1;
        } else {
            cart[id] = { id: id, name: name, price: price, qty: 1, notes: '' };
        }

        renderCart();
    }

    function updateQty(id, change) {
        if (cart[id]) {
            cart[id].qty += change;
            if (cart[id].qty <= 0) {
                delete cart[id];
            }
        }
        renderCart();
    }

    function updateItemNotes(id, text) {
        if (cart[id]) {
            cart[id].notes = text;
        }
    }

    function renderCart() {
        const container = document.getElementById('cart-items');
        container.innerHTML = '';

        let total = 0;
        let index = 0;

        const keys = Object.keys(cart);
        if (keys.length === 0) {
            container.innerHTML = '<p class="text-sm text-slate-400 italic text-center py-4" id="empty-cart-text">Belum ada item ditambahkan.</p>';
            document.getElementById('cart-total-display').innerText = 'Rp 0';
            return;
        }

        keys.forEach(key => {
            const item = cart[key];
            const subtotal = item.price * item.qty;
            total += subtotal;

            const div = document.createElement('div');
            div.className = 'flex flex-col border border-slate-100 p-3 rounded-lg bg-slate-50 relative';
            div.innerHTML = `
                <div class="flex justify-between items-start">
                    <span class="text-sm font-semibold text-slate-800">${escapeHtml(item.name)}</span>
                    <span class="text-sm font-bold text-slate-700">Rp ${formatIDR(subtotal)}</span>
                </div>
                <div class="flex items-center justify-between mt-2 gap-2">
                    <input type="text"
                           placeholder="Catatan item (pedas, dll)"
                           name="items[${index}][notes]"
                           value="${escapeHtml(item.notes)}"
                           oninput="updateItemNotes(${item.id}, this.value)"
                           class="flex-1 min-w-0 px-2 py-1 border border-slate-200 rounded text-[11px] focus:outline-none bg-white" />
                    <div class="flex items-center space-x-2 shrink-0">
                        <button type="button" onclick="updateQty(${item.id}, -1)" class="w-6 h-6 flex items-center justify-center bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded text-xs">-</button>
                    <span class="text-sm font-semibold w-4 text-center">${escapeHtml(item.qty)}</span>
                        <button type="button" onclick="updateQty(${item.id}, 1)" class="w-6 h-6 flex items-center justify-center bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded text-xs">+</button>
                    </div>
                </div>

                <!-- Hidden fields for the form -->
                <input type="hidden" name="items[${index}][food_item_id]" value="${escapeHtml(item.id)}" />
                <input type="hidden" name="items[${index}][qty]" value="${escapeHtml(item.qty)}" />
            `;
            container.appendChild(div);
            index++;
        });

        document.getElementById('cart-total-display').innerText = 'Rp ' + formatIDR(total);
    }

    function formatIDR(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
</script>
@endsection
