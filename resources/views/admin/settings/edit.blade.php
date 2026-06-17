@extends('layouts.admin')

@section('header-title', 'Hotel Settings')

@section('content')
<div class="max-w-2xl mx-auto bg-white p-8 rounded-2xl border border-slate-100 shadow-sm">
    <div class="border-b border-slate-100 pb-4 mb-8">
        <h3 class="text-base font-bold text-slate-800">Pengaturan Umum Hotel Kejora</h3>
        <p class="text-xs text-slate-400 mt-1">Konfigurasi dasar aplikasi hotel, tarif pajak, biaya service, dan penamaan booking/invoice.</p>
    </div>

    <!-- Validation Errors -->
    @if ($errors->any())
        <div class="mb-6 p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-r-lg text-sm">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Hotel Name -->
            <div class="md:col-span-2">
                <label for="name" class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Hotel</label>
                <input type="text" id="name" name="name" value="{{ old('name', $settings->name) }}" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-amber-500" />
            </div>

            <!-- Phone -->
            <div>
                <label for="phone" class="block text-sm font-semibold text-slate-700 mb-1.5">Nomor Telepon</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone', $settings->phone) }}" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-amber-500" />
            </div>

            <!-- Breakfast Threshold -->
            <div>
                <label for="breakfast_threshold" class="block text-sm font-semibold text-slate-700 mb-1.5">Breakfast Limit (Rp)</label>
                <input type="number" id="breakfast_threshold" name="breakfast_threshold" value="{{ old('breakfast_threshold', (int) $settings->breakfast_threshold) }}" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-amber-500" title="Batas minimum tarif kamar mendapatkan sarapan gratis" />
            </div>

            <!-- Tax rate -->
            <div>
                <label for="tax_rate" class="block text-sm font-semibold text-slate-700 mb-1.5">Pajak PPN (%)</label>
                <input type="number" step="0.01" id="tax_rate" name="tax_rate" value="{{ old('tax_rate', $settings->tax_rate) }}" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-amber-500" />
            </div>

            <!-- Service charge rate -->
            <div>
                <label for="service_charge_rate" class="block text-sm font-semibold text-slate-700 mb-1.5">Service Charge (%)</label>
                <input type="number" step="0.01" id="service_charge_rate" name="service_charge_rate" value="{{ old('service_charge_rate', $settings->service_charge_rate) }}" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-amber-500" />
            </div>

            <!-- Booking Prefix -->
            <div>
                <label for="booking_prefix" class="block text-sm font-semibold text-slate-700 mb-1.5">Prefix Booking Code</label>
                <input type="text" id="booking_prefix" name="booking_prefix" value="{{ old('booking_prefix', $settings->booking_prefix) }}" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-amber-500" />
            </div>

            <!-- Invoice Prefix -->
            <div>
                <label for="invoice_prefix" class="block text-sm font-semibold text-slate-700 mb-1.5">Prefix Invoice Code</label>
                <input type="text" id="invoice_prefix" name="invoice_prefix" value="{{ old('invoice_prefix', $settings->invoice_prefix) }}" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-amber-500" />
            </div>

            <!-- Address -->
            <div class="md:col-span-2">
                <label for="address" class="block text-sm font-semibold text-slate-700 mb-1.5">Alamat Lengkap Hotel</label>
                <textarea id="address" name="address" rows="3" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-amber-500">{{ old('address', $settings->address) }}</textarea>
            </div>
        </div>

        <div class="pt-6 border-t border-slate-100 flex justify-end">
            <button type="submit" class="px-6 py-3 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl shadow-md transition-colors uppercase tracking-wider text-sm">
                Simpan Pengaturan
            </button>
        </div>
    </form>
</div>
@endsection
