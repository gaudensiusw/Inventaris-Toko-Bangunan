<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FaceAbsensiService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class FaceAbsensiController extends Controller
{
    /**
     * @var FaceAbsensiService
     */
    protected $faceAbsensiService;

    public function __construct(FaceAbsensiService $faceAbsensiService)
    {
        $this->faceAbsensiService = $faceAbsensiService;
    }

    /**
     * Menampilkan halaman Blade antarmuka kamera.
     */
    public function index()
    {
        return view('kamera-absensi');
    }

    /**
     * Menerima request AJAX berisi foto Base64, mencocokkan wajah,
     * dan mencatat kehadiran karyawan.
     */
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|string',
        ]);

        // Kirim gambar ke service pengenal wajah
        $result = $this->faceAbsensiService->recognizeFace($request->image);

        // Jika service mengembalikan status error atau tidak dikenal
        if (isset($result['status']) && $result['status'] !== 'success') {
            $msg = $result['message'] ?? 'Gagal memproses verifikasi wajah.';
            $skor = $result['skor'] ?? null;
            
            return response()->json([
                'success' => false,
                'message' => $msg . ($skor ? " (Skor kecocokan: " . ($skor * 100) . "%)" : "")
            ], 400);
        }

        $detectedName = $result['nama'] ?? null;
        if (!$detectedName) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendeteksi nama dari respon face recognition.'
            ], 400);
        }

        // Cari karyawan aktif berdasarkan nama yang dikembalikan (diubah ke lowercase snake_case)
        $employee = \Modules\Employee\Models\Karyawan::where('aktif', 1)
            ->whereRaw("LOWER(REPLACE(nama, ' ', '_')) = ?", [$detectedName])
            ->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => "Wajah teridentifikasi sebagai '" . ucwords(str_replace('_', ' ', $detectedName)) . "', namun karyawan tersebut tidak ditemukan atau berstatus nonaktif di database."
            ], 404);
        }

        $today = Carbon::now('Asia/Jakarta')->toDateString();
        
        // Cek absensi hari ini
        $absensi = \Modules\Employee\Models\Absensi::where('karyawan_id', $employee->id)
            ->whereDate('tanggal', $today)
            ->first();

        if ($absensi && $absensi->status === 'hadir') {
            return response()->json([
                'success' => false,
                'message' => "{$employee->nama} sudah melakukan absensi Hadir hari ini pada pukul " . Carbon::parse($absensi->jam_masuk)->format('H:i') . "."
            ], 422);
        }

        $timeNow = Carbon::now('Asia/Jakarta')->format('H:i:s');

        if ($absensi) {
            // Update status yang ada ke 'hadir'
            $absensi->update([
                'status' => 'hadir',
                'jam_masuk' => $timeNow,
                'catatan' => ($absensi->catatan ? $absensi->catatan . "\n" : "") . "Hadir via Kiosk Face Recognition [" . Carbon::now('Asia/Jakarta')->format('H:i') . "]",
            ]);
        } else {
            // Buat record absensi baru
            $absensi = \Modules\Employee\Models\Absensi::create([
                'karyawan_id' => $employee->id,
                'tanggal' => $today,
                'status' => 'hadir',
                'jam_masuk' => $timeNow,
                'jam_keluar' => null,
                'catatan' => 'Hadir via Kiosk Face Recognition',
                'status_bayar' => 0
            ]);
        }

        // Log aktivitas absensi – dicatat sebagai UPDATED (memperbarui kehadiran karyawan hari ini)
        if (function_exists('activity')) {
            activity('Absensi')
                ->performedOn($absensi)
                ->causedBy(auth()->user())
                ->event('updated')
                ->withProperties(['attributes' => $absensi->only(['karyawan_id', 'tanggal', 'status', 'jam_masuk', 'catatan'])])
                ->log("Absensi Hadir via Face Recognition berhasil untuk {$employee->nama} pukul " . Carbon::now('Asia/Jakarta')->format('H:i'));
        }

        return response()->json([
            'success' => true,
            'message' => "Absensi berhasil dicatat! Selamat bekerja, {$employee->nama}.",
            'employee' => [
                'nama' => $employee->nama,
                'kode_karyawan' => $employee->kode_karyawan,
                'jam_masuk' => Carbon::now('Asia/Jakarta')->format('H:i')
            ],
            'confidence' => $result['skor'] ?? null
        ]);
    }
}
