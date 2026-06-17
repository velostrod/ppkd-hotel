@extends('layouts.admin')

@section('header-title', 'Data Kamar')

@section('content')
<div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-6">
    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
        <h3 class="text-base font-bold text-slate-800">Manajemen Kamar Fisik</h3>
        @if(!auth()->user()->isManager())
        <button onclick="openAddModal()" class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 rounded-xl text-sm font-bold shadow-sm uppercase tracking-wider transition-all">
            + Tambah Kamar
        </button>
        @endif
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm border-collapse">
            <thead>
                <tr class="text-slate-400 font-semibold border-b border-slate-100">
                    <th class="py-3 px-4">Nomor Kamar</th>
                    <th class="py-3 px-4">Tipe Kamar</th>
                    <th class="py-3 px-4">Lantai</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4">Catatan</th>
                    <th class="py-3 px-4">Aktif</th>
                    @if(!auth()->user()->isManager())
                    <th class="py-3 px-4 text-right">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 text-slate-700">
                @foreach($rooms as $room)
                    <tr>
                        <td class="py-4 px-4 font-bold text-base">#{{ $room->room_number }}</td>
                        <td class="py-4 px-4 font-semibold text-slate-700">{{ $room->roomType->name }}</td>
                        <td class="py-4 px-4">Lantai {{ $room->floor }}</td>
                        <td class="py-4 px-4">
                            @php
                                $badge = 'bg-slate-100 text-slate-600';
                                switch($room->status) {
                                    case 'available': $badge = 'bg-emerald-50 text-emerald-700 border border-emerald-100'; break;
                                    case 'reserved': $badge = 'bg-blue-50 text-blue-700 border border-blue-100'; break;
                                    case 'occupied': $badge = 'bg-rose-50 text-rose-700 border border-rose-100'; break;
                                    case 'dirty': $badge = 'bg-amber-600 text-white'; break;
                                    case 'cleaning': $badge = 'bg-amber-50 text-amber-700 border border-amber-100'; break;
                                    case 'inspected': $badge = 'bg-teal-50 text-teal-700 border border-teal-100'; break;
                                    case 'maintenance': $badge = 'bg-indigo-50 text-indigo-700 border border-indigo-100'; break;
                                    case 'out_of_order': $badge = 'bg-red-50 text-red-700 border border-red-100'; break;
                                }
                            @endphp
                            <span class="px-2.5 py-0.5 rounded text-xs font-semibold uppercase tracking-wider {{ $badge }}">
                                {{ $room->status }}
                            </span>
                        </td>
                        <td class="py-4 px-4 text-slate-500 text-xs italic">{{ $room->notes ?? '-' }}</td>
                        <td class="py-4 px-4">
                            <span class="px-2 py-0.5 rounded text-xs font-bold {{ $room->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                {{ $room->is_active ? 'Yes' : 'No' }}
                            </span>
                        </td>
                        @if(!auth()->user()->isManager())
                        <td class="py-4 px-4 text-right">
                            <button onclick="openEditModal({{ json_encode($room) }})" class="text-amber-500 hover:underline text-xs font-bold">Edit</button>
                        </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="pt-4 border-t border-slate-50">
        {{ $rooms->links() }}
    </div>
</div>

<!-- ==========================================
     ADD MODAL
     ========================================== -->
<div id="add-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 animate-in fade-in zoom-in-95 duration-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-6">
            <h3 class="text-sm font-bold text-slate-800 uppercase">Tambah Kamar Baru</h3>
            <button onclick="closeAddModal()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        
        <form method="POST" action="{{ route('admin.rooms.store') }}" class="space-y-4">
            @csrf
            <div>
                <label for="room_number" class="block text-xs font-bold text-slate-500 uppercase mb-1">Nomor Kamar</label>
                <input type="text" id="room_number" name="room_number" required class="w-full px-3.5 py-2 border border-slate-200 rounded-lg text-sm focus:ring-amber-500" placeholder="Misal: 101" />
            </div>

            <div>
                <label for="room_type_id" class="block text-xs font-bold text-slate-500 uppercase mb-1">Tipe Kamar</label>
                <select id="room_type_id" name="room_type_id" required class="w-full px-3.5 py-2 border border-slate-200 rounded-lg text-sm focus:ring-amber-500 bg-white">
                    @foreach($roomTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }} (Rp {{ number_format($type->base_price, 0, ',', '.') }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="floor" class="block text-xs font-bold text-slate-500 uppercase mb-1">Lantai Kamar</label>
                <input type="number" id="floor" name="floor" required class="w-full px-3.5 py-2 border border-slate-200 rounded-lg text-sm focus:ring-amber-500" placeholder="Lantai" />
            </div>

            <div>
                <label for="notes" class="block text-xs font-bold text-slate-500 uppercase mb-1">Catatan Kamar</label>
                <textarea id="notes" name="notes" rows="2" class="w-full px-3.5 py-2 border border-slate-200 rounded-lg text-sm focus:ring-amber-500" placeholder="Opsional (Kamar dekat jendela, dsb.)"></textarea>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs rounded-lg uppercase tracking-wider">
                    Simpan Kamar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==========================================
     EDIT MODAL
     ========================================== -->
<div id="edit-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 animate-in fade-in zoom-in-95 duration-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-6">
            <h3 class="text-sm font-bold text-slate-800 uppercase" id="edit-modal-title">Edit Kamar</h3>
            <button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        
        <form method="POST" id="edit-form" class="space-y-4">
            @csrf
            @method('PUT')
            
            <div>
                <label for="edit_room_number" class="block text-xs font-bold text-slate-500 uppercase mb-1">Nomor Kamar</label>
                <input type="text" id="edit_room_number" name="room_number" required class="w-full px-3.5 py-2 border border-slate-200 rounded-lg text-sm focus:ring-amber-500" />
            </div>

            <div>
                <label for="edit_room_type_id" class="block text-xs font-bold text-slate-500 uppercase mb-1">Tipe Kamar</label>
                <select id="edit_room_type_id" name="room_type_id" required class="w-full px-3.5 py-2 border border-slate-200 rounded-lg text-sm focus:ring-amber-500 bg-white">
                    @foreach($roomTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="edit_floor" class="block text-xs font-bold text-slate-500 uppercase mb-1">Lantai Kamar</label>
                <input type="number" id="edit_floor" name="floor" required class="w-full px-3.5 py-2 border border-slate-200 rounded-lg text-sm focus:ring-amber-500" />
            </div>

            <div>
                <label for="edit_status" class="block text-xs font-bold text-slate-500 uppercase mb-1">Status Kamar</label>
                <select id="edit_status" name="status" required class="w-full px-3.5 py-2 border border-slate-200 rounded-lg text-sm focus:ring-amber-500 bg-white">
                    <option value="available">Available</option>
                    <option value="reserved">Reserved</option>
                    <option value="occupied">Occupied</option>
                    <option value="dirty">Dirty</option>
                    <option value="cleaning">Cleaning</option>
                    <option value="inspected">Inspected</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="out_of_order">Out of Order</option>
                </select>
            </div>

            <div>
                <label for="edit_notes" class="block text-xs font-bold text-slate-500 uppercase mb-1">Catatan</label>
                <textarea id="edit_notes" name="notes" rows="2" class="w-full px-3.5 py-2 border border-slate-200 rounded-lg text-sm focus:ring-amber-500"></textarea>
            </div>

            <label class="flex items-center cursor-pointer select-none">
                <input type="checkbox" id="edit_is_active" name="is_active" value="1" class="rounded border-slate-300 text-amber-500 w-4 h-4 mr-2" />
                <span class="text-xs font-bold text-slate-700 uppercase">Kamar Aktif (Dijual)</span>
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

    function openEditModal(room) {
        document.getElementById('edit-modal-title').innerText = 'Edit Kamar: #' + room.room_number;
        document.getElementById('edit_room_number').value = room.room_number;
        document.getElementById('edit_room_type_id').value = room.room_type_id;
        document.getElementById('edit_floor').value = room.floor;
        document.getElementById('edit_status').value = room.status;
        document.getElementById('edit_notes').value = room.notes || '';
        document.getElementById('edit_is_active').checked = !!room.is_active;

        document.getElementById('edit-form').action = '/admin/rooms/' + room.id;
        
        document.getElementById('edit-modal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('edit-modal').classList.add('hidden');
    }
</script>
@endsection
