<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #{{ $pos->no_transaksi }}</title>
    @php
        $paperSize = request('paper_size', '80');
        $widthClass = match($paperSize) {
            '58' => '58mm',
            'a4' => '100%',
            default => '80mm',
        };
        $maxWidth = match($paperSize) {
            '58' => '220px',
            'a4' => '750px',
            default => '300px',
        };
        $fontSize = match($paperSize) {
            '58' => '10px',
            'a4' => '14px',
            default => '12px',
        };
    @endphp
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: {{ $fontSize }};
            background: #f5f5f5;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .receipt {
            background: white;
            width: {{ $widthClass }};
            max-width: {{ $maxWidth }};
            padding: {{ $paperSize === '58' ? '10px' : ($paperSize === 'a4' ? '24px' : '16px') }};
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }

        /* Header */
        .store-name {
            text-align: center;
            font-size: {{ $paperSize === '58' ? '13px' : ($paperSize === 'a4' ? '20px' : '16px') }};
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 2px;
        }
        .store-tagline {
            text-align: center;
            font-size: {{ $paperSize === '58' ? '9px' : '10px' }};
            color: #666;
            margin-bottom: 4px;
        }
        .store-address {
            text-align: center;
            font-size: {{ $paperSize === '58' ? '8.5px' : '10px' }};
            color: #666;
            line-height: 1.4;
            margin-top: 2px;
        }

        /* Divider */
        .divider {
            border: none;
            border-top: 1px dashed #ccc;
            margin: 10px 0;
        }
        .divider-solid {
            border: none;
            border-top: 1px solid #999;
            margin: 10px 0;
        }
        .divider-double {
            border: none;
            border-top: 3px double #333;
            margin: 10px 0;
        }

        /* Info rows */
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            font-size: {{ $paperSize === '58' ? '9.5px' : '11px' }};
        }
        .info-label { color: #666; }
        .info-value { font-weight: bold; text-align: right; }

        /* Items table */
        .items-header {
            display: flex;
            justify-content: space-between;
            font-size: {{ $paperSize === '58' ? '8.5px' : '10px' }};
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .item-row {
            margin-bottom: 6px;
        }
        .item-name {
            font-weight: bold;
            font-size: {{ $paperSize === '58' ? '9.5px' : '11px' }};
            margin-bottom: 1px;
        }
        .item-detail {
            display: flex;
            justify-content: space-between;
            font-size: {{ $paperSize === '58' ? '9.5px' : '11px' }};
            color: #555;
        }
        .item-subtotal {
            font-weight: bold;
            color: #222;
        }

        /* Totals */
        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: {{ $paperSize === '58' ? '9.5px' : '11px' }};
            margin-bottom: 3px;
        }
        .grand-total {
            display: flex;
            justify-content: space-between;
            font-size: {{ $paperSize === '58' ? '12px' : ($paperSize === 'a4' ? '18px' : '15px') }};
            font-weight: bold;
            margin-top: 4px;
        }

        /* Footer */
        .footer {
            text-align: center;
            font-size: {{ $paperSize === '58' ? '8.5px' : '10px' }};
            color: #888;
            margin-top: 6px;
            line-height: 1.6;
        }
        .thank-you {
            text-align: center;
            font-size: {{ $paperSize === '58' ? '11px' : '13px' }};
            font-weight: bold;
            margin: 8px 0 4px;
            letter-spacing: 1px;
        }

        /* Status badge */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            border: 1px solid;
            letter-spacing: 1px;
        }
        .badge-bon { color: #c05c00; border-color: #c05c00; }
        .badge-cash { color: #1a7a4a; border-color: #1a7a4a; }

        /* Print button (hidden on print) */
        .print-actions {
            width: 100%;
            max-width: {{ $maxWidth }};
            margin: 16px auto 0;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .paper-selector {
            display: flex;
            gap: 6px;
            background: #e2e8f0;
            padding: 4px;
            border-radius: 8px;
        }
        .btn-size {
            flex: 1;
            padding: 6px 10px;
            border: none;
            background: transparent;
            font-size: 11px;
            font-weight: bold;
            color: #475569;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
        }
        .btn-size.active {
            background: white;
            color: #0f172a;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .action-btns {
            display: flex;
            gap: 8px;
        }
        .btn-print {
            flex: 1;
            padding: 10px;
            background: #1e293b;
            color: white;
            border: none;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            letter-spacing: 0.5px;
            border-radius: 6px;
        }
        .btn-print:hover { background: #0f172a; }
        .btn-close {
            flex: 1;
            padding: 10px;
            background: white;
            color: #555;
            border: 1px solid #ccc;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            border-radius: 6px;
        }

        @media print {
            body { background: white; padding: 0; }
            .receipt { box-shadow: none; width: {{ $widthClass }}; max-width: 100%; }
            .print-actions { display: none !important; }
        }
    </style>
</head>
<body>

<div>
    <div class="receipt">
        <!-- Store Header -->
        <div class="store-name">Toko Rajawali</div>
        <div class="store-tagline">Toko Bangunan & Material</div>
        <div class="store-address">Jl. Merdeka No.4, Kel. Kedondong Raye,<br>Kec. Banyuasin III, Kab. Banyuasin,<br>Sumatera Selatan</div>
        <div class="store-address">Telp: 0852-66448857</div>

        <hr class="divider-double" style="margin-top: 12px">

        <!-- Transaction Info -->
        <div class="info-row">
            <span class="info-label">No. Struk</span>
            <span class="info-value">{{ $pos->no_transaksi }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Tanggal</span>
            <span class="info-value">{{ \Carbon\Carbon::parse($pos->tgl_transaksi)->format('d M Y H:i') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Operator</span>
            <span class="info-value">{{ auth()->user()->name ?? 'Operator' }}</span>
        </div>
        @if($pos->nama_pelanggan && $pos->nama_pelanggan !== 'Umum')
        <div class="info-row">
            <span class="info-label">Pelanggan</span>
            <span class="info-value">{{ $pos->nama_pelanggan }}</span>
        </div>
        @endif
        <div class="info-row">
            <span class="info-label">Pengiriman</span>
            <span class="info-value">{{ $pos->opsi_pengiriman }}</span>
        </div>

        <hr class="divider">

        <!-- Items -->
        <div class="items-header">
            <span>Barang</span>
            <span>Subtotal</span>
        </div>

        @foreach($pos->details as $detail)
        <div class="item-row">
            <div class="item-name">{{ $detail->product->nama ?? 'Produk Dihapus' }}</div>
            <div class="item-detail">
                <span>{{ $detail->qty }} {{ $detail->satuan_nama ?? ($detail->product->unit ?? '') }} × {{ number_format($detail->harga_satuan ?: $detail->harga, 0, ',', '.') }}</span>
                <span class="item-subtotal">{{ number_format($detail->subtotal, 0, ',', '.') }}</span>
            </div>
        </div>
        @endforeach

        <hr class="divider">

        <!-- Totals -->
        <div class="total-row">
            <span>Subtotal</span>
            <span>Rp {{ number_format($pos->subtotal, 0, ',', '.') }}</span>
        </div>
        @if($pos->biaya_addon > 0)
        <div class="total-row">
            <span>Add On ({{ $pos->keterangan_addon ?: 'Biaya Lain' }})</span>
            <span>Rp {{ number_format($pos->biaya_addon, 0, ',', '.') }}</span>
        </div>
        @endif
        @if($pos->ongkos_kirim > 0)
        <div class="total-row">
            <span>Ongkos Kirim</span>
            <span>Rp {{ number_format($pos->ongkos_kirim, 0, ',', '.') }}</span>
        </div>
        @endif

        <hr class="divider-solid">

        <div class="grand-total">
            <span>TOTAL</span>
            <span>Rp {{ number_format($pos->total_tagihan, 0, ',', '.') }}</span>
        </div>

        <div style="margin-top: 8px; display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 11px; color: #666;">Metode Bayar</span>
            <span class="badge {{ $pos->metode_pembayaran === 'Bon' ? 'badge-bon' : 'badge-cash' }}">
                {{ $pos->metode_pembayaran }}
            </span>
        </div>

        @if($pos->pelanggan && $pos->pelanggan->nama !== 'Umum')
        <div class="info-row" style="margin-top: 4px; color: #047857; font-weight: bold;">
            <span>Sisa Deposit Pelanggan:</span>
            <span>Rp {{ number_format($pos->pelanggan->deposit, 0, ',', '.') }}</span>
        </div>
        @endif

        @if($pos->catatan)
        <hr class="divider" style="margin-top: 10px">
        <div style="font-size: 10px; color: #666;">
            <strong>Catatan:</strong> {{ $pos->catatan }}
        </div>
        @endif

        <hr class="divider-double" style="margin-top: 12px">

        <div class="thank-you">★ TERIMA KASIH ★</div>
        <div class="footer">
            Barang yang sudah dibeli tidak dapat<br>
            dikembalikan tanpa nota pembelian.<br>
            <br>
            Struk ini adalah bukti pembayaran sah.
        </div>
    </div>

    <!-- Print Action Buttons -->
    <div class="print-actions">
        <div class="paper-selector">
            <a href="{{ request()->fullUrlWithQuery(['paper_size' => '58']) }}" class="btn-size {{ $paperSize === '58' ? 'active' : '' }}">📄 Thermal 58mm</a>
            <a href="{{ request()->fullUrlWithQuery(['paper_size' => '80']) }}" class="btn-size {{ $paperSize === '80' ? 'active' : '' }}">📄 Thermal 80mm</a>
            <a href="{{ request()->fullUrlWithQuery(['paper_size' => 'a4']) }}" class="btn-size {{ $paperSize === 'a4' ? 'active' : '' }}">📜 Kertas A4</a>
        </div>
        <div class="action-btns">
            <button class="btn-print" onclick="window.print()">🖨 Cetak Struk</button>
            <button class="btn-close" onclick="window.close()">✕ Tutup</button>
        </div>
    </div>
</div>

<script>
    // Auto-trigger print dialog when page loads
    window.addEventListener('load', function() {
        setTimeout(() => window.print(), 400);
    });
</script>
</body>
</html>
