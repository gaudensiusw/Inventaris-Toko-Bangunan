# 🚀 Panduan Setup Microservice Face Recognition (Absensi IMS)

Repositori ini berisi *microservice* Python (Flask) yang bertugas sebagai "otak" pengenal wajah untuk fitur Kiosk Absensi web Laravel kita. 

Karena *file* model AI dan *database* wajah sengaja tidak di-*push* ke GitHub (masuk `.gitignore`) agar repositori tetap ringan, ada beberapa langkah wajib yang harus dilakukan **setelah melakukan `git pull` pertama kali** di laptop baru.

---

## 🛠️ Tahap Persiapan (Hanya Sekali)

Setelah menarik pembaruan kode terbaru dari GitHub, ikuti 3 langkah instalasi berikut:

### 1. Install Library Python
Pastikan Python (v3.10+) sudah terinstal di laptopmu. Buka terminal, arahkan ke folder `absensi-service`, lalu jalankan:
```bash
python -m pip install -r requirements.txt

2. Masukkan File Model AI Manual
Karena ukurannya besar, file kecerdasan buatannya tidak ada di GitHub.

Download file facenet.tflite (https://drive.google.com/file/d/11vqj_9wiyuH5_SGfx3CoXdXqFxfrkCfe/view?usp=sharing).

Masukkan file facenet.tflite ke dalam folder absensi-service/models/.
(Catatan: Untuk file blaze_face_short_range.tflite akan ter-download otomatis saat Python pertama kali dijalankan).

3. Sinkronisasi Data Wajah (Dataset AI)
AI di laptopmu saat ini belum memiliki ingatan wajah karyawan satupun. Pilih salah satu cara berikut:

Cara Cepat: Download file embeddings.pkl (https://drive.google.com/file/d/1Q5HkcFSy973yrY-sejCPgYZltGmpmHmA/view?usp=sharing), lalu paste ke folder absensi-service/data/.

Cara Sistem: Jalankan Laravel, login sebagai Admin/Owner, masuk ke menu Karyawan -> Edit, lalu upload ulang foto-foto wajah karyawan. AI akan otomatis membuat ingatan barunya.