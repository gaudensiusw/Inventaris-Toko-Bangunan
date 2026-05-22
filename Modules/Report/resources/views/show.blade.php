@extends('layouts.app')

@section('title', 'Report - ' . $title)
@section('header_title', $title)

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <a href="{{ route('report.index') }}" class="text-slate-500 hover:text-blue-600 flex items-center gap-2 font-bold text-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali ke Daftar Laporan
        </a>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 hover:text-blue-600 px-4 py-2 rounded-xl text-sm font-bold shadow-sm transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Print / Export PDF
            </button>
            <button class="bg-green-600 text-white hover:bg-green-700 px-4 py-2 rounded-xl text-sm font-bold shadow-sm transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Export Excel
            </button>
        </div>
    </div>

    <!-- Data Table Container -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-bold text-slate-800">{{ $title }}</h3>
                <p class="text-sm text-slate-500">Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }}</p>
            </div>
            <span class="bg-blue-100 text-blue-700 text-xs font-black uppercase px-3 py-1 rounded-full tracking-widest">
                {{ count($data) }} Data
            </span>
        </div>
        
        <div class="overflow-x-auto">
            @if(count($data) > 0)
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-slate-50 text-slate-500 border-b border-slate-100">
                        <tr>
                            <th class="py-4 px-6 font-semibold text-xs uppercase tracking-wider">#</th>
                            <!-- Generate headers dynamically based on data keys if available -->
                            @if(is_array($data[0]) || is_object($data[0]))
                                @foreach(array_keys(is_array($data[0]) ? $data[0] : $data[0]->toArray()) as $key)
                                    @if(!in_array($key, ['id', 'created_at', 'updated_at', 'deleted_at']))
                                        <th class="py-4 px-6 font-semibold text-xs uppercase tracking-wider">{{ str_replace('_', ' ', $key) }}</th>
                                    @endif
                                @endforeach
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($data as $index => $row)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-4 px-6 text-slate-500">{{ $index + 1 }}</td>
                            @if(is_array($row) || is_object($row))
                                @foreach(is_array($row) ? $row : $row->toArray() as $key => $val)
                                    @if(!in_array($key, ['id', 'created_at', 'updated_at', 'deleted_at']))
                                        <td class="py-4 px-6 text-slate-800 font-medium">
                                            @if(is_numeric($val) && strlen((string)$val) >= 4 && strpos($key, 'id') === false && strpos($key, 'qty') === false)
                                                Rp {{ number_format($val, 0, ',', '.') }}
                                            @elseif(is_array($val) || is_object($val))
                                                [Detail Data]
                                            @else
                                                {{ $val }}
                                            @endif
                                        </td>
                                    @endif
                                @endforeach
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="py-24 text-center">
                    <div class="w-20 h-20 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <h4 class="text-lg font-bold text-slate-800">Tidak Ada Data</h4>
                    <p class="text-sm text-slate-500 mt-1">Belum ada data untuk {{ $title }} pada saat ini.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .bg-white, .bg-white * {
            visibility: visible;
        }
        .bg-white {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            border: none;
            box-shadow: none;
        }
    }
</style>
@endsection
