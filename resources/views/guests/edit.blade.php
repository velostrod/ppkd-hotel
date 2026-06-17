@extends('layouts.fo')

@section('header-title', 'Ubah Profil Tamu')

@section('content')
<div class="max-w-2xl mx-auto bg-white p-8 rounded-2xl border border-slate-100 shadow-sm">
    <div class="flex items-center justify-between mb-8 pb-4 border-b border-slate-100">
        <h3 class="text-base font-bold text-slate-800">Ubah Data: {{ $guest->full_name }}</h3>
        <a href="{{ route('guests.index') }}" class="text-slate-400 hover:text-slate-600 text-sm flex items-center font-medium">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
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

    <form method="POST" action="{{ route('guests.update', $guest->id) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Full Name -->
            <div class="md:col-span-2">
                <label for="full_name" class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Lengkap Tamu <span class="text-rose-500">*</span></label>
                <input type="text" id="full_name" name="full_name" value="{{ old('full_name', $guest->full_name) }}" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 transition-all" />
            </div>

            <!-- Identity Number -->
            <div>
                <label for="id_number" class="block text-sm font-semibold text-slate-700 mb-1.5">Nomor KTP / Paspor <span class="text-rose-500">*</span></label>
                <input type="text" id="id_number" name="id_number" value="{{ old('id_number', $guest->id_number) }}" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 transition-all" />
            </div>

            <!-- Phone -->
            <div>
                <label for="phone" class="block text-sm font-semibold text-slate-700 mb-1.5">Nomor Telepon <span class="text-rose-500">*</span></label>
                <input type="text" id="phone" name="phone" value="{{ old('phone', $guest->phone) }}" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 transition-all" />
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', $guest->email) }}" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 transition-all" />
            </div>

            <!-- Nationality -->
            <div>
                <label for="nationality" class="block text-sm font-semibold text-slate-700 mb-1.5">Kewarganegaraan <span class="text-rose-500">*</span></label>
                <input type="text" id="nationality" name="nationality" value="{{ old('nationality', $guest->nationality) }}" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 transition-all" />
            </div>

            <!-- Gender -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Jenis Kelamin <span class="text-rose-500">*</span></label>
                <div class="flex items-center space-x-6 py-2.5">
                    <label class="flex items-center text-sm font-medium text-slate-700 cursor-pointer">
                        <input type="radio" name="gender" value="male" {{ old('gender', $guest->gender) === 'male' ? 'checked' : '' }} class="w-4 h-4 text-amber-500 focus:ring-0 mr-2" />
                        Laki-laki
                    </label>
                    <label class="flex items-center text-sm font-medium text-slate-700 cursor-pointer">
                        <input type="radio" name="gender" value="female" {{ old('gender', $guest->gender) === 'female' ? 'checked' : '' }} class="w-4 h-4 text-amber-500 focus:ring-0 mr-2" />
                        Perempuan
                    </label>
                </div>
            </div>

            <!-- Address -->
            <div class="md:col-span-2">
                <label for="address" class="block text-sm font-semibold text-slate-700 mb-1.5">Alamat</label>
                <textarea id="address" name="address" rows="3" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 transition-all">{{ old('address', $guest->address) }}</textarea>
            </div>

            <!-- Notes -->
            <div class="md:col-span-2">
                <label for="notes" class="block text-sm font-semibold text-slate-700 mb-1.5">Catatan Khusus</label>
                <textarea id="notes" name="notes" rows="2" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 transition-all">{{ old('notes', $guest->notes) }}</textarea>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 flex justify-end">
            <button type="submit" class="px-6 py-3 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl shadow-md transition-colors uppercase tracking-wider text-sm">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
