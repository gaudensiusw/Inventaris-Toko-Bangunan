@extends('layouts.app')

@section('title', 'Toko Bangunan - Stock Management')
@section('header_title', 'Stock Management')

@section('content')
<!-- Action Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div onclick="document.getElementById('stockInModal').classList.remove('hidden')" class="bg-white border border-slate-200 hover:border-green-300 hover:shadow-md cursor-pointer rounded-xl p-5 transition-all flex items-center gap-4 group">
        <div class="w-12 h-12 bg-green-50 text-green-500 rounded-full flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path></svg>
        </div>
        <div>
            <h3 class="font-bold text-slate-800 text-lg">Stock In</h3>
            <p class="text-xs text-slate-500 mt-0.5">Receive new stock</p>
        </div>
    </div>
    
    <div class="bg-white border border-slate-200 hover:border-red-300 hover:shadow-md cursor-pointer rounded-xl p-5 transition-all flex items-center gap-4 group">
        <div class="w-12 h-12 bg-red-50 text-red-500 rounded-full flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
        </div>
        <div>
            <h3 class="font-bold text-slate-800 text-lg">Stock Out</h3>
            <p class="text-xs text-slate-500 mt-0.5">Record stock removal</p>
        </div>
    </div>

    <div class="bg-white border border-slate-200 hover:border-blue-300 hover:shadow-md cursor-pointer rounded-xl p-5 transition-all flex items-center gap-4 group">
        <div class="w-12 h-12 bg-blue-50 text-[#2563eb] rounded-full flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
        </div>
        <div>
            <h3 class="font-bold text-slate-800 text-lg">Stock Adjustment</h3>
            <p class="text-xs text-slate-500 mt-0.5">Adjust stock levels</p>
        </div>
    </div>
</div>

<!-- Current Stock Levels -->
<div class="bg-white border border-slate-200 rounded-xl shadow-sm mb-6 overflow-hidden">
    <div class="p-4 border-b border-slate-200 bg-slate-50">
        <h3 class="text-base font-bold text-slate-800">Current Stock Levels</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm whitespace-nowrap">
            <thead class="bg-white text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="p-4 font-semibold uppercase tracking-wider text-xs">Product</th>
                    <th class="p-4 font-semibold uppercase tracking-wider text-xs">SKU</th>
                    <th class="p-4 font-semibold uppercase tracking-wider text-xs">Current Stock</th>
                    <th class="p-4 font-semibold uppercase tracking-wider text-xs">Min/Max</th>
                    <th class="p-4 font-semibold uppercase tracking-wider text-xs">Stock Value</th>
                    <th class="p-4 font-semibold uppercase tracking-wider text-xs">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($stocks as $stock)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="p-4 font-medium text-slate-800">{{ $stock['product'] }}</td>
                    <td class="p-4 text-slate-500">{{ $stock['sku'] }}</td>
                    <td class="p-4 font-medium">{{ $stock['stock'] }}</td>
                    <td class="p-4 text-slate-500">{{ $stock['minmax'] }}</td>
                    <td class="p-4 text-slate-600">{{ $stock['value'] }}</td>
                    <td class="p-4">
                        @if($stock['status'] == 'Normal')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800 border border-slate-200">
                            Normal
                        </span>
                        @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-500 text-white shadow-sm">
                            Low Stock
                        </span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Recent Transactions -->
<div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
    <div class="p-4 border-b border-slate-200 bg-slate-50">
        <h3 class="text-base font-bold text-slate-800">Recent Transactions</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm whitespace-nowrap">
            <thead class="bg-white text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="p-4 font-semibold uppercase tracking-wider text-xs">Date</th>
                    <th class="p-4 font-semibold uppercase tracking-wider text-xs">Type</th>
                    <th class="p-4 font-semibold uppercase tracking-wider text-xs">Product</th>
                    <th class="p-4 font-semibold uppercase tracking-wider text-xs">Quantity</th>
                    <th class="p-4 font-semibold uppercase tracking-wider text-xs">Unit Price</th>
                    <th class="p-4 font-semibold uppercase tracking-wider text-xs">Total</th>
                    <th class="p-4 font-semibold uppercase tracking-wider text-xs">Reference</th>
                    <th class="p-4 font-semibold uppercase tracking-wider text-xs">Notes</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($transactions as $trx)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="p-4 text-slate-600">{{ $trx['date'] }}</td>
                    <td class="p-4">
                        @if($trx['type'] == 'in')
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-green-100 text-green-700 uppercase">in</span>
                        @elseif($trx['type'] == 'out')
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-700 uppercase">out</span>
                        @elseif($trx['type'] == 'sale')
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-purple-100 text-purple-700 uppercase">sale</span>
                        @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-blue-100 text-blue-700 uppercase">adjustment</span>
                        @endif
                    </td>
                    <td class="p-4 font-medium text-slate-800">{{ $trx['product'] }}</td>
                    <td class="p-4 font-medium text-slate-700">{{ $trx['qty'] }}</td>
                    <td class="p-4 text-slate-500">{{ $trx['price'] }}</td>
                    <td class="p-4 font-semibold text-slate-800">{{ $trx['total'] }}</td>
                    <td class="p-4 text-slate-500 text-xs">{{ $trx['ref'] }}</td>
                    <td class="p-4 text-slate-500">{{ $trx['notes'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Stock In Modal -->
<div id="stockInModal" class="fixed inset-0 z-[100] flex items-center justify-center hidden">
    <!-- Overlay -->
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="document.getElementById('stockInModal').classList.add('hidden')"></div>
    
    <!-- Modal Card -->
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden m-4 transform transition-all">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path></svg>
                Receive New Stock
            </h3>
            <button onclick="document.getElementById('stockInModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 transition-colors p-1 rounded-lg hover:bg-slate-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1">Product <span class="text-red-500">*</span></label>
                <select class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 bg-slate-50">
                    <option value="">Select product</option>
                    <option>Semen Portland - Gresik</option>
                    <option>Besi Beton 10mm x 12m</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Quantity <span class="text-red-500">*</span></label>
                    <input type="number" value="0" class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Unit Price</label>
                    <input type="number" value="0" class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1">Reference Number</label>
                <input type="text" placeholder="e.g., PO-2026-001" class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1">Notes</label>
                <textarea placeholder="Optional notes" class="w-full border border-slate-300 rounded-lg text-sm p-2.5 focus:ring-blue-500 focus:border-blue-500 resize-none h-20"></textarea>
            </div>

            <!-- Summary -->
            <div class="bg-blue-50 rounded-lg p-3 text-sm text-blue-800 border border-blue-100 flex flex-wrap justify-between items-center mt-2">
                <span>Qty: <b>0</b></span>
                <span>Price: <b>Rp 0</b></span>
                <span class="font-bold">Total: Rp 0</span>
            </div>
        </div>
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
            <button onclick="document.getElementById('stockInModal').classList.add('hidden')" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors">Cancel</button>
            <button class="px-5 py-2 bg-slate-800 hover:bg-slate-900 rounded-lg text-sm font-bold text-white shadow transition-colors">Submit</button>
        </div>
    </div>
</div>
@endsection
