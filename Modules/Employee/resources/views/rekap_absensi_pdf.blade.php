<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Kehadiran Karyawan - {{ $monthName }} {{ $year }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 20px; text-transform: uppercase; letter-spacing: 1px; }
        .header p { margin: 5px 0 0; color: #666; font-size: 13px; }
        .rekap-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .rekap-table th, .rekap-table td { border: 1px solid #ddd; padding: 8px 10px; text-align: center; }
        .rekap-table th { background-color: #f8fafc; text-transform: uppercase; font-size: 10px; color: #475569; font-weight: bold; }
        .rekap-table td.text-left { text-align: left; }
        .footer { margin-top: 50px; text-align: right; font-size: 11px; }
        .footer p { margin: 5px 0; }
        .signature { margin-top: 50px; border-top: 1px solid #333; width: 180px; display: inline-block; text-align: center; padding-top: 5px; }
        .meta-info { margin-bottom: 15px; font-size: 11px; color: #555; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Toko Bangunan</h1>
        <p>Rekapitulasi Kehadiran Karyawan - Periode {{ $monthName }} {{ $year }}</p>
    </div>

    <div class="meta-info">
        Tanggal Cetak: {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
    </div>

    <table class="rekap-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th class="text-left" style="width: 35%;">Nama Karyawan</th>
                <th class="text-left" style="width: 25%;">Jabatan</th>
                <th style="width: 9%;">Hadir</th>
                <th style="width: 9%;">Sakit</th>
                <th style="width: 9%;">Izin</th>
                <th style="width: 9%;">Alpha</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employees as $index => $emp)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-left">{{ $emp->nama }}</td>
                    <td class="text-left">{{ $emp->jabatan->nama_jabatan ?? '-' }}</td>
                    <td>{{ $emp->total_hadir }}</td>
                    <td>{{ $emp->total_sakit }}</td>
                    <td>{{ $emp->total_izin }}</td>
                    <td>{{ $emp->total_alpha }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="padding: 20px; color: #666;">Tidak ada data karyawan aktif untuk periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Pimpinan Toko,</p>
        <br><br><br>
        <div class="signature">Owner / Manager</div>
    </div>

</body>
</html>
