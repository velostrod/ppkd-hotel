@extends('layouts.admin')

@section('header-title', 'Tipe Kamar (Pricing)')

@section('content')
<div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between border-b border-slate-100 pb-4 gap-3">
        <h3 class="text-base font-bold text-slate-800">Manajemen Kategori & Tarif Kamar</h3>
        @if(auth()->user()->isAdmin() || auth()->user()->isManager())
        <button onclick="openAddModal()" class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 rounded-xl text-sm font-bold shadow-sm uppercase tracking-wider transition-all w-full sm:w-auto text-center">
            + Tambah Tipe Kamar
        </button>
        @endif
    </div>

    <!-- Mobile Cards -->
    <div class="block md:hidden space-y-3">
        @foreach($roomTypes as $type)
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 space-y-2">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="font-bold text-slate-800">{{ $type->name }}</span>
                        @if($type->description)
                            <span class="block text-xs text-slate-400 font-normal mt-0.5">{{ $type->description }}</span>
                        @endif
                    </div>
                    <span class="px-2 py-0.5 rounded text-xs font-bold uppercase {{ $type->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                        {{ $type->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div>
                        <span class="text-slate-400">Kapasitas</span>
                        <p class="font-semibold">{{ $type->capacity }} Orang</p>
                    </div>
                    <div>
                        <span class="text-slate-400">Harga Dasar</span>
                        <p class="font-bold text-slate-800">Rp {{ number_format($type->base_price, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <span class="text-slate-400">Breakfast</span>
                        @if($type->breakfast_included)
                            <p class="text-emerald-700 font-semibold">Included</p>
                        @else
                            <p class="font-semibold">Rp {{ number_format($type->breakfast_price, 0, ',', '.') }}</p>
                        @endif
                    </div>
                    <div>
                        <span class="text-slate-400">Extra Bed</span>
                        @if($type->extra_bed_allowed)
                            <p class="font-semibold">Rp {{ number_format($type->extra_bed_price, 0, ',', '.') }}</p>
                        @else
                            <p class="text-slate-400">Not Allowed</p>
                        @endif
                    </div>
                </div>
                @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                <div class="pt-2 border-t border-slate-100 text-right">
                    <button onclick="openEditModal({{ json_encode($type) }})" class="text-amber-500 hover:underline text-xs font-bold">Edit</button>
                </div>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Desktop Table -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left text-sm border-collapse">
            <thead>
                <tr class="text-slate-400 font-semibold border-b border-slate-100">
                    <th class="py-3 px-4">Nama Tipe</th>
                    <th class="py-3 px-4">Kapasitas (Pax)</th>
                    <th class="py-3 px-4">Harga Dasar / Malam</th>
                    <th class="py-3 px-4">Breakfast Policy</th>
                    <th class="py-3 px-4">Extra Bed Policy</th>
                    <th class="py-3 px-4">Status</th>
                    @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                    <th class="py-3 px-4 text-right">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 text-slate-700">
                @foreach($roomTypes as $type)
                    <tr>
                        <td class="py-4 px-4 font-bold">
                            {{ $type->name }}
                            <span class="block text-xs text-slate-400 font-normal mt-0.5 max-w-xs truncate" title="{{ $type->description }}">{{ $type->description }}</span>
                        </td>
                        <td class="py-4 px-4 font-semibold">{{ $type->capacity }} Orang</td>
                        <td class="py-4 px-4 font-bold text-slate-800">Rp {{ number_format($type->base_price, 0, ',', '.') }}</td>
                        <td class="py-4 px-4">
                            @if($type->breakfast_included)
                                <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded">Included</span>
                            @else
                                <span class="text-xs text-slate-500 block">Addon: Rp {{ number_format($type->breakfast_price, 0, ',', '.') }}</span>
                            @endif
                        </td>
                        <td class="py-4 px-4">
                            @if($type->extra_bed_allowed)
                                <span class="text-xs text-slate-700 block font-semibold">Allowed: Rp {{ number_format($type->extra_bed_price, 0, ',', '.') }}</span>
                            @else
                                <span class="px-2 py-0.5 bg-slate-100 text-slate-400 text-xs rounded">Not Allowed</span>
                            @endif
                        </td>
                        <td class="py-4 px-4">
                            <span class="px-2 py-0.5 rounded text-xs font-bold uppercase {{ $type->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                {{ $type->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        @if(auth()->user()->isAdmin())
                        <td class="py-4 px-4 text-right">
                            <button onclick="openEditModal({{ json_encode($type) }})" class="text-amber-500 hover:underline text-xs font-bold">Edit</button>
                        </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- ==========================================
     ADD MODAL
     ========================================== -->
<div id="add-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 overflow-y-auto max-h-[90vh]">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-6">
            <h3 class="text-sm font-bold text-slate-800 uppercase">Tambah Tipe Kamar Baru</h3>
            <button onclick="closeAddModal()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        
        <form method="POST" action="{{ route('admin.room-types.store') }}" class="space-y-4">
            @csrf
            <div>
                <label for="name" class="block text-xs font-bold text-slate-500 uppercase mb-1">Nama Tipe Kamar</label>
                <input type="text" id="name" name="name" required class="w-full px-3.5 py-2 border border-slate-200 rounded-lg text-sm focus:ring-amber-500" placeholder="Misal: Standard Room" />
            </div>

            <div>
                <label for="description" class="block text-xs font-bold text-slate-500 uppercase mb-1">Deskripsi</label>
                <textarea id="description" name="description" rows="2" class="w-full px-3.5 py-2 border border-slate-200 rounded-lg text-sm focus:ring-amber-500" placeholder="Fasilitas dan rincian..."></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="base_price" class="block text-xs font-bold text-slate-500 uppercase mb-1">Harga Dasar / Malam</label>
                    <input type="number" id="base_price" name="base_price" required class="w-full px-3.5 py-2 border border-slate-200 rounded-lg text-sm focus:ring-amber-500" />
                </div>
                <div>
                    <label for="capacity" class="block text-xs font-bold text-slate-500 uppercase mb-1">Kapasitas (Orang)</label>
                    <input type="number" id="capacity" name="capacity" required class="w-full px-3.5 py-2 border border-slate-200 rounded-lg text-sm focus:ring-amber-500" />
                </div>
            </div>

            <div class="bg-slate-50 p-4 rounded-xl space-y-4 border border-slate-100">
                <span class="text-xs font-bold text-slate-700 block border-b border-slate-100 pb-1 uppercase tracking-wider">Breakfast & Extra Bed</span>
                
                <div class="grid grid-cols-2 gap-4">
                    <label class="flex items-center cursor-pointer select-none">
                        <input type="checkbox" name="breakfast_included" value="1" class="rounded border-slate-300 text-amber-500 w-4 h-4 mr-2" />
                        <span class="text-xs font-semibold text-slate-700">Breakfast Included</span>
                    </label>
                    <div>
                        <label for="breakfast_price" class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Harga Breakfast Addon</label>
                        <input type="number" id="breakfast_price" name="breakfast_price" value="0" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <label class="flex items-center cursor-pointer select-none">
                        <input type="checkbox" name="extra_bed_allowed" value="1" checked class="rounded border-slate-300 text-amber-500 w-4 h-4 mr-2" />
                        <span class="text-xs font-semibold text-slate-700">Extra Bed Allowed</span>
                    </label>
                    <div>
                        <label for="extra_bed_price" class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Harga Extra Bed</label>
                        <input type="number" id="extra_bed_price" name="extra_bed_price" value="0" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs" />
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs rounded-lg uppercase tracking-wider">
                    Simpan Tipe Kamar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==========================================
     EDIT MODAL
     ========================================== -->
<div id="edit-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 overflow-y-auto max-h-[90vh]">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-6">
            <h3 class="text-sm font-bold text-slate-800 uppercase" id="edit-modal-title">Edit Tipe Kamar</h3>
            <button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        
        <form method="POST" id="edit-form" class="space-y-4">
            @csrf
            @method('PUT')
            
            <div>
                <label for="edit_name" class="block text-xs font-bold text-slate-500 uppercase mb-1">Nama Tipe Kamar</label>
                <input type="text" id="edit_name" name="name" required class="w-full px-3.5 py-2 border border-slate-200 rounded-lg text-sm focus:ring-amber-500" />
            </div>

            <div>
                <label for="edit_description" class="block text-xs font-bold text-slate-500 uppercase mb-1">Deskripsi</label>
                <textarea id="edit_description" name="description" rows="2" class="w-full px-3.5 py-2 border border-slate-200 rounded-lg text-sm focus:ring-amber-500"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="edit_base_price" class="block text-xs font-bold text-slate-500 uppercase mb-1">Harga Dasar / Malam</label>
                    <input type="number" id="edit_base_price" name="base_price" required class="w-full px-3.5 py-2 border border-slate-200 rounded-lg text-sm focus:ring-amber-500" />
                </div>
                <div>
                    <label for="edit_capacity" class="block text-xs font-bold text-slate-500 uppercase mb-1">Kapasitas (Orang)</label>
                    <input type="number" id="edit_capacity" name="capacity" required class="w-full px-3.5 py-2 border border-slate-200 rounded-lg text-sm focus:ring-amber-500" />
                </div>
            </div>

            <div class="bg-slate-50 p-4 rounded-xl space-y-4 border border-slate-100">
                <span class="text-xs font-bold text-slate-700 block border-b border-slate-100 pb-1 uppercase tracking-wider">Breakfast & Extra Bed</span>
                
                <div class="grid grid-cols-2 gap-4">
                    <label class="flex items-center cursor-pointer select-none">
                        <input type="checkbox" id="edit_breakfast_included" name="breakfast_included" value="1" class="rounded border-slate-300 text-amber-500 w-4 h-4 mr-2" />
                        <span class="text-xs font-semibold text-slate-700">Breakfast Included</span>
                    </label>
                    <div>
                        <label for="edit_breakfast_price" class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Harga Breakfast Addon</label>
                        <input type="number" id="edit_breakfast_price" name="breakfast_price" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <label class="flex items-center cursor-pointer select-none">
                        <input type="checkbox" id="edit_extra_bed_allowed" name="extra_bed_allowed" value="1" class="rounded border-slate-300 text-amber-500 w-4 h-4 mr-2" />
                        <span class="text-xs font-semibold text-slate-700">Extra Bed Allowed</span>
                    </label>
                    <div>
                        <label for="edit_extra_bed_price" class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Harga Extra Bed</label>
                        <input type="number" id="edit_extra_bed_price" name="extra_bed_price" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs" />
                    </div>
                </div>
            </div>

            <label class="flex items-center cursor-pointer select-none">
                <input type="checkbox" id="edit_is_active" name="is_active" value="1" class="rounded border-slate-300 text-amber-500 w-4 h-4 mr-2" />
                <span class="text-xs font-bold text-slate-700 uppercase">Status Tipe Kamar Aktif</span>
            </label>

            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs rounded-lg uppercase tracking-wider">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddModal() {
        document.getElementById('add-modal').classList.remove('hidden');
    }
    function closeAddModal() {
        document.getElementById('add-modal').classList.add('hidden');
    }

    function openEditModal(type) {
        document.getElementById('edit-modal-title').innerText = 'Edit Tipe Kamar: ' + type.name;
        document.getElementById('edit_name').value = type.name;
        document.getElementById('edit_description').value = type.description || '';
        document.getElementById('edit_base_price').value = parseInt(type.base_price);
        document.getElementById('edit_capacity').value = type.capacity;
        
        document.getElementById('edit_breakfast_included').checked = !!type.breakfast_included;
        document.getElementById('edit_breakfast_price').value = parseInt(type.breakfast_price);
        
        document.getElementById('edit_extra_bed_allowed').checked = !!type.extra_bed_allowed;
        document.getElementById('edit_extra_bed_price').value = parseInt(type.extra_bed_price);
        document.getElementById('edit_is_active').checked = !!type.is_active;

        document.getElementById('edit-form').action = '/admin/room-types/' + type.id;
        
        document.getElementById('edit-modal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('edit-modal').classList.add('hidden');
    }
</script>
@endsection
