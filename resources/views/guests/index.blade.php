@extends('layouts.fo')

@section('header-title', 'Manajemen Tamu')

@section('content')
<div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-6">
    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h3 class="text-base font-bold text-slate-800">Daftar Profil Tamu</h3>
        
        <div class="flex items-center space-x-3">
            <form method="GET" action="{{ route('guests.index') }}" class="flex">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari tamu..." 
                       class="px-4 py-2 border border-slate-200 rounded-l-xl text-sm focus:outline-none focus:ring-1 focus:ring-amber-500 focus:border-amber-500" />
                <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-r-xl text-sm font-semibold hover:bg-slate-700 transition-colors">
                    Cari
                </button>
            </form>
            
            <a href="{{ route('guests.create') }}" class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 rounded-xl text-sm font-bold shadow-sm transition-colors uppercase tracking-wider">
                Tambah Tamu
            </a>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm border-collapse">
            <thead>
                <tr class="text-slate-400 font-semibold border-b border-slate-100">
                    <th class="py-3 px-4">Nama Lengkap</th>
                    <th class="py-3 px-4">Identitas (KTP/Paspor)</th>
                    <th class="py-3 px-4">Telepon</th>
                    <th class="py-3 px-4">Email</th>
                    <th class="py-3 px-4">Kewarganegaraan</th>
                    <th class="py-3 px-4">Gender</th>
                    <th class="py-3 px-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($guests as $guest)
                    <tr>
                        <td class="py-4 px-4 font-bold text-slate-700">{{ $guest->full_name }}</td>
                        <td class="py-4 px-4 text-slate-500">{{ $guest->id_number }}</td>
                        <td class="py-4 px-4 text-slate-500">{{ $guest->phone }}</td>
                        <td class="py-4 px-4 text-slate-500">{{ $guest->email ?? '-' }}</td>
                        <td class="py-4 px-4 text-slate-500"><span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-xs">{{ $guest->nationality }}</span></td>
                        <td class="py-4 px-4 text-slate-500">
                            <span class="px-2 py-0.5 rounded text-xs {{ $guest->gender === 'male' ? 'bg-blue-50 text-blue-700' : 'bg-pink-50 text-pink-700' }}">
                                {{ $guest->gender === 'male' ? 'Laki-laki' : 'Perempuan' }}
                            </span>
                        </td>
                        <td class="py-4 px-4 text-right space-x-2">
                            <a href="{{ route('guests.edit', $guest->id) }}" class="text-amber-500 hover:text-amber-600 font-bold text-xs">Edit</a>
                            <form action="{{ route('guests.destroy', $guest->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data tamu ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-500 hover:text-rose-600 font-bold text-xs">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">Tidak ada data tamu ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="pt-4 border-t border-slate-50">
        {{ $guests->appends(request()->query())->links() }}
    </div>
</div>
@endsection
