<?php

namespace Modules\Employee\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'aktif');
        $search = $request->query('search');

        $query = \Modules\Employee\Models\Karyawan::with('jabatan');

        if ($status === 'aktif') {
            $query->where('aktif', 1);
        } elseif ($status === 'nonaktif') {
            $query->where('aktif', 0);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('kode_karyawan', 'like', "%{$search}%");
            });
        }

        $employees = $query->paginate(15)->appends($request->query());
        $jabatans = \Modules\Employee\Models\Jabatan::all();
        
        return view('employee::index', compact('employees', 'jabatans', 'status', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'jabatan_id' => 'required|exists:jabatan,id',
            'tanggal_masuk' => 'required|date',
        ]);

        // Cari karyawan dengan nomor urut paling besar (ekstrak angkanya saja)
        $lastKaryawan = \Modules\Employee\Models\Karyawan::orderByRaw("CAST(SUBSTRING(kode_karyawan, 5) AS UNSIGNED) DESC")->first();

        if ($lastKaryawan) {
            // Ambil angkanya saja, lalu tambah 1
            $lastNumber = (int) substr($lastKaryawan->kode_karyawan, 4);
            $nextNumber = $lastNumber + 1;
        } else {
            // Jika belum ada data sama sekali
            $nextNumber = 1;
        }

        // Format menjadi EMP-XXX
        $newCode = 'EMP-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        \Modules\Employee\Models\Karyawan::create([
            'kode_karyawan' => $newCode,
            'nama' => $request->nama,
            'jabatan_id' => $request->jabatan_id,
            'tanggal_masuk' => $request->tanggal_masuk,
            'no_hp' => $request->no_hp,
            'alamat' => $request->alamat,
            'aktif' => 1,
            'bonus_tetap' => 0,
        ]);

        return redirect()->back()->with('success', 'Data karyawan berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $employee = \Modules\Employee\Models\Karyawan::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:100',
            'jabatan_id' => 'required|exists:jabatan,id',
            'tanggal_masuk' => 'required|date',
            'email' => 'nullable|email',
            'bonus_tetap' => 'nullable|numeric|min:0',
            'potongan' => 'nullable|numeric|min:0',
            'keterangan_potongan' => 'nullable|string|max:255',
        ]);

        $newPotongan = $employee->potongan + ($request->potongan ?? 0);
        
        $newKet = $employee->keterangan_potongan;
        if ($request->filled('keterangan_potongan') || $request->potongan > 0) {
            $existingArr = $employee->keterangan_potongan ? json_decode($employee->keterangan_potongan, true) : [];
            if (!is_array($existingArr)) {
                $existingArr = $employee->keterangan_potongan ? [['keterangan' => $employee->keterangan_potongan, 'nominal' => 0]] : [];
            }
            $existingArr[] = [
                'keterangan' => $request->keterangan_potongan ?? 'Kasbon',
                'nominal' => $request->potongan ?? 0
            ];
            $newKet = json_encode($existingArr);
        }

        $employee->update([
            'nama' => $request->nama,
            'jabatan_id' => $request->jabatan_id,
            'tanggal_masuk' => $request->tanggal_masuk,
            'no_hp' => $request->no_hp,
            'email' => $request->email,
            'alamat' => $request->alamat,
            'bonus_tetap' => $request->bonus_tetap ?? 0,
            'potongan' => $newPotongan,
            'keterangan_potongan' => $newKet,
            'aktif' => $request->has('aktif') ? 1 : 0,
        ]);

        return redirect()->back()->with('success', 'Data karyawan berhasil diperbarui');
    }

    public function toggleStatus(Request $request, $id)
    {
        $employee = \Modules\Employee\Models\Karyawan::findOrFail($id);
        $employee->update(['aktif' => !$employee->aktif]);

        $statusText = $employee->aktif ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Karyawan berhasil {$statusText}");
    }

    public function show($id)
    {
        $employee = \Modules\Employee\Models\Karyawan::with('jabatan')->findOrFail($id);

        $currentMonth = \Carbon\Carbon::now()->month;
        $currentYear = \Carbon\Carbon::now()->year;

        $allAbsensisMonth = \Modules\Employee\Models\Absensi::where('karyawan_id', $id)
            ->whereMonth('tanggal', $currentMonth)
            ->whereYear('tanggal', $currentYear)
            ->get();

        $unpaidAbsensis = $allAbsensisMonth->where('status_bayar', 0);

        $rekap = [
            'hadir' => $unpaidAbsensis->where('status', 'hadir')->count(),
            'sakit' => $unpaidAbsensis->where('status', 'sakit')->count(),
            'alpha' => $unpaidAbsensis->where('status', 'alpha')->count(),
            'izin' => $unpaidAbsensis->where('status', 'izin')->count(),
        ];

        $estimasi_gaji = $rekap['hadir'] * ($employee->jabatan->gaji_harian ?? 0);

        // Map calendar data
        $kalender_absensi = [];
        foreach ($allAbsensisMonth as $ab) {
            $kalender_absensi[$ab->tanggal->format('Y-m-d')] = [
                'status' => $ab->status,
                'status_bayar' => $ab->status_bayar,
            ];
        }

        return response()->json([
            'employee' => $employee,
            'rekap_absensi' => $rekap,
            'absensi_harian' => $allAbsensisMonth,
            'kalender_absensi' => $kalender_absensi,
            'estimasi_gaji' => $estimasi_gaji,
        ]);
    }

    public function generateSlipGaji(Request $request, $id)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Invalid or expired signature.');
        }

        $employee = \Modules\Employee\Models\Karyawan::with('jabatan')->findOrFail($id);

        if (!$employee->aktif) {
            abort(403, 'Aksi ditolak. Karyawan sudah nonaktif.');
        }

        $tanggalPembayaran = $request->query('tanggal_pembayaran');
        $bonus = $request->query('bonus', 0);
        $potongan = $request->query('potongan', 0);
        $keterangan_potongan = $request->query('keterangan_potongan', null);

        $query = \Modules\Employee\Models\Absensi::where('karyawan_id', $id);

        $currentMonth = \Carbon\Carbon::now()->month;
        $currentYear = \Carbon\Carbon::now()->year;

        if ($tanggalPembayaran) {
            $query->where('tanggal_pembayaran', $tanggalPembayaran);
        } else {
            $query->whereMonth('tanggal', $currentMonth)
                  ->whereYear('tanggal', $currentYear);
        }

        $absensis = $query->get();

        $rekap = [
            'hadir' => $absensis->where('status', 'hadir')->count(),
            'sakit' => $absensis->where('status', 'sakit')->count(),
            'alpha' => $absensis->where('status', 'alpha')->count(),
            'izin' => $absensis->where('status', 'izin')->count(),
        ];

        $gaji_harian = $employee->jabatan->gaji_harian ?? 0;
        $total_gaji_pokok = $rekap['hadir'] * $gaji_harian;
        $total_gaji = $total_gaji_pokok + $bonus - $potongan;

        $potongan_details = [];
        if ($keterangan_potongan) {
            $decoded = json_decode($keterangan_potongan, true);
            if (is_array($decoded)) {
                $potongan_details = $decoded;
            } else {
                $potongan_details = [['keterangan' => $keterangan_potongan, 'nominal' => $potongan]];
            }
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('employee::slip_gaji', compact('employee', 'rekap', 'gaji_harian', 'total_gaji_pokok', 'bonus', 'potongan', 'potongan_details', 'total_gaji', 'currentMonth', 'currentYear', 'tanggalPembayaran'));
        
        return $pdf->download('slip_gaji_' . str_replace(' ', '_', strtolower($employee->nama)) . '.pdf');
    }

    public function storeAbsensi(Request $request, $id)
    {
        $employee = \Modules\Employee\Models\Karyawan::findOrFail($id);
        if (!$employee->aktif) {
            return response()->json(['success' => false, 'message' => 'Aksi ditolak. Karyawan sudah nonaktif.'], 403);
        }

        $request->validate([
            'tanggal' => 'required|date',
            'status' => 'required|in:hadir,izin,sakit,alpha',
            'jam_masuk' => 'nullable|date_format:H:i',
            'jam_keluar' => 'nullable|date_format:H:i',
            'catatan' => 'nullable|string'
        ]);

        $query = \Modules\Employee\Models\Absensi::where('karyawan_id', $id)
            ->where('tanggal', $request->tanggal);

        // Include trashed if SoftDeletes is ever added
        if (method_exists(\Modules\Employee\Models\Absensi::class, 'restore')) {
            $query->withTrashed();
        }

        $absensi = $query->first();

        if ($absensi) {
            if (method_exists($absensi, 'restore') && $absensi->trashed()) {
                $absensi->restore();
            }
            $absensi->update([
                'status' => $request->status,
                'jam_masuk' => $request->jam_masuk,
                'jam_keluar' => $request->jam_keluar,
                'catatan' => $request->catatan,
                'status_bayar' => 0
            ]);
        } else {
            \Modules\Employee\Models\Absensi::create([
                'karyawan_id' => $id,
                'tanggal' => $request->tanggal,
                'status' => $request->status,
                'jam_masuk' => $request->jam_masuk,
                'jam_keluar' => $request->jam_keluar,
                'catatan' => $request->catatan,
                'status_bayar' => 0
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Absensi berhasil disimpan']);
    }

    public function bayarGaji(Request $request, $id)
    {
        $now = \Carbon\Carbon::now();
        
        $employee = \Modules\Employee\Models\Karyawan::with('jabatan')->findOrFail($id);

        if (!$employee->aktif) {
            abort(403, 'Aksi ditolak. Karyawan sudah nonaktif.');
        }
        
        $potongan = $employee->potongan ?? 0;
        $keterangan_potongan = $employee->keterangan_potongan;
        $bonus = $employee->bonus_tetap ?? 0;

        // 1. Dapatkan rekap absensi yang belum dibayar untuk menghitung gaji pokok secara historis
        $unpaidAbsensis = \Modules\Employee\Models\Absensi::where('karyawan_id', $id)
            ->where('status_bayar', 0)
            ->get();
            
        $jumlahHariKerja = $unpaidAbsensis->where('status', 'hadir')->count();
        $gajiHarian = $employee->jabatan->gaji_harian ?? 0;
        $totalGajiPokok = $jumlahHariKerja * $gajiHarian;

        // 2. Simpan transaksi penggajian ke database (Tabel penggajian) agar tercatat historis
        \Illuminate\Support\Facades\DB::table('penggajian')->insert([
            'karyawan_id' => $id,
            'periode_mulai' => \Carbon\Carbon::now()->startOfMonth()->toDateString(),
            'periode_selesai' => \Carbon\Carbon::now()->toDateString(),
            'tanggal_bayar' => $now->toDateString(),
            'jumlah_hari_kerja' => $jumlahHariKerja,
            'total_gaji_pokok' => $totalGajiPokok,
            'bonus_mingguan' => $bonus, // bonus disimpan di kolom bonus_mingguan/bonus_tetap
            'catatan' => $keterangan_potongan ?? 'Pembayaran gaji',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // 3. Update status absensi menjadi dibayarkan
        \Modules\Employee\Models\Absensi::where('karyawan_id', $id)
            ->where('status_bayar', 0)
            ->update([
                'status_bayar' => 1,
                'tanggal_pembayaran' => $now
            ]);

        // 4. Reset potongan & keterangan potongan, namun pertahankan bonus_tetap jika itu adalah nilai bawaan
        $employee->update([
            // 'bonus_tetap' => 0, // Dibiarkan tetap (tidak direset) karena merupakan Bonus Tetap bulanan
            'potongan' => 0,
            'keterangan_potongan' => null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Gaji berhasil dibayarkan',
            'download_url' => \Illuminate\Support\Facades\URL::signedRoute('employee.slipGaji', [
                'id' => $id, 
                'tanggal_pembayaran' => $now->toDateTimeString(),
                'bonus' => $bonus,
                'potongan' => $potongan,
                'keterangan_potongan' => $keterangan_potongan
            ])
        ]);
    }

    public function destroyAbsensi(Request $request, $id)
    {
        $request->validate([
            'tanggal' => 'required|date'
        ]);

        $deleted = \Modules\Employee\Models\Absensi::where('karyawan_id', $id)
            ->whereDate('tanggal', $request->tanggal)
            ->delete();

        if ($deleted) {
            return response()->json(['success' => true, 'message' => 'Data absensi berhasil dihapus']);
        }

        return response()->json(['success' => false, 'message' => 'Data absensi tidak ditemukan'], 404);
    }
}
