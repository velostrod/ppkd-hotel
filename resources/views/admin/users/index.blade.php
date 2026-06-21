@extends('layouts.admin')

@section('header-title', 'Staff Management')

@section('content')
<div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between border-b border-slate-100 pb-4 gap-3">
        <h3 class="text-base font-bold text-slate-800">Daftar Akun Staff Hotel</h3>
        <button onclick="openAddModal()" class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 rounded-xl text-sm font-bold shadow-sm uppercase tracking-wider transition-all w-full sm:w-auto text-center">
            + Tambah Staff
        </button>
    </div>

    <!-- Mobile Cards -->
    <div class="block md:hidden space-y-3">
        @foreach($users as $user)
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-800">{{ $user->name }}</span>
                    <span class="px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wider {{ $user->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-rose-50 text-rose-700 border border-rose-100' }}">
                        {{ $user->status }}
                    </span>
                </div>
                <div class="text-xs text-slate-500">{{ $user->email }}</div>
                <div class="flex items-center justify-between pt-1">
                    <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded text-xs font-semibold uppercase tracking-wider">
                        {{ str_replace('_', ' ', $user->role->name) }}
                    </span>
                    <div class="space-x-3 shrink-0">
                        <button onclick="openEditModal({{ json_encode($user) }})" class="text-amber-500 hover:underline text-xs font-bold whitespace-nowrap">Edit</button>
                        <form action="{{ route('admin.users.toggle', $user->id) }}" method="POST" class="inline-block">
                            @csrf
                            <button type="submit" class="{{ $user->status === 'active' ? 'text-rose-500' : 'text-emerald-500' }} hover:underline text-xs font-bold whitespace-nowrap">
                                {{ $user->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Desktop Table -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left text-sm border-collapse">
            <thead>
                <tr class="text-slate-400 font-semibold border-b border-slate-100">
                    <th class="py-3 px-4">Nama Lengkap</th>
                    <th class="py-3 px-4">Email</th>
                    <th class="py-3 px-4">Role Akses</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 text-slate-700">
                @foreach($users as $user)
                    <tr>
                        <td class="py-4 px-4 font-bold">{{ $user->name }}</td>
                        <td class="py-4 px-4 text-slate-500">{{ $user->email }}</td>
                        <td class="py-4 px-4">
                            <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded text-xs font-semibold uppercase tracking-wider">
                                {{ str_replace('_', ' ', $user->role->name) }}
                            </span>
                        </td>
                        <td class="py-4 px-4">
                            <span class="px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wider {{ $user->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-rose-50 text-rose-700 border border-rose-100' }}">
                                {{ $user->status }}
                            </span>
                        </td>
                        <td class="py-4 px-4 text-right space-x-2">
                            <button onclick="openEditModal({{ json_encode($user) }})" class="text-amber-500 hover:underline text-xs font-bold">Edit</button>
                            <form action="{{ route('admin.users.toggle', $user->id) }}" method="POST" class="inline-block">
                                @csrf
                                <button type="submit" class="{{ $user->status === 'active' ? 'text-rose-500' : 'text-emerald-500' }} hover:underline text-xs font-bold">
                                    {{ $user->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="pt-4 border-t border-slate-50">
        {{ $users->links() }}
    </div>
</div>

<!-- ==========================================
     ADD STAFF MODAL
     ========================================== -->
<div id="add-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 animate-in fade-in zoom-in-95 duration-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-6">
            <h3 class="text-sm font-bold text-slate-800 uppercase">Tambah Staff Baru</h3>
            <button onclick="closeAddModal()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
            @csrf
            <div>
                <label for="name" class="block text-xs font-bold text-slate-500 uppercase mb-1">Nama Lengkap</label>
                <input type="text" id="name" name="name" required class="w-full px-3.5 py-2 border border-slate-200 rounded-lg text-sm focus:ring-amber-500 focus:border-amber-500" />
            </div>

            <div>
                <label for="email" class="block text-xs font-bold text-slate-500 uppercase mb-1">Email Login</label>
                <input type="email" id="email" name="email" required class="w-full px-3.5 py-2 border border-slate-200 rounded-lg text-sm focus:ring-amber-500 focus:border-amber-500" />
            </div>

            <div>
                <label for="password" class="block text-xs font-bold text-slate-500 uppercase mb-1">Password</label>
                <input type="password" id="password" name="password" required class="w-full px-3.5 py-2 border border-slate-200 rounded-lg text-sm focus:ring-amber-500 focus:border-amber-500" />
            </div>

            <div>
                <label for="role_id" class="block text-xs font-bold text-slate-500 uppercase mb-1">Role Akses</label>
                <select id="role_id" name="role_id" required class="w-full px-3.5 py-2 border border-slate-200 rounded-lg text-sm focus:ring-amber-500 focus:border-amber-500 bg-white">
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}">{{ str_replace('_', ' ', $role->name) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="block text-xs font-bold text-slate-500 uppercase mb-1">Status</label>
                <select id="status" name="status" required class="w-full px-3.5 py-2 border border-slate-200 rounded-lg text-sm focus:ring-amber-500 focus:border-amber-500 bg-white">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit" class="px-5 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs rounded-lg uppercase tracking-wider">
                    Simpan Akun
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==========================================
     EDIT STAFF MODAL
     ========================================== -->
<div id="edit-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 animate-in fade-in zoom-in-95 duration-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-6">
            <h3 class="text-sm font-bold text-slate-800 uppercase" id="edit-modal-title">Edit Staff</h3>
            <button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        
        <form method="POST" id="edit-form" class="space-y-4">
            @csrf
            @method('PUT')
            
            <div>
                <label for="edit_name" class="block text-xs font-bold text-slate-500 uppercase mb-1">Nama Lengkap</label>
                <input type="text" id="edit_name" name="name" required class="w-full px-3.5 py-2 border border-slate-200 rounded-lg text-sm focus:ring-amber-500" />
            </div>

            <div>
                <label for="edit_email" class="block text-xs font-bold text-slate-500 uppercase mb-1">Email Login</label>
                <input type="email" id="edit_email" name="email" required class="w-full px-3.5 py-2 border border-slate-200 rounded-lg text-sm focus:ring-amber-500" />
            </div>

            <div>
                <label for="edit_password" class="block text-xs font-bold text-slate-500 uppercase mb-1">Password Baru (Opsional)</label>
                <input type="password" id="edit_password" name="password" class="w-full px-3.5 py-2 border border-slate-200 rounded-lg text-sm focus:ring-amber-500" placeholder="Kosongkan jika tidak diubah" />
            </div>

            <div>
                <label for="edit_role_id" class="block text-xs font-bold text-slate-500 uppercase mb-1">Role Akses</label>
                <select id="edit_role_id" name="role_id" required class="w-full px-3.5 py-2 border border-slate-200 rounded-lg text-sm focus:ring-amber-500 bg-white">
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}">{{ str_replace('_', ' ', $role->name) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="edit_status" class="block text-xs font-bold text-slate-500 uppercase mb-1">Status</label>
                <select id="edit_status" name="status" required class="w-full px-3.5 py-2 border border-slate-200 rounded-lg text-sm focus:ring-amber-500 bg-white">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit" class="px-5 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs rounded-lg uppercase tracking-wider">
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

    function openEditModal(user) {
        document.getElementById('edit-modal-title').innerText = 'Edit Staff: ' + user.name;
        document.getElementById('edit_name').value = user.name;
        document.getElementById('edit_email').value = user.email;
        document.getElementById('edit_role_id').value = user.role_id;
        document.getElementById('edit_status').value = user.status;
        
        // Dynamic route path
        document.getElementById('edit-form').action = '/admin/users/' + user.id;
        
        document.getElementById('edit-modal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('edit-modal').classList.add('hidden');
    }
</script>
@endsection
