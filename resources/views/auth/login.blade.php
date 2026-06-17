<x-guest-layout>
    <script>
        sessionStorage.removeItem('tab_session_active');
    </script>
    <!-- Session Status -->
    @if(session('status'))
        <div class="mb-4 text-sm font-medium text-emerald-500 bg-emerald-500/10 p-3 rounded-lg border border-emerald-500/20">
            {{ session('status') }}
        </div>
    @endif

    <!-- General Errors -->
    @if ($errors->any())
        <div class="mb-4 text-sm font-medium text-rose-500 bg-rose-500/10 p-3 rounded-lg border border-rose-500/20">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" onsubmit="sessionStorage.setItem('tab_session_active', 'true');" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-medium text-slate-300 mb-1.5">Email Staf</label>
            <input id="email" 
                   type="email" 
                   name="email" 
                   value="{{ old('email') }}" 
                   required 
                   autofocus 
                   autocomplete="username" 
                   class="block w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 transition-all text-sm"
                   placeholder="username@kejora.com" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-slate-300 mb-1.5">Kata Sandi</label>
            <input id="password" 
                   type="password" 
                   name="password" 
                   required 
                   autocomplete="current-password" 
                   class="block w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 transition-all text-sm"
                   placeholder="Masukkan kata sandi Anda" />
        </div>



        <!-- Submit -->
        <div>
            <button type="submit" class="w-full py-3 bg-amber-500 hover:bg-amber-400 text-slate-950 font-semibold rounded-xl transition-all shadow-lg shadow-amber-500/20 active:scale-[0.98] text-sm tracking-wider uppercase">
                Masuk ke Sistem
            </button>
        </div>
    </form>
</x-guest-layout>
