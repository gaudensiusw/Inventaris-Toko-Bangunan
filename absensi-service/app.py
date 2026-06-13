"""
absensi-service/app.py
======================
Python Face Recognition Microservice
Peran  : "Mata" — hanya mengenali wajah, TIDAK menyimpan data apapun.
Storage: Semua riwayat absensi dikelola sepenuhnya oleh Laravel + MySQL.
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
import tensorflow as tf
import numpy as np
import pickle
import os
import base64
import io
import uuid
from PIL import Image
import mediapipe as mp
from mediapipe.tasks import python as mp_python
from mediapipe.tasks.python import vision as mp_vision
import urllib.request
from sklearn.metrics.pairwise import cosine_similarity

app = Flask(__name__)
CORS(app)

# ─── Path Konfigurasi ─────────────────────────────────────────────────────────
BASE_DIR      = os.path.dirname(__file__)
MODEL_PATH    = os.path.join(BASE_DIR, 'models', 'facenet.tflite')
PKL_PATH      = os.path.join(BASE_DIR, 'data', 'embeddings.pkl')
DATASET_DIR   = os.path.join(BASE_DIR, 'data', 'dataset')
DETECTOR_PATH = os.path.join(BASE_DIR, 'models', 'blaze_face_short_range.tflite')

# ─── Load FaceNet TFLite Model ────────────────────────────────────────────────
print("Loading FaceNet model...")
interpreter = tf.lite.Interpreter(model_path=MODEL_PATH)
interpreter.allocate_tensors()
input_details  = interpreter.get_input_details()
output_details = interpreter.get_output_details()
print("✅ FaceNet loaded")

def get_embedding(pil_image):
    """Ubah wajah (PIL Image 160x160) menjadi vektor 128-dimensi."""
    img = np.array(pil_image.convert('RGB').resize((160, 160))).astype(np.float32)
    img = (img - 127.5) / 128.0  # normalisasi sesuai repo shubham0204
    img = np.expand_dims(img, axis=0)
    interpreter.set_tensor(input_details[0]['index'], img)
    interpreter.invoke()
    return interpreter.get_tensor(output_details[0]['index'])[0]

# ─── Load Embeddings Karyawan ─────────────────────────────────────────────────
def load_embeddings():
    if os.path.exists(PKL_PATH):
        with open(PKL_PATH, 'rb') as f:
            return pickle.load(f)
    return {}

known_embeddings = load_embeddings()
print(f"✅ Embeddings loaded: {len(known_embeddings)} karyawan")

def save_embeddings():
    with open(PKL_PATH, 'wb') as f:
        pickle.dump(known_embeddings, f)

# ─── Face Detector (MediaPipe API baru) ───────────────────────────────────────
if not os.path.exists(DETECTOR_PATH):
    print("Downloading face detector model...")
    urllib.request.urlretrieve(
        'https://storage.googleapis.com/mediapipe-models/face_detector/'
        'blaze_face_short_range/float16/1/blaze_face_short_range.tflite',
        DETECTOR_PATH
    )
    print("✅ Face detector downloaded")

base_options  = mp_python.BaseOptions(model_asset_path=DETECTOR_PATH)
options       = mp_vision.FaceDetectorOptions(
    base_options=base_options,
    min_detection_confidence=0.5
)
face_detector = mp_vision.FaceDetector.create_from_options(options)

def crop_face(pil_image, margin_pct=0.2):
    """
    Deteksi wajah dari gambar dan kembalikan crop 160x160.
    Return None jika tidak ada wajah terdeteksi.
    """
    img_rgb  = np.array(pil_image.convert('RGB'))
    h, w     = img_rgb.shape[:2]
    mp_image = mp.Image(image_format=mp.ImageFormat.SRGB, data=img_rgb)
    results  = face_detector.detect(mp_image)

    if not results.detections:
        return None

    det      = results.detections[0]
    bb       = det.bounding_box
    margin_x = int(bb.width  * margin_pct)
    margin_y = int(bb.height * margin_pct)
    x1 = max(0, bb.origin_x - margin_x)
    y1 = max(0, bb.origin_y - margin_y)
    x2 = min(w, bb.origin_x + bb.width  + margin_x)
    y2 = min(h, bb.origin_y + bb.height + margin_y)

    return pil_image.crop((x1, y1, x2, y2)).resize((160, 160))

def identify_face(query_emb, threshold=0.65):
    """
    Cocokkan embedding dengan semua karyawan.
    Return (nama, skor) — nama = 'Tidak Dikenal' jika skor < threshold.
    """
    best_name  = 'Tidak Dikenal'
    best_score = -1.0

    for nama, embeddings in known_embeddings.items():
        scores    = [cosine_similarity([query_emb], [e])[0][0] for e in embeddings]
        avg_score = float(np.mean(scores))
        if avg_score > best_score:
            best_score = avg_score
            best_name  = nama

    if best_score < threshold:
        return 'Tidak Dikenal', best_score
    return best_name, best_score

def decode_image(data_url):
    """Decode base64 image (data URL atau raw base64) menjadi PIL Image."""
    if ',' in data_url:
        data_url = data_url.split(',')[1]
    img_bytes = base64.b64decode(data_url)
    return Image.open(io.BytesIO(img_bytes)).convert('RGB')


# ══════════════════════════════════════════════════════════════════════════════
# ENDPOINTS
# ══════════════════════════════════════════════════════════════════════════════

@app.route('/health', methods=['GET'])
def health():
    """
    Cek apakah service berjalan dan berapa karyawan yang terdaftar.
    Dipanggil oleh Laravel saat startup untuk memastikan koneksi OK.
    """
    return jsonify({
        "status":   "ok",
        "karyawan": len(known_embeddings),
        "daftar":   list(known_embeddings.keys())
    })


@app.route('/absensi', methods=['POST'])
def absensi():
    """
    ── ENDPOINT UTAMA ──
    Terima gambar dari Laravel, kenali wajah, kembalikan hasilnya.
    Python TIDAK menyimpan apapun — semua pencatatan dilakukan oleh Laravel.

    Request JSON:
        { "image": "<base64 string>" }

    Response sukses:
        { "status": "success", "nama": "budi_santoso", "skor": 0.87 }

    Response wajah tidak dikenali:
        { "status": "unknown", "message": "Wajah tidak dikenali", "skor": 0.42 }

    Response wajah tidak terdeteksi:
        { "status": "error", "message": "Wajah tidak terdeteksi di gambar" }
    """
    data = request.json

    if not data or 'image' not in data:
        return jsonify({"status": "error", "message": "Field 'image' tidak ditemukan"}), 400

    try:
        image = decode_image(data['image'])
    except Exception as e:
        return jsonify({"status": "error", "message": f"Gagal decode gambar: {str(e)}"}), 400

    # Deteksi & crop wajah
    face = crop_face(image)
    if face is None:
        return jsonify({"status": "error", "message": "Wajah tidak terdeteksi di gambar"})

    # Generate embedding & cocokkan
    emb        = get_embedding(face)
    nama, skor = identify_face(emb)

    if nama == 'Tidak Dikenal':
        return jsonify({
            "status":  "unknown",
            "message": "Wajah tidak dikenali",
            "skor":    round(skor, 4)
        })

    # Kembalikan identitas — Laravel yang memutuskan apakah perlu disimpan
    return jsonify({
        "status": "success",
        "nama":   nama,
        "skor":   round(skor, 4)
    })


@app.route('/registrasi', methods=['POST'])
def registrasi():
    """
    Daftarkan karyawan baru dengan memproses beberapa foto menjadi embedding.
    Foto fisik disimpan di data/dataset/<nama>/ sebagai backup.
    Embedding disimpan ke data/embeddings.pkl dan langsung aktif.

    Request JSON:
        {
            "nama": "budi santoso",
            "foto_list": ["<base64_1>", "<base64_2>", ...]
        }

    Response:
        { "status": "success", "nama": "budi_santoso", "jumlah_embedding": 3 }
    """
    global known_embeddings

    data = request.json
    if not data or 'nama' not in data or 'foto_list' not in data:
        return jsonify({"status": "error", "message": "Field 'nama' dan 'foto_list' wajib diisi"}), 400

    nama      = data['nama'].strip().lower().replace(' ', '_')
    foto_list = data['foto_list']

    if not foto_list:
        return jsonify({"status": "error", "message": "foto_list kosong"}), 400

    # Buat folder backup foto
    folder = os.path.join(DATASET_DIR, nama)
    os.makedirs(folder, exist_ok=True)

    new_embeddings = []
    gagal          = 0

    for foto_b64 in foto_list:
        try:
            img  = decode_image(foto_b64)
            face = crop_face(img)
            if face is None:
                gagal += 1
                continue
            # Simpan foto fisik sebagai backup dataset
            img.save(os.path.join(folder, f'{uuid.uuid4().hex}.jpg'))
            # Generate embedding
            new_embeddings.append(get_embedding(face))
        except Exception:
            gagal += 1
            continue

    if not new_embeddings:
        return jsonify({
            "status":  "error",
            "message": f"Tidak ada wajah terdeteksi dari {len(foto_list)} foto yang dikirim"
        })

    # Update embeddings in-memory dan simpan ke file
    known_embeddings[nama] = new_embeddings
    save_embeddings()

    return jsonify({
        "status":           "success",
        "nama":             nama,
        "jumlah_embedding": len(new_embeddings),
        "foto_gagal":       gagal
    })


@app.route('/karyawan', methods=['GET'])
def daftar_karyawan():
    """Daftar semua karyawan yang sudah terdaftar di embeddings.pkl."""
    return jsonify({
        "total":    len(known_embeddings),
        "karyawan": list(known_embeddings.keys())
    })


@app.route('/karyawan/<nama>', methods=['DELETE'])
def hapus_karyawan(nama):
    """
    Hapus embedding karyawan (misal saat resign).
    Dipanggil oleh Laravel saat admin menghapus karyawan.
    """
    global known_embeddings
    nama = nama.strip().lower().replace(' ', '_')

    if nama not in known_embeddings:
        return jsonify({"status": "error", "message": f"'{nama}' tidak ditemukan"}), 404

    del known_embeddings[nama]
    save_embeddings()

    return jsonify({"status": "success", "message": f"'{nama}' berhasil dihapus"})


# ─── Entry Point ──────────────────────────────────────────────────────────────
if __name__ == '__main__':
    os.makedirs(os.path.join(BASE_DIR, 'models'), exist_ok=True)
    os.makedirs(os.path.join(BASE_DIR, 'data'), exist_ok=True)
    os.makedirs(DATASET_DIR, exist_ok=True)
    app.run(host='0.0.0.0', port=5000, debug=True)