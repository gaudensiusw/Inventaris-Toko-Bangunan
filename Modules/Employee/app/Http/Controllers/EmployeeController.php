<?php

namespace Modules\Employee\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = \Modules\Employee\Models\Karyawan::with('jabatan')->get();
        $jabatans = \Modules\Employee\Models\Jabatan::all();
        return view('employee::index', compact('employees', 'jabatans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'jabatan_id' => 'required|exists:jabatan,id',
            'tanggal_masuk' => 'required|date',
        ]);

        \Modules\Employee\Models\Karyawan::create([
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
        ]);

        $employee->update([
            'nama' => $request->nama,
            'jabatan_id' => $request->jabatan_id,
            'tanggal_masuk' => $request->tanggal_masuk,
            'no_hp' => $request->no_hp,
            'email' => $request->email,
            'alamat' => $request->alamat,
            'bonus_tetap' => $request->bonus_tetap ?? 0,
            'aktif' => $request->has('aktif') ? 1 : 0,
        ]);

        return redirect()->back()->with('success', 'Data karyawan berhasil diperbarui');
    }

    public function destroy($id)
    {
        $employee = \Modules\Employee\Models\Karyawan::findOrFail($id);
        $employee->update(['aktif' => 0]);

        return redirect()->back()->with('success', 'Karyawan dinonaktifkan');
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
        $employee = \Modules\Employee\Models\Karyawan::with('jabatan')->findOrFail($id);

        $tanggalPembayaran = $request->query('tanggal_pembayaran');

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
        $bonus = $employee->bonus_tetap ?? 0; // Use dynamic bonus from db
        $total_gaji = $total_gaji_pokok + $bonus;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('employee::slip_gaji', compact('employee', 'rekap', 'gaji_harian', 'total_gaji_pokok', 'bonus', 'total_gaji', 'currentMonth', 'currentYear', 'tanggalPembayaran'));
        
        return $pdf->download('slip_gaji_' . str_replace(' ', '_', strtolower($employee->nama)) . '.pdf');
    }

    public function storeAbsensi(Request $request, $id)
    {
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
        
        \Modules\Employee\Models\Absensi::where('karyawan_id', $id)
            ->where('status_bayar', 0)
            ->update([
                'status_bayar' => 1,
                'tanggal_pembayaran' => $now
            ]);

        \Modules\Employee\Models\Karyawan::where('id', $id)->update(['bonus_tetap' => 0]);

        return response()->json([
            'success' => true,
            'message' => 'Gaji berhasil dibayarkan',
            'download_url' => route('employee.slipGaji', ['id' => $id, 'tanggal_pembayaran' => $now->toDateTimeString()])
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
