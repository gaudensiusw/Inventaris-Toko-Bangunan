<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class FaceAbsensiService
{
    /**
     * @var string
     */
    protected $baseUrl;

    public function __construct()
    {
        // Mendapatkan URL microservice face recognition dari .env atau default ke http://localhost:5000
        $this->baseUrl = env('FACE_RECOGNITION_URL', 'http://localhost:5000');
    }

    /**
     * Mengirim gambar Base64 ke microservice Python untuk pengenalan wajah.
     *
     * @param string $base64Image Gambar dalam format Base64 (data URL atau raw base64)
     * @return array
     */
    public function recognizeFace(string $base64Image): array
    {
        try {
            // Hilangkan header data URL jika ada (misal: "data:image/jpeg;base64,")
            if (str_contains($base64Image, ',')) {
                $base64Image = explode(',', $base64Image)[1];
            }

            // Request POST ke Flask service dengan timeout 10 detik
            $response = Http::timeout(10)->post("{$this->baseUrl}/absensi", [
                'image' => $base64Image
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Face recognition service error response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'status' => 'error',
                'message' => 'Layanan Face Recognition mengembalikan respon kegagalan (' . $response->status() . ').'
            ];

        } catch (Exception $e) {
            Log::error('Gagal menghubungi face recognition service', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'status' => 'error',
                'message' => 'Koneksi ke server Face Recognition gagal. Pastikan service Python di ' . $this->baseUrl . ' sudah berjalan.'
            ];
        }
    }
}
