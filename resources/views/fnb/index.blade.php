@extends('layouts.fnb')

@section('header-title', 'Display Antrean Dapur F&B')

@section('content')
<div class="space-y-8 max-w-6xl mx-auto">
    <!-- Active Orders Grid -->
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-6">
        <h3 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-3">Daftar Antrean Masuk</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($activeOrders as $order)
                <div class="border border-slate-100 p-5 rounded-2xl bg-slate-50/50 flex flex-col justify-between gap-5 relative">
                    <!-- Order Header -->
                    <div>
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="text-sm font-bold text-slate-800">Order #{{ $order->id }}</span>
                                <span class="block text-xs font-bold text-slate-700 mt-0.5">Kamar #{{ $order->reservation->room->room_number }}</span>
                            </div>
                            
                            @php
                                $sColor = 'bg-slate-100 text-slate-600 dark:bg-slate-850 dark:text-slate-300';
                                if($order->status === 'pending') $sColor = 'bg-slate-200 text-slate-900 dark:bg-slate-800 dark:text-slate-300';
                                elseif($order->status === 'preparing') $sColor = 'bg-amber-500 text-slate-950 dark:bg-amber-500 dark:text-slate-950';
                                elseif($order->status === 'confirmed') $sColor = 'bg-blue-500 text-white dark:bg-blue-600 dark:text-white';
                            @endphp
                            <span class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $sColor }}">
                                {{ $order->status }}
                            </span>
                        </div>
                        
                        <p class="text-[10px] text-slate-400 mt-1 uppercase tracking-wider">Tamu: {{ $order->guest->full_name }}</p>
                        <p class="text-[10px] text-slate-400">Jam Order: {{ $order->order_time->format('H:i') }}</p>

                        <!-- Items List -->
                        <div class="mt-4 pt-3 border-t border-slate-100 space-y-2">
                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest block">Item Pesanan:</span>
                            @foreach($order->items as $item)
                                <div class="flex justify-between text-xs">
                                    <span class="font-semibold text-slate-700">{{ $item->qty }}x {{ $item->foodItem->name }}</span>
                                    @if($item->notes)
                                        <span class="text-[10px] text-rose-500 italic block font-medium">({{ $item->notes }})</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <!-- Special instructions -->
                        @if($order->notes)
                            <div class="mt-3 pt-2 border-t border-dashed border-slate-200">
                                <span class="text-[9px] text-slate-400 font-bold uppercase tracking-widest block">Catatan Tambahan:</span>
                                <span class="text-xs text-rose-600 font-semibold italic">"{{ $order->notes }}"</span>
                            </div>
                        @endif
                    </div>

                    <!-- Actions Form -->
                    <div class="pt-3 border-t border-slate-100">
                        <form action="{{ route('fnb.process', $order->id) }}" method="POST" class="flex flex-col gap-2">
                            @csrf
                            @if($order->status === 'pending')
                                <input type="hidden" name="status" value="preparing" />
                                <button type="submit" class="w-full py-2 !bg-amber-500 hover:!bg-amber-400 !text-slate-950 font-bold text-xs rounded-lg uppercase tracking-wider shadow-sm transition-transform active:scale-[0.98]">
                                    Mulai Masak (Prepare)
                                </button>
                            @elseif($order->status === 'preparing')
                                <input type="hidden" name="status" value="delivered" />
                                <button type="submit" class="w-full py-2 !bg-emerald-500 hover:!bg-emerald-400 !text-white font-bold text-xs rounded-lg uppercase tracking-wider shadow-sm transition-transform active:scale-[0.98]">
                                    Selesai & Antar (Deliver)
                                </button>
                            @endif
                            
                            <!-- Cancel Action -->
                            <button type="button" 
                                    onclick="if(confirm('Batalkan order ini?')) { document.getElementById('cancel-form-{{ $order->id }}').submit(); }"
                                    class="w-full py-1 text-rose-500 hover:bg-rose-50 border border-transparent hover:border-rose-100 text-[10px] font-bold rounded-lg uppercase transition-all">
                                Batalkan Order
                            </button>
                        </form>
                        
                        <!-- Real cancel form -->
                        <form id="cancel-form-{{ $order->id }}" action="{{ route('fnb.process', $order->id) }}" method="POST" class="hidden">
                            @csrf
                            <input type="hidden" name="status" value="cancelled" />
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-8 text-center text-slate-400">Tidak ada antrean pesanan makanan & minuman.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
