@extends('layouts.app')

@section('title', 'Toko Bangunan - Notifikasi')
@section('header_title', 'Notifikasi')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-200 flex justify-between items-center bg-slate-50/50">
            <h3 class="text-lg font-bold text-slate-800">Semua Notifikasi</h3>
            <button class="text-blue-600 hover:text-blue-800 text-sm font-bold">Tandai semua dibaca</button>
        </div>
        <div class="divide-y divide-slate-100">
            @foreach($notifications as $notif)
            <div class="p-5 hover:bg-slate-50 transition-colors flex items-start gap-4 {{ $notif['unread'] ? 'bg-blue-50/20' : '' }}">
                <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 
                    {{ $notif['type'] == 'Stok Rendah' ? 'bg-red-100 text-red-600' : 
                       ($notif['type'] == 'Tagihan' ? 'bg-orange-100 text-orange-600' : 'bg-blue-100 text-blue-600') }}">
                    @if($notif['type'] == 'Stok Rendah')
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    @elseif($notif['type'] == 'Tagihan')
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    @else
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-center mb-1">
                        <span class="font-bold text-sm text-slate-800">{{ $notif['type'] }}</span>
                        <span class="text-[11px] text-slate-500 font-medium">{{ $notif['time'] }}</span>
                    </div>
                    <p class="text-sm text-slate-600 leading-relaxed">{{ $notif['message'] }}</p>
                </div>
                @if($notif['unread'])
                <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                @endif
            </div>
            @endforeach
        </div>
        <div class="p-4 text-center border-t border-slate-100 bg-slate-50/50">
            <button class="text-slate-500 hover:text-slate-700 text-sm font-bold">Lihat notifikasi lama</button>
        </div>
    </div>
</div>
@endsection
