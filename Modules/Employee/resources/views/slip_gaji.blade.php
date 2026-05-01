<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Slip Gaji - {{ $employee->nama }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 24px; text-transform: uppercase; letter-spacing: 1px; }
        .header p { margin: 5px 0 0; color: #666; }
        .info-table, .detail-table, .rekap-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 5px; }
        .info-table .label { font-weight: bold; width: 150px; }
        .detail-table th, .detail-table td, .rekap-table th, .rekap-table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        .detail-table th, .rekap-table th { background-color: #f9f9f9; text-transform: uppercase; font-size: 12px; color: #555; }
        .text-right { text-align: right !important; }
        .total-row { font-weight: bold; background-color: #f0f8ff !important; }
        .footer { margin-top: 50px; text-align: right; }
        .footer p { margin: 5px 0; }
        .signature { margin-top: 50px; border-top: 1px solid #333; width: 200px; display: inline-block; text-align: center; padding-top: 5px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Toko Bangunan</h1>
        <p>Slip Gaji Karyawan - Periode {{ \Carbon\Carbon::create()->month($currentMonth)->translatedFormat('F') }} {{ $currentYear }}</p>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">Nama Karyawan</td>
            <td>: {{ $employee->nama }}</td>
            <td class="label">Tanggal Cetak</td>
            <td>: {{ date('d F Y') }}</td>
        </tr>
        <tr>
            <td class="label">Jabatan</td>
            <td>: {{ $employee->jabatan->nama_jabatan ?? '-' }}</td>
            <td class="label">Status</td>
            <td>: {{ $employee->aktif ? 'Aktif' : 'Tidak Aktif' }}</td>
        </tr>
    </table>

    <h3>Rekap Absensi</h3>
    <table class="rekap-table">
        <tr>
            <th>Hadir</th>
            <th>Sakit</th>
            <th>Izin/Cuti</th>
            <th>Alpha</th>
        </tr>
        <tr>
            <td>{{ $rekap['hadir'] }} Hari</td>
            <td>{{ $rekap['sakit'] }} Hari</td>
            <td>{{ $rekap['izin'] }} Hari</td>
            <td>{{ $rekap['alpha'] }} Hari</td>
        </tr>
    </table>

    <h3>Rincian Gaji</h3>
    <table class="detail-table">
        <tr>
            <th>Keterangan</th>
            <th class="text-right">Jumlah</th>
        </tr>
        <tr>
            <td>Gaji Pokok ({{ $rekap['hadir'] }} Hari x Rp {{ number_format($gaji_harian, 0, ',', '.') }})</td>
            <td class="text-right">Rp {{ number_format($total_gaji_pokok, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Bonus / Tunjangan</td>
            <td class="text-right">Rp {{ number_format($bonus, 0, ',', '.') }}</td>
        </tr>
        <tr class="total-row">
            <td>TOTAL GAJI BERSIH</td>
            <td class="text-right">Rp {{ number_format($total_gaji, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="footer">
        <p>Pimpinan Toko,</p>
        <br><br><br>
        <div class="signature">Owner / Manager</div>
    </div>

</body>
</html>
