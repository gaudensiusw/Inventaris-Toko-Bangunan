<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #{{ $pos->no_transaksi }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            padding: 20px;
        }

        .receipt {
            background: white;
            width: 300px;
            padding: 16px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }

        /* Header */
        .store-name {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 2px;
        }
        .store-tagline {
            text-align: center;
            font-size: 10px;
            color: #666;
            margin-bottom: 4px;
        }
        .store-address {
            text-align: center;
            font-size: 10px;
            color: #666;
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
            font-size: 11px;
        }
        .info-label { color: #666; }
        .info-value { font-weight: bold; text-align: right; }

        /* Items table */
        .items-header {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
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
            font-size: 11px;
            margin-bottom: 1px;
        }
        .item-detail {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
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
            font-size: 11px;
            margin-bottom: 3px;
        }
        .grand-total {
            display: flex;
            justify-content: space-between;
            font-size: 15px;
            font-weight: bold;
            margin-top: 4px;
        }

        /* Footer */
        .footer {
            text-align: center;
            font-size: 10px;
            color: #888;
            margin-top: 6px;
            line-height: 1.6;
        }
        .thank-you {
            text-align: center;
            font-size: 13px;
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
            width: 300px;
            margin: 16px auto 0;
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
        }

        @media print {
            body { background: white; padding: 0; }
            .receipt { box-shadow: none; width: 100%; }
            .print-actions { display: none; }
        }
    </style>
</head>
<body>

<div>
    <div class="receipt">
        <!-- Store Header -->
        <div class="store-name">Toko Rajawali</div>
        <div class="store-tagline">Toko Bangunan & Material</div>
        <div class="store-address">Jl. Raya Rajawali No. 1, Kota Anda</div>
        <div class="store-address">Telp: (021) 000-0000</div>

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
            <span class="info-label">Kasir</span>
            <span class="info-value">Admin</span>
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
        <div class="total-row">
            <span>Pajak</span>
            <span>Rp {{ number_format($pos->pajak, 0, ',', '.') }}</span>
        </div>

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
        <button class="btn-print" onclick="window.print()">🖨 Cetak Struk</button>
        <button class="btn-close" onclick="window.close()">✕ Tutup</button>
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
