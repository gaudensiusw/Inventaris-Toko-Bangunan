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

        // 1. Read month and year from request, default to current month and year
        $month = intval($request->query('month', \Carbon\Carbon::now()->month));
        $year = intval($request->query('year', \Carbon\Carbon::now()->year));

        // 2. Calculate Prev/Next month & year using Carbon
        $currentDate = \Carbon\Carbon::createFromDate($year, $month, 1);
        $prevDate = $currentDate->copy()->subMonth();
        $nextDate = $currentDate->copy()->addMonth();

        $prevMonth = $prevDate->month;
        $prevYear = $prevDate->year;
        $nextMonth = $nextDate->month;
        $nextYear = $nextDate->year;

        // 3. Make dynamic statistics label based on the month
        $monthNamesIndo = [
            1 => 'JANUARI', 2 => 'FEBRUARI', 3 => 'MARET', 4 => 'APRIL',
            5 => 'MEI', 6 => 'JUNI', 7 => 'JULI', 8 => 'AGUSTUS',
            9 => 'SEPTEMBER', 10 => 'OKTOBER', 11 => 'NOVEMBER', 12 => 'DESEMBER'
        ];
        $monthNameHeader = $monthNamesIndo[$month] ?? strtoupper($currentDate->translatedFormat('F'));

        $now = \Carbon\Carbon::now();
        if ($month === $now->month && $year === $now->year) {
            $statistikLabel = "STATISTIK KEHADIRAN (DI BULAN INI)";
        } else {
            $statistikLabel = "STATISTIK KEHADIRAN ({$monthNameHeader})";
        }

        $activeEmployeeId = $request->query('id');

        return view('employee::index', compact(
            'employees', 'jabatans', 'status', 'search',
            'month', 'year', 'prevMonth', 'prevYear', 'nextMonth', 'nextYear', 'statistikLabel', 'monthNameHeader', 'activeEmployeeId'
        ));
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
            'keterangan_potongan' => 'nullable|string',
            'foto_wajah' => 'nullable|array|max:3',
            'foto_wajah.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $newPotongan = ($employee->potongan ?? 0) + ($request->potongan ?? 0);
        
        $newKet = $employee->keterangan_potongan;
        if ($request->filled('keterangan_potongan') || ($request->filled('potongan') && $request->potongan > 0)) {
            $existingArr = is_array($employee->keterangan_potongan)
                ? $employee->keterangan_potongan
                : ($employee->keterangan_potongan ? json_decode($employee->keterangan_potongan, true) : []);

            if (!is_array($existingArr)) {
                $existingArr = $employee->keterangan_potongan ? [['keterangan' => (string)$employee->keterangan_potongan, 'nominal' => 0, 'tanggal' => null]] : [];
            }

            $existingArr[] = [
                'keterangan' => $request->keterangan_potongan ?: 'Kasbon',
                'nominal' => (float)($request->potongan ?? 0),
                'tanggal' => now()->toDateString()
            ];

            $newKet = $existingArr;
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

        // Proses unggah foto wajah untuk Face Recognition
        $fotoListBase64 = [];
        if ($request->hasFile('foto_wajah')) {
            $files = $request->file('foto_wajah');
            foreach ($files as $file) {
                if ($file->isValid()) {
                    $fileData = file_get_contents($file->getRealPath());
                    $base64 = 'data:' . $file->getMimeType() . ';base64,' . base64_encode($fileData);
                    $fotoListBase64[] = $base64;
                }
            }
        }

        if (!empty($fotoListBase64)) {
            $faceSuccess = true;
            $faceErrorMsg = '';

            try {
                $faceRecognitionUrl = env('FACE_RECOGNITION_URL', 'http://localhost:5000');
                // Mengirim request POST ke server Python Flask endpoint /registrasi
                $response = \Illuminate\Support\Facades\Http::timeout(15)->post("{$faceRecognitionUrl}/registrasi", [
                    'nama' => $employee->nama,
                    'foto_list' => $fotoListBase64
                ]);

                if ($response->successful()) {
                    $resData = $response->json();
                    if (isset($resData['status']) && $resData['status'] === 'success') {
                        $faceSuccess = true;
                    } else {
                        $faceSuccess = false;
                        $faceErrorMsg = $resData['message'] ?? 'Respon gagal dari server Face Recognition.';
                    }
                } else {
                    $faceSuccess = false;
                    $faceErrorMsg = 'Server Face Recognition mengembalikan respon error (' . $response->status() . ').';
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Gagal terhubung ke face recognition registrasi', [
                    'message' => $e->getMessage()
                ]);
                $faceSuccess = false;
                $faceErrorMsg = 'Tidak dapat terhubung ke server Face Recognition. Pastikan service Python aktif.';
            }

            if ($faceSuccess) {
                return redirect()->back()->with('success', 'Data karyawan berhasil diperbarui dan registrasi wajah berhasil dilakukan.');
            } else {
                return redirect()->back()->with('warning', 'Data karyawan berhasil diperbarui, namun registrasi wajah GAGAL: ' . $faceErrorMsg);
            }
        }

        return redirect()->back()->with('success', 'Data karyawan berhasil diperbarui');
    }

    public function toggleStatus(Request $request, $id)
    {
        $employee = \Modules\Employee\Models\Karyawan::findOrFail($id);
        $employee->update(['aktif' => !$employee->aktif]);

        $statusText = $employee->aktif ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Karyawan berhasil {$statusText}");
    }

    public function show(Request $request, $id)
    {
        $employee = \Modules\Employee\Models\Karyawan::with('jabatan')->findOrFail($id);

        $month = intval($request->query('month', \Carbon\Carbon::now()->month));
        $year = intval($request->query('year', \Carbon\Carbon::now()->year));

        $allAbsensisMonth = \Modules\Employee\Models\Absensi::where('karyawan_id', $id)
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
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

        // Calculate Prev/Next month & year using Carbon
        $currentDate = \Carbon\Carbon::createFromDate($year, $month, 1);
        $prevDate = $currentDate->copy()->subMonth();
        $nextDate = $currentDate->copy()->addMonth();

        $prevMonth = $prevDate->month;
        $prevYear = $prevDate->year;
        $nextMonth = $nextDate->month;
        $nextYear = $nextDate->year;

        // Dynamic statistics label
        $monthNamesIndo = [
            1 => 'JANUARI', 2 => 'FEBRUARI', 3 => 'MARET', 4 => 'APRIL',
            5 => 'MEI', 6 => 'JUNI', 7 => 'JULI', 8 => 'AGUSTUS',
            9 => 'SEPTEMBER', 10 => 'OKTOBER', 11 => 'NOVEMBER', 12 => 'DESEMBER'
        ];
        $monthNameHeader = $monthNamesIndo[$month] ?? strtoupper($currentDate->translatedFormat('F'));

        $now = \Carbon\Carbon::now();
        if ($month === $now->month && $year === $now->year) {
            $statistikLabel = "STATISTIK KEHADIRAN (DI BULAN INI)";
        } else {
            $statistikLabel = "STATISTIK KEHADIRAN ({$monthNameHeader})";
        }

        return response()->json([
            'employee' => $employee,
            'rekap_absensi' => $rekap,
            'absensi_harian' => $allAbsensisMonth,
            'kalender_absensi' => $kalender_absensi,
            'estimasi_gaji' => $estimasi_gaji,
            'month' => $month,
            'year' => $year,
            'prevMonth' => $prevMonth,
            'prevYear' => $prevYear,
            'nextMonth' => $nextMonth,
            'nextYear' => $nextYear,
            'statistikLabel' => $statistikLabel,
            'monthNameHeader' => $monthNameHeader,
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

        $currentMonth = intval($request->query('month', \Carbon\Carbon::now()->month));
        $currentYear = intval($request->query('year', \Carbon\Carbon::now()->year));

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
        $rawPotongan = $keterangan_potongan ?? $employee->keterangan_potongan;

        if ($rawPotongan) {
            if (is_array($rawPotongan)) {
                $potongan_details = $rawPotongan;
            } else {
                $decoded = json_decode($rawPotongan, true);
                if (is_array($decoded)) {
                    $potongan_details = $decoded;
                } else {
                    $potongan_details = [['keterangan' => $rawPotongan, 'nominal' => $potongan, 'tanggal' => null]];
                }
            }
        } elseif ($potongan > 0) {
            $potongan_details = [['keterangan' => 'Kasbon / Potongan', 'nominal' => $potongan, 'tanggal' => null]];
        }

        $monthNamesIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $monthName = $monthNamesIndo[$currentMonth] ?? \Carbon\Carbon::createFromDate($currentYear, $currentMonth, 1)->translatedFormat('F');

        activity('Employee')
            ->performedOn($employee)
            ->causedBy(auth()->user())
            ->event('updated')
            ->log("Mencetak Slip Gaji {$employee->nama} Periode {$monthName} {$currentYear}");

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

        // Log aktivitas absensi – dicatat sebagai UPDATED (membuat atau memperbarui data kehadiran)
        if (function_exists('activity')) {
            $absensiLog = \Modules\Employee\Models\Absensi::where('karyawan_id', $id)
                ->where('tanggal', $request->tanggal)
                ->first();
            if ($absensiLog) {
                activity('Absensi')
                    ->performedOn($absensiLog)
                    ->causedBy(auth()->user())
                    ->event('updated')
                    ->withProperties(['attributes' => $absensiLog->only(['karyawan_id', 'tanggal', 'status', 'jam_masuk', 'jam_keluar', 'catatan'])])
                    ->log("Admin memperbarui data absensi {$employee->nama} tanggal {$request->tanggal}");
            }
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

        // Read active month and year from request, defaulting to current
        $month = intval($request->query('month', \Carbon\Carbon::now()->month));
        $year = intval($request->query('year', \Carbon\Carbon::now()->year));
        
        $potongan = $employee->potongan ?? 0;
        $keterangan_potongan = $employee->keterangan_potongan;
        $bonus = $employee->bonus_tetap ?? 0;

        // 1. Dapatkan rekap absensi yang belum dibayar untuk menghitung gaji pokok secara historis pada bulan/tahun spesifik
        $unpaidAbsensis = \Modules\Employee\Models\Absensi::where('karyawan_id', $id)
            ->where('status_bayar', 0)
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->get();
            
        $jumlahHariKerja = $unpaidAbsensis->where('status', 'hadir')->count();
        $gajiHarian = $employee->jabatan->gaji_harian ?? 0;
        $totalGajiPokok = $jumlahHariKerja * $gajiHarian;

        // 2. Simpan transaksi penggajian ke database (Tabel penggajian) agar tercatat historis
        \Illuminate\Support\Facades\DB::table('penggajian')->insert([
            'karyawan_id' => $id,
            'periode_mulai' => \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth()->toDateString(),
            'periode_selesai' => \Carbon\Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString(),
            'tanggal_bayar' => $now->toDateString(),
            'jumlah_hari_kerja' => $jumlahHariKerja,
            'total_gaji_pokok' => $totalGajiPokok,
            'bonus_mingguan' => $bonus, // bonus disimpan di kolom bonus_mingguan/bonus_tetap
            'catatan' => is_array($keterangan_potongan) ? json_encode($keterangan_potongan) : ($keterangan_potongan ?? 'Pembayaran gaji'),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // 3. Update status absensi menjadi dibayarkan secara spesifik bulan/tahun ini
        \Modules\Employee\Models\Absensi::where('karyawan_id', $id)
            ->where('status_bayar', 0)
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
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

        $monthNamesIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $monthName = $monthNamesIndo[$month] ?? \Carbon\Carbon::createFromDate($year, $month, 1)->translatedFormat('F');

        activity('Employee')
            ->performedOn($employee)
            ->causedBy(auth()->user())
            ->event('updated')
            ->log("Melakukan Pembayaran Gaji {$employee->nama} Periode {$monthName} {$year}");

        return response()->json([
            'success' => true,
            'message' => 'Gaji berhasil dibayarkan',
            'download_url' => \Illuminate\Support\Facades\URL::signedRoute('employee.slipGaji', [
                'id' => $id, 
                'tanggal_pembayaran' => $now->toDateTimeString(),
                'bonus' => $bonus,
                'potongan' => $potongan,
                'keterangan_potongan' => is_array($keterangan_potongan) ? json_encode($keterangan_potongan) : $keterangan_potongan,
                'month' => $month,
                'year' => $year
            ])
        ]);
    }

    public function destroyAbsensi(Request $request, $id)
    {
        $request->validate([
            'tanggal' => 'required|date'
        ]);

        // Ambil data sebelum dihapus untuk keperluan log
        $absensiToDelete = \Modules\Employee\Models\Absensi::where('karyawan_id', $id)
            ->whereDate('tanggal', $request->tanggal)
            ->first();

        if (!$absensiToDelete) {
            return response()->json(['success' => false, 'message' => 'Data absensi tidak ditemukan'], 404);
        }

        // Log aksi DELETED sebelum data dihapus dari database
        if (function_exists('activity')) {
            $employee = \Modules\Employee\Models\Karyawan::find($id);
            activity('Absensi')
                ->performedOn($absensiToDelete)
                ->causedBy(auth()->user())
                ->event('deleted')
                ->withProperties(['old' => $absensiToDelete->only(['karyawan_id', 'tanggal', 'status', 'jam_masuk', 'jam_keluar', 'catatan'])])
                ->log("Admin menghapus data absensi " . ($employee->nama ?? "ID: {$id}") . " tanggal {$request->tanggal}");
        }

        $absensiToDelete->delete();

        return response()->json(['success' => true, 'message' => 'Data absensi berhasil dihapus']);
    }

    public function rekapPeriodeList(Request $request)
    {
        $monthNamesIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $periods = \Modules\Employee\Models\Absensi::selectRaw(
                'MONTH(tanggal) as month, YEAR(tanggal) as year'
            )
            ->groupBy('month', 'year')
            ->orderByRaw('year DESC, month DESC')
            ->get()
            ->map(function ($item) use ($monthNamesIndo) {
                $monthName = $monthNamesIndo[$item->month] ?? 'Bulan';
                return [
                    'month'        => $item->month,
                    'year'         => $item->year,
                    'label'        => "Periode — {$monthName} {$item->year}",
                    'month_name'   => $monthName,
                    'download_url' => route('employee.rekapAbsensi', [
                        'month' => $item->month,
                        'year'  => $item->year,
                    ]),
                ];
            });

        return response()->json(['periods' => $periods]);
    }

    public function exportRekapAbsensi(Request $request)

    {
        $month = intval($request->query('month', \Carbon\Carbon::now()->month));
        $year = intval($request->query('year', \Carbon\Carbon::now()->year));

        $employees = \Modules\Employee\Models\Karyawan::with(['jabatan', 'absensis' => function($query) use ($month, $year) {
            $query->whereMonth('tanggal', $month)
                  ->whereYear('tanggal', $year);
        }])
        ->where('aktif', 1)
        ->get();

        $employees = $employees->map(function($employee) {
            $employee->total_hadir = $employee->absensis->where('status', 'hadir')->count();
            $employee->total_sakit = $employee->absensis->where('status', 'sakit')->count();
            $employee->total_izin = $employee->absensis->where('status', 'izin')->count();
            $employee->total_alpha = $employee->absensis->where('status', 'alpha')->count();
            return $employee;
        });

        $employees = $employees->sortByDesc('total_hadir')->values();

        $monthNamesIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        $currentDate = \Carbon\Carbon::createFromDate($year, $month, 1);
        $monthName = $monthNamesIndo[$month] ?? $currentDate->translatedFormat('F');

        if (function_exists('activity')) {
            activity('Employee')
                ->causedBy(auth()->user())
                ->event('download')
                ->log("Mencetak Rekapitulasi Absensi Periode {$monthName} {$year}");
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('employee::rekap_absensi_pdf', compact('employees', 'month', 'year', 'monthName'));
        
        return $pdf->download("Rekap_Kehadiran_Karyawan_{$monthName}_{$year}.pdf");
    }
}

