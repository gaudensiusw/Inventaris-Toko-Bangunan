<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Retur Penjualan - {{ $refund->no_transaksi }}</title>
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
            default => '320px',
        };
    @endphp
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Courier New', Courier, monospace;
        }

        body {
            background-color: #f1f5f9;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .receipt {
            background: #fff;
            width: {{ $widthClass }};
            max-width: {{ $maxWidth }};
            padding: {{ $paperSize === '58' ? '10px' : ($paperSize === 'a4' ? '24px' : '16px') }};
            border-radius: 4px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .store-name {
            text-align: center;
            font-size: {{ $paperSize === '58' ? '13px' : ($paperSize === 'a4' ? '20px' : '16px') }};
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .store-tagline {
            text-align: center;
            font-size: 10px;
            color: #444;
            margin-bottom: 2px;
        }

        .store-address {
            text-align: center;
            font-size: 10px;
            color: #555;
            line-height: 1.4;
            margin-top: 2px;
        }

        .divider {
            border: none;
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .divider-double {
            border: none;
            border-top: 2px solid #000;
            margin: 8px 0;
        }

        .title-badge {
            text-align: center;
            font-weight: bold;
            font-size: {{ $paperSize === '58' ? '11px' : '13px' }};
            text-transform: uppercase;
            margin: 6px 0;
            background-color: #000;
            color: #fff;
            padding: 3px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            font-size: {{ $paperSize === '58' ? '9.5px' : '11px' }};
            margin-bottom: 3px;
        }

        .info-label {
            color: #555;
        }

        .info-value {
            font-weight: bold;
        }

        .item-row {
            margin-bottom: 6px;
            font-size: {{ $paperSize === '58' ? '9.5px' : '11px' }};
        }

        .item-name {
            font-weight: bold;
        }

        .item-detail {
            display: flex;
            justify-content: space-between;
            color: #333;
        }

        .grand-total {
            display: flex;
            justify-content: space-between;
            font-size: {{ $paperSize === '58' ? '12px' : ($paperSize === 'a4' ? '18px' : '14px') }};
            font-weight: bold;
            margin-top: 4px;
        }

        .footer {
            text-align: center;
            font-size: 10px;
            color: #555;
            margin-top: 12px;
        }

        .signature-box {
            display: flex;
            justify-content: space-between;
            margin-top: 16px;
            font-size: 10px;
            text-align: center;
        }

        .no-print {
            margin-bottom: 16px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            width: 100%;
            max-width: {{ $maxWidth }};
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
            gap: 10px;
        }

        .btn-print {
            background-color: #2563eb;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            font-family: sans-serif;
            font-size: 14px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            flex: 1;
            text-align: center;
        }

        .btn-back {
            background-color: #64748b;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            font-family: sans-serif;
            font-size: 14px;
            flex: 1;
            text-align: center;
        }

        @media print {
            body {
                background-color: #fff;
                padding: 0;
            }
            .receipt {
                box-shadow: none;
                width: {{ $widthClass }};
                max-width: 100%;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="no-print">
    <div class="paper-selector">
        <a href="{{ request()->fullUrlWithQuery(['paper_size' => '58']) }}" class="btn-size {{ $paperSize === '58' ? 'active' : '' }}">📄 Thermal 58mm</a>
        <a href="{{ request()->fullUrlWithQuery(['paper_size' => '80']) }}" class="btn-size {{ $paperSize === '80' ? 'active' : '' }}">📄 Thermal 80mm</a>
        <a href="{{ request()->fullUrlWithQuery(['paper_size' => 'a4']) }}" class="btn-size {{ $paperSize === 'a4' ? 'active' : '' }}">📜 Kertas A4</a>
    </div>
    <div class="action-btns">
        <a href="{{ route('pos.retur.index') }}" class="btn-back">← Kembali ke Retur</a>
        <button onclick="window.print()" class="btn-print">🖨️ Cetak Struk Retur</button>
    </div>
</div>

<div class="receipt">
    <!-- Store Header -->
    <div class="store-name">Toko Rajawali</div>
    <div class="store-tagline">Toko Bangunan & Material</div>
    <div class="store-address">Jl. Merdeka No.4, Kel. Kedondong Raye,<br>Kec. Banyuasin III, Kab. Banyuasin,<br>Sumatera Selatan</div>
    <div class="store-address">Telp: 0852-66448857</div>

    <div class="title-badge">BUKTI RETUR PENJUALAN</div>

    <!-- Transaction Info -->
    <div class="info-row">
        <span class="info-label">No. Struk Asli</span>
        <span class="info-value">{{ $refund->no_transaksi }}</span>
    </div>
    @if($refund->no_refund)
    <div class="info-row">
        <span class="info-label">No. Retur</span>
        <span class="info-value" style="font-size:10px;">{{ $refund->no_refund }}</span>
    </div>
    @endif
    <div class="info-row">
        <span class="info-label">Tgl Retur</span>
        <span class="info-value">{{ \Carbon\Carbon::parse($refund->tgl_refund)->format('d M Y H:i') }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Operator</span>
        <span class="info-value">{{ $refund->user->name ?? 'Operator' }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Pelanggan</span>
        <span class="info-value">{{ $refund->pos->pelanggan->nama ?? ($refund->pos->nama_pelanggan ?: 'Umum') }}</span>
    </div>

    <hr class="divider-double">

    <!-- Returned Items -->
    <div style="font-size: 11px; font-weight: bold; margin-bottom: 4px;">RINCIAN BARANG RETUR:</div>
    @foreach($batch_refunds as $b)
    <div class="item-row">
        <div class="item-name">{{ $b->nama_produk }}</div>
        <div class="item-detail">
            @php
                $hargaSatuan = $b->qty_refund > 0 ? ($b->nominal_refund / $b->qty_refund) : 0;
                $satuanLabel = $b->satuan_nama ?: 'Pcs';
            @endphp
            <span>{{ floatval($b->qty_refund) }} {{ $satuanLabel }} &times; Rp {{ number_format($hargaSatuan, 0, ',', '.') }}</span>
            <span>Rp {{ number_format($b->nominal_refund, 0, ',', '.') }}</span>
        </div>
        <div style="font-size: 9px; color: #666; font-style: italic;">Catatan: {{ $b->alasan }}</div>
    </div>
    @endforeach

    <hr class="divider">

    <!-- Totals -->
    <div class="grand-total">
        <span>TOTAL REFUND</span>
        <span>Rp {{ number_format($batch_refunds->sum('nominal_refund'), 0, ',', '.') }}</span>
    </div>

    <div style="margin-top: 6px; font-size: 10px; color: #444;">
        Status Transaksi Asli: <strong>{{ strtoupper($refund->pos->metode_pembayaran) }}</strong>
        @if($refund->pos->metode_pembayaran === 'Bon')
            <br><em>(Potong Saldo Hutang Pelanggan)</em>
        @else
            <br><em>(Pengembalian Uang Tunai / Cash)</em>
        @endif
    </div>

    <div class="signature-box">
        <div>
            <p>Hormat Kami,</p>
            <br><br>
            <p>( Toko )</p>
        </div>
        <div>
            <p>Pelanggan,</p>
            <br><br>
            <p>( {{ $refund->pos->pelanggan->nama ?? 'Pembeli' }} )</p>
        </div>
    </div>

    <div class="footer">
        <hr class="divider">
        <p>Terima kasih atas kerja samanya.</p>
        <p>Simpan lembar nota retur ini sebagai bukti resmi.</p>
    </div>
</div>

<script>
    // Auto-print hanya ketika dibuka pertama kali setelah proses retur
    // (bukan ketika dibuka kembali dari riwayat)
    @if(session('success'))
    window.onload = function() {
        setTimeout(function() {
            window.print();
        }, 600);
    }
    @endif
</script>

</body>
</html>
