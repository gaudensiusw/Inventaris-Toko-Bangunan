@extends('layouts.app')

@section('title', 'Presensi Wajah - Toko Rajawali')
@section('header_title', 'Presensi Pengenalan Wajah')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Header info -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center bg-white p-6 rounded-2xl border border-slate-200 shadow-sm gap-4">
        <div>
            <h3 class="text-lg font-bold text-slate-800">Terminal Presensi Mandiri</h3>
            <p class="text-sm text-slate-500 mt-1">Posisikan wajah Anda tepat di dalam area pemandu kamera untuk melakukan absensi otomatis.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="flex h-3 w-3 relative">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
            </span>
            <span class="text-xs font-bold text-slate-600 uppercase tracking-wider bg-slate-100 px-3 py-1.5 rounded-full border border-slate-200" id="status-koneksi">
                Menghubungkan ke Kamera...
            </span>
        </div>
    </div>

    <!-- Main Workspace -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Left: Webcam Container (7 cols) -->
        <div class="lg:col-span-7 flex flex-col items-center">
            <div class="relative w-full aspect-[4/3] bg-slate-900 rounded-3xl overflow-hidden border border-slate-700 shadow-2xl group">
                
                <!-- Video Feed -->
                <video id="webcam" autoplay playsinline muted class="w-full h-full object-cover transform -scale-x-100"></video>
                
                <!-- High-tech Overlay scanning guides -->
                <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                    
                    <!-- Scanner Box guide -->
                    <div class="relative w-64 h-64 md:w-80 md:h-80 border-2 border-dashed border-blue-500/40 rounded-full flex items-center justify-center animate-pulse">
                        <!-- Neon Corner Brackets -->
                        <div class="absolute top-0 left-0 w-8 h-8 border-t-4 border-l-4 border-blue-500 rounded-tl-3xl"></div>
                        <div class="absolute top-0 right-0 w-8 h-8 border-t-4 border-r-4 border-blue-500 rounded-tr-3xl"></div>
                        <div class="absolute bottom-0 left-0 w-8 h-8 border-b-4 border-l-4 border-blue-500 rounded-bl-3xl"></div>
                        <div class="absolute bottom-0 right-0 w-8 h-8 border-b-4 border-r-4 border-blue-500 rounded-br-3xl"></div>
                        
                        <!-- Tracking grid lines inside -->
                        <div class="absolute inset-8 rounded-full border border-blue-400/20 bg-blue-500/5 backdrop-blur-[1px]"></div>
                    </div>
                    
                    <!-- Scanning Horizontal Red Line Laser effect -->
                    <div id="laser-line" class="absolute w-full h-[2px] bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.8)] opacity-0"></div>
                </div>

                <!-- Snapshot Overlay (Flash effect) -->
                <div id="flash-effect" class="absolute inset-0 bg-white opacity-0 transition-opacity duration-100 pointer-events-none"></div>

                <!-- Hidden Canvas for frame snapshot -->
                <canvas id="snapshot-canvas" class="hidden"></canvas>
            </div>
            
            <!-- Controls below camera -->
            <div class="mt-4 flex gap-3 w-full">
                <button id="btn-snap" class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-2xl py-4 flex items-center justify-center gap-3 font-bold transition-all shadow-lg shadow-blue-500/20 hover:shadow-blue-500/30 transform hover:-translate-y-0.5 active:translate-y-0">
                    <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    Verifikasi Wajah
                </button>
                <button id="btn-camera-toggle" class="bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 rounded-2xl px-4 flex items-center justify-center transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18.5"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Right: Status Console & Results (5 cols) -->
        <div class="lg:col-span-5 flex flex-col gap-6">
            
            <!-- Live Status & Logs -->
            <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm flex-1 flex flex-col justify-between">
                <div>
                    <h4 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4">Konsol Deteksi</h4>
                    
                    <!-- Status Cards dynamic -->
                    <div id="status-card" class="bg-slate-50 border border-slate-100 rounded-2xl p-6 transition-all duration-300 flex flex-col items-center justify-center min-h-[200px]">
                        <div id="status-icon" class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mb-4 ring-8 ring-blue-50/50">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h5 id="status-title" class="font-bold text-slate-800 text-lg">Siap Memindai</h5>
                        <p id="status-desc" class="text-slate-500 text-sm text-center mt-2 px-4">Kamera aktif. Silakan hadapkan wajah Anda dan tekan tombol Verifikasi.</p>
                    </div>

                    <!-- Snapshot preview of successful scan -->
                    <div id="success-preview" class="hidden mt-6 bg-slate-50 border border-slate-200 rounded-2xl p-4 flex items-center gap-4">
                        <div class="w-20 h-20 rounded-xl overflow-hidden border-2 border-white shadow bg-slate-200 flex-shrink-0">
                            <img id="preview-img" src="" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-800 uppercase tracking-wide">Terverifikasi</span>
                            <h6 id="preview-name" class="font-bold text-slate-800 truncate text-base mt-1">-</h6>
                            <p id="preview-meta" class="text-slate-500 text-xs mt-0.5">-</p>
                        </div>
                    </div>
                </div>

                <!-- Logs console feed -->
                <div class="mt-6 border-t border-slate-100 pt-6">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-2">Riwayat Log Sesi</span>
                    <div id="logs-console" class="h-32 overflow-y-auto bg-slate-950 text-slate-400 font-mono text-xs rounded-xl p-3 space-y-1.5 custom-scrollbar">
                        <div class="text-blue-400">> Menginisialisasi sistem...</div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // State management
    let localStream = null;
    const video = document.getElementById('webcam');
    const canvas = document.getElementById('snapshot-canvas');
    const btnSnap = document.getElementById('btn-snap');
    const btnToggle = document.getElementById('btn-camera-toggle');
    const laserLine = document.getElementById('laser-line');
    const flashEffect = document.getElementById('flash-effect');
    const statusConnection = document.getElementById('status-koneksi');
    const logsConsole = document.getElementById('logs-console');
    
    // Status UI Elements
    const statusCard = document.getElementById('status-card');
    const statusIcon = document.getElementById('status-icon');
    const statusTitle = document.getElementById('status-title');
    const statusDesc = document.getElementById('status-desc');
    
    // Preview Elements
    const successPreview = document.getElementById('success-preview');
    const previewImg = document.getElementById('preview-img');
    const previewName = document.getElementById('preview-name');
    const previewMeta = document.getElementById('preview-meta');

    // Web Audio Synthesizer for high-end audio feedback
    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    
    function playSound(type) {
        try {
            // Resume context if suspended (browser security)
            if (audioCtx.state === 'suspended') {
                audioCtx.resume();
            }

            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.connect(gain);
            gain.connect(audioCtx.destination);

            if (type === 'success') {
                // Happy high-pitched chime
                const now = audioCtx.currentTime;
                osc.frequency.setValueAtTime(523.25, now); // C5
                osc.frequency.setValueAtTime(659.25, now + 0.1); // E5
                osc.frequency.setValueAtTime(783.99, now + 0.2); // G5
                osc.frequency.setValueAtTime(1046.50, now + 0.3); // C6
                
                gain.gain.setValueAtTime(0.3, now);
                gain.gain.exponentialRampToValueAtTime(0.01, now + 0.5);
                
                osc.start(now);
                osc.stop(now + 0.5);
            } else if (type === 'error') {
                // Low buzz tone
                const now = audioCtx.currentTime;
                osc.type = 'sawtooth';
                osc.frequency.setValueAtTime(120, now); // Low frequency
                osc.frequency.linearRampToValueAtTime(80, now + 0.3);
                
                gain.gain.setValueAtTime(0.2, now);
                gain.gain.exponentialRampToValueAtTime(0.01, now + 0.35);
                
                osc.start(now);
                osc.stop(now + 0.35);
            } else if (type === 'snap') {
                // Click camera shutter sound
                const now = audioCtx.currentTime;
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(1000, now);
                osc.frequency.exponentialRampToValueAtTime(10, now + 0.08);
                
                gain.gain.setValueAtTime(0.4, now);
                gain.gain.exponentialRampToValueAtTime(0.01, now + 0.08);
                
                osc.start(now);
                osc.stop(now + 0.08);
            }
        } catch (e) {
            console.error('Audio feedback error:', e);
        }
    }

    // Add log entry helper
    function addLog(message, colorClass = 'text-slate-400') {
        const time = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        const logItem = document.createElement('div');
        logItem.className = colorClass;
        logItem.innerHTML = `<span class="text-slate-600">[${time}]</span> ${message}`;
        logsConsole.appendChild(logItem);
        logsConsole.scrollTop = logsConsole.scrollHeight;
    }

    // Initialize Camera Feed
    async function startCamera() {
        addLog('Mengakses perangkat kamera...');
        statusConnection.textContent = 'Menghubungkan...';
        statusConnection.className = 'text-xs font-bold text-amber-600 uppercase tracking-wider bg-amber-50 px-3 py-1.5 rounded-full border border-amber-200';

        try {
            const constraints = {
                video: {
                    width: { ideal: 640 },
                    height: { ideal: 480 },
                    facingMode: "user"
                },
                audio: false
            };

            localStream = await navigator.mediaDevices.getUserMedia(constraints);
            video.srcObject = localStream;
            
            // Wait for video metadata to load
            video.onloadedmetadata = () => {
                addLog('Kamera aktif - 640x480px feed.', 'text-green-400');
                statusConnection.textContent = 'Kamera Aktif';
                statusConnection.className = 'text-xs font-bold text-green-600 uppercase tracking-wider bg-green-50 px-3 py-1.5 rounded-full border border-green-200';
            };
        } catch (err) {
            console.error("Camera access error:", err);
            addLog('Akses kamera ditolak atau tidak ditemukan!', 'text-red-400');
            statusConnection.textContent = 'Kamera Gagal';
            statusConnection.className = 'text-xs font-bold text-red-600 uppercase tracking-wider bg-red-50 px-3 py-1.5 rounded-full border border-red-200';
            
            // Update UI status
            updateStatus('error', 'Kamera Gagal', 'Sistem tidak dapat mengakses kamera Anda. Pastikan izin kamera telah diberikan di browser Anda.');
            playSound('error');
        }
    }

    // Stop Camera Feed
    function stopCamera() {
        if (localStream) {
            localStream.getTracks().forEach(track => track.stop());
            addLog('Kamera dihentikan.');
        }
    }

    // Update Status UI
    function updateStatus(type, title, description) {
        statusCard.className = `rounded-2xl p-6 transition-all duration-300 flex flex-col items-center justify-center min-h-[200px] border `;
        
        if (type === 'scanning') {
            statusCard.classList.add('bg-blue-50/50', 'border-blue-200', 'animate-pulse');
            statusIcon.className = 'w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mb-4 ring-8 ring-blue-100/50';
            statusIcon.innerHTML = `
                <svg class="w-8 h-8 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            `;
        } else if (type === 'success') {
            statusCard.classList.add('bg-green-50', 'border-green-200');
            statusIcon.className = 'w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-4 ring-8 ring-green-100/50';
            statusIcon.innerHTML = `
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                </svg>
            `;
        } else if (type === 'error') {
            statusCard.classList.add('bg-red-50', 'border-red-200');
            statusIcon.className = 'w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mb-4 ring-8 ring-red-100/50';
            statusIcon.innerHTML = `
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            `;
        } else {
            // ready/normal
            statusCard.classList.add('bg-slate-50', 'border-slate-100');
            statusIcon.className = 'w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mb-4 ring-8 ring-blue-50/50';
            statusIcon.innerHTML = `
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
            `;
        }

        statusTitle.textContent = title;
        statusDesc.textContent = description;
    }

    // Capture snapshot & verify via AJAX
    async function verifyFace() {
        if (!localStream) {
            addLog('Aksi ditolak: Kamera tidak aktif.', 'text-red-400');
            return;
        }

        playSound('snap');

        // Shutter flash animation effect
        flashEffect.classList.remove('opacity-0');
        flashEffect.classList.add('opacity-80');
        setTimeout(() => {
            flashEffect.classList.remove('opacity-80');
            flashEffect.classList.add('opacity-0');
        }, 100);

        // Laser scan laser line animation effect
        laserLine.className = 'absolute w-full h-[2px] bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.8)] opacity-100 top-0 transition-all duration-1000 ease-in-out';
        setTimeout(() => {
            laserLine.style.top = '100%';
        }, 50);
        setTimeout(() => {
            laserLine.className = 'absolute w-full h-[2px] bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.8)] opacity-0';
            laserLine.style.top = '0';
        }, 1050);

        addLog('Mengambil cuplikan wajah (snapshot)...');
        
        // Draw frame to hidden canvas
        const width = video.videoWidth || 640;
        const height = video.videoHeight || 480;
        canvas.width = width;
        canvas.height = height;
        
        const ctx = canvas.getContext('2d');
        // Mirror snapshot because video feed is mirrored
        ctx.translate(width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(video, 0, 0, width, height);
        
        // Convert to Base64
        const dataUrl = canvas.toDataURL('image/jpeg', 0.85);

        // Update UI to scanning state
        updateStatus('scanning', 'Menganalisis Wajah...', 'Mencocokkan biometrik wajah dengan database karyawan...');
        btnSnap.disabled = true;
        btnSnap.classList.add('opacity-50', 'cursor-not-allowed');
        successPreview.classList.add('hidden');

        addLog('Mengirim data ke server Laravel...');

        try {
            const response = await fetch('{{ route("absensi.kamera.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ image: dataUrl })
            });

            const result = await response.json();

            if (response.ok && result.success) {
                // Success attendance logged
                playSound('success');
                updateStatus('success', 'Verifikasi Berhasil', result.message);
                addLog(`Presensi berhasil: ${result.employee.nama} (${result.employee.kode_karyawan})`, 'text-green-400');

                // Display success preview
                previewImg.src = dataUrl;
                previewName.textContent = result.employee.nama;
                previewMeta.textContent = `${result.employee.kode_karyawan} • Masuk: ${result.employee.jam_masuk}`;
                successPreview.classList.remove('hidden');
            } else {
                // Error response from Laravel
                playSound('error');
                updateStatus('error', 'Verifikasi Gagal', result.message || 'Wajah tidak dikenali.');
                addLog(`Gagal: ${result.message}`, 'text-red-400');
            }
        } catch (error) {
            console.error('Fetch error:', error);
            playSound('error');
            updateStatus('error', 'Koneksi Bermasalah', 'Gagal berkomunikasi dengan server aplikasi.');
            addLog('Kesalahan sistem: Gagal mengirim request.', 'text-red-400');
        } finally {
            btnSnap.disabled = false;
            btnSnap.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }

    // Toggle Camera
    btnToggle.addEventListener('click', () => {
        if (localStream) {
            stopCamera();
            localStream = null;
            video.srcObject = null;
            statusConnection.textContent = 'Kamera Nonaktif';
            statusConnection.className = 'text-xs font-bold text-slate-500 uppercase tracking-wider bg-slate-100 px-3 py-1.5 rounded-full border border-slate-200';
            updateStatus('ready', 'Kamera Dimatikan', 'Klik ikon kamera atau refresh halaman untuk menyalakan kembali feed kamera.');
            addLog('Aliran kamera dimatikan oleh pengguna.', 'text-amber-400');
        } else {
            startCamera();
        }
    });

    btnSnap.addEventListener('click', verifyFace);

    // Auto-start camera when page loads
    document.addEventListener('DOMContentLoaded', () => {
        startCamera();
    });

    // Cleanup camera when leaving the page
    window.addEventListener('beforeunload', () => {
        stopCamera();
    });
</script>
@endpush
