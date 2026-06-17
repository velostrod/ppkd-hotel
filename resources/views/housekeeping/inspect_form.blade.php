@extends('layouts.hk')

@section('header-title', 'Formulir Inspeksi Kamar')

@section('content')
<div class="max-w-4xl mx-auto bg-white p-8 rounded-2xl border border-slate-100 shadow-sm space-y-8">
    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
        <div>
            <h3 class="text-base font-bold text-slate-800">Inspeksi Kamar #{{ $inspection->room->room_number }}</h3>
            <p class="text-xs text-slate-400 mt-1 uppercase tracking-widest">Reservasi: {{ $inspection->reservation->reservation_code }} | Tamu: {{ $inspection->reservation->guest->full_name }}</p>
        </div>
        <a href="{{ route('housekeeping.dashboard') }}" class="text-slate-400 hover:text-slate-600 text-sm flex items-center font-medium">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </div>

    <!-- Validation Errors -->
    @if ($errors->any())
        <div class="p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-r-lg text-sm mb-6">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('housekeeping.inspect', $inspection->id) }}" class="space-y-8">
        @csrf

        <!-- 1. General Condition & Damage -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label for="room_condition" class="block text-sm font-semibold text-slate-700 mb-1.5">Kondisi Umum Kamar</label>
                <select id="room_condition" name="room_condition" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-amber-500">
                    <option value="good" {{ old('room_condition') === 'good' ? 'selected' : '' }}>Good (Baik / Layak)</option>
                    <option value="needs_cleaning" {{ old('room_condition') === 'needs_cleaning' ? 'selected' : '' }}>Needs Cleaning (Kotor)</option>
                    <option value="damaged" {{ old('room_condition') === 'damaged' ? 'selected' : '' }}>Damaged (Ada Kerusakan)</option>
                </select>
            </div>

            <div>
                <label for="damage_found" class="block text-sm font-semibold text-slate-700 mb-1.5">Ada Kerusakan/Kehilangan?</label>
                <select id="damage_found" name="damage_found" required onchange="toggleDamageCost(this.value)" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-amber-500">
                    <option value="0" {{ old('damage_found') === '0' ? 'selected' : '' }}>Tidak Ada</option>
                    <option value="1" {{ old('damage_found') === '1' ? 'selected' : '' }}>Ya, Ada</option>
                </select>
            </div>

            <div id="damage-cost-container" style="display: none;">
                <label for="damage_cost" class="block text-sm font-semibold text-slate-700 mb-1.5">Estimasi Biaya Kerusakan (Rp)</label>
                <input type="number" id="damage_cost" name="damage_cost" value="{{ old('damage_cost', 0) }}" min="0" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-amber-500" />
            </div>
        </div>

        <!-- 2. Checklist items -->
        <div class="space-y-4">
            <h4 class="text-sm font-bold text-slate-800 border-b border-slate-50 pb-2">Checklist Inventaris Kamar</h4>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="text-slate-400 font-semibold border-b border-slate-100">
                            <th class="pb-2">Nama Barang</th>
                            <th class="pb-2">Kondisi</th>
                            <th class="pb-2">Biaya Kerusakan/Kehilangan (Rp)</th>
                            <th class="pb-2">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 text-slate-700">
                        @php
                            $checklistItems = ['Televisi & Remote', 'AC & Remote', 'Tempat Tidur & Linen', 'Kamar Mandi & Shower', 'Handuk Mandi', 'Mini Bar & Gelas', 'Kunci / Card Kamar'];
                        @endphp
                        @foreach($checklistItems as $idx => $item)
                            <tr>
                                <td class="py-3 font-semibold">
                                    {{ $item }}
                                    <input type="hidden" name="items[{{ $idx }}][item_name]" value="{{ $item }}" />
                                </td>
                                <td class="py-3">
                                    <select name="items[{{ $idx }}][condition]" onchange="updateItemCost(this, {{ $idx }})" class="px-2 py-1.5 border border-slate-200 rounded-lg text-xs focus:ring-amber-500 bg-white">
                                        <option value="good">Good</option>
                                        <option value="damaged">Damaged</option>
                                        <option value="missing">Missing</option>
                                    </select>
                                </td>
                                <td class="py-3">
                                    <input type="number" name="items[{{ $idx }}][charge_amount]" value="0" min="0" oninput="calculateTotalDamage()" class="w-32 px-2 py-1.5 border border-slate-200 rounded-lg text-xs focus:ring-amber-500 item-charge-input" data-index="{{ $idx }}" />
                                </td>
                                <td class="py-3">
                                    <input type="text" name="items[{{ $idx }}][notes]" class="w-full px-2 py-1.5 border border-slate-200 rounded-lg text-xs focus:ring-amber-500" placeholder="Catatan tambahan" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 3. Notes -->
        <div>
            <label for="notes" class="block text-sm font-semibold text-slate-700 mb-1.5">Catatan Kesimpulan Inspeksi</label>
            <textarea id="notes" name="notes" rows="3" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-amber-500" placeholder="Jelaskan detail temuan jika ada barang rusak atau hilang..."></textarea>
        </div>

        <div class="pt-6 border-t border-slate-100 flex justify-end">
            <button type="submit" class="px-6 py-3 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl shadow-md transition-colors uppercase tracking-wider text-sm">
                Simpan & Kirim Hasil Inspeksi
            </button>
        </div>
    </form>
</div>

<script>
    function toggleDamageCost(val) {
        const container = document.getElementById('damage-cost-container');
        if (val === '1') {
            container.style.display = 'block';
        } else {
            container.style.display = 'none';
            document.getElementById('damage_cost').value = 0;
        }
    }

    function updateItemCost(select, index) {
        const chargeInput = document.querySelector(`input[name="items[${index}][charge_amount]"]`);
        if (select.value === 'good') {
            chargeInput.value = 0;
        } else if (select.value === 'damaged') {
            chargeInput.value = 50000; // Default estimate
        } else if (select.value === 'missing') {
            chargeInput.value = 100000; // Default estimate
        }
        calculateTotalDamage();
    }

    function calculateTotalDamage() {
        let total = 0;
        const inputs = document.querySelectorAll('.item-charge-input');
        inputs.forEach(input => {
            total += parseFloat(input.value) || 0;
        });

        if (total > 0) {
            document.getElementById('damage_found').value = '1';
            toggleDamageCost('1');
            document.getElementById('damage_cost').value = total;
        } else {
            document.getElementById('damage_found').value = '0';
            toggleDamageCost('0');
        }
    }
</script>
@endsection
