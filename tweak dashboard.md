Saya ingin lanjut menyempurnakan ADMIN DASHBOARD Laravel Cabai AI yang sekarang.

PENTING:
- Jangan buat project baru.
- Jangan ubah FastAPI.
- Jangan ubah Flutter.
- Jangan merusak API existing.
- Jangan merusak Sanctum/login/logout.
- Jangan ubah endpoint mobile yang sudah berjalan kecuali benar-benar diperlukan.
- Pertahankan Laravel 12.
- Gunakan UI admin existing yang sudah dibuat dengan style modern seperti referensi sebelumnya.
- Fokus pada fitur ADMIN dan monitoring.
- Inspect struktur project/database/controller/model existing terlebih dahulu sebelum mengubah apa pun.
- Jika perlu menambah kolom database, buat migration BARU. Jangan edit migration lama yang sudah pernah dijalankan.

==================================================
1. USER MANAGEMENT
==================================================

Tambahkan menu admin:

Users

Route contoh:
GET /admin/users
GET /admin/users/{user}
GET /admin/users/{user}/edit
PUT/PATCH /admin/users/{user}

Fitur halaman users:

- daftar user
- nama
- email
- role
- status aktif
- tanggal registrasi
- jumlah deteksi
- terakhir melakukan deteksi jika mudah
- tombol detail/edit

Tambahkan search:
- nama
- email

Filter:
- semua
- admin
- user
- aktif
- nonaktif

Admin dapat:
- mengubah nama
- mengubah email
- mengubah role user/admin
- aktif/nonaktifkan akun
- reset password jika diperlukan

PENTING:
Endpoint POST /api/register tetap HARUS selalu membuat:

role = user

User mobile tidak boleh menentukan role sendiri.

Jangan izinkan admin secara tidak sengaja menonaktifkan/menghapus dirinya sendiri jika itu menyebabkan tidak ada admin aktif.

Jika field status user belum ada, tambahkan misalnya:

is_active boolean default true

Pastikan login API menolak akun nonaktif dengan response yang jelas.

==================================================
2. DETECTION REVIEW / VERIFIKASI ADMIN
==================================================

Ini fitur utama.

Tambahkan mekanisme review terhadap hasil diagnosis AI.

Tambahkan field detection jika belum ada:

review_status nullable string
reviewed_by nullable foreignId users
reviewed_at nullable timestamp
review_notes nullable text
corrected_disease_id nullable foreignId diseases

Gunakan status:

pending
verified
corrected
rejected

Default detection baru:

review_status = pending

Jangan menghapus atau menimpa hasil AI asli.

Contoh:

AI Prediction:
Cercospora Leaf Spot
92.4%

Admin correction:
Bacterial Spot

Maka:
- prediction AI asli tetap tersimpan
- disease_id AI asli tetap dapat dipertahankan sesuai struktur existing
- corrected_disease_id = penyakit hasil koreksi admin
- review_status = corrected
- reviewed_by = admin
- reviewed_at = timestamp
- review_notes = alasan koreksi

Tujuannya agar hasil AI asli dapat dibandingkan dengan hasil verifikasi manusia untuk evaluasi/retraining model nanti.

==================================================
3. DETECTION DETAIL PAGE
==================================================

Upgrade halaman detail detection.

Bagian utama:

FOTO
- foto asli
- overlay bbox jika object_detection tersedia
- jangan error jika bbox null

HASIL AI
- AI class
- nama penyakit
- confidence
- classification_source
- valid_input
- needs_retake

VALIDATOR
- method
- positive_score
- best_label
- threshold

OBJECT DETECTION
- class_name
- confidence
- bbox
- image_width
- image_height

TOP PREDICTIONS
Tampilkan Top 3:
- class
- confidence
- progress bar

ADMIN REVIEW
Tampilkan:

Review Status:
- Belum Ditinjau
- Terverifikasi
- Dikoreksi
- Ditolak

Form admin:
- status review
- penyakit hasil koreksi
- catatan

Jika status:
verified
→ corrected_disease_id boleh null

Jika:
corrected
→ corrected_disease_id wajib

Jika:
rejected
→ beri catatan alasan jika memungkinkan

Tampilkan:
- reviewer
- waktu review
- catatan

==================================================
4. MENU "PERLU DITINJAU"
==================================================

Tambahkan menu/sidebar:

Perlu Ditinjau

atau submenu pada Deteksi.

Tampilkan detection dengan kondisi prioritas:

review_status = pending

dan terutama:

confidence < 0.70
OR needs_retake = true
OR valid_input = false

Berikan badge/priority:

HIGH
- invalid_input
- confidence sangat rendah

MEDIUM
- needs_retake
- confidence < 70%

NORMAL
- belum direview tetapi confidence tinggi

Kolom:
- thumbnail
- user
- hasil AI
- confidence
- reason
- status
- tanggal
- tombol Review

Tambahkan filter:

Semua
Belum Ditinjau
Terverifikasi
Dikoreksi
Ditolak
Invalid
Needs Retake
Low Confidence

==================================================
5. DASHBOARD ADMIN
==================================================

Upgrade dashboard agar menampilkan:

STATISTIC CARDS

- Total User
- Total Deteksi
- Deteksi Hari Ini
- Invalid Input
- Needs Retake
- Belum Ditinjau
- Dikoreksi Admin
- Average Confidence

Tambahkan section:

DETEKSI TERBARU

PENYAKIT PALING SERING

Hanya hitung:
valid_input = true
AND disease_id IS NOT NULL

Jangan hitung invalid input sebagai penyakit.

PERLU DITINJAU

Tampilkan 5 detection terbaru yang:
review_status = pending
OR needs_retake = true
OR valid_input = false
OR confidence < 70%

AI ENGINE

Tampilkan:
- FastAPI Online/Offline
- Validator: CLIP
- Detector: YOLOE
- Classifier: EfficientNet V4
- Classes: 6
- last check
- response time jika mudah

Jika FastAPI mati:
dashboard tetap HARUS terbuka normal.

==================================================
6. MASTER PENYAKIT
==================================================

Pastikan Disease CRUD memiliki data:

- ai_class_name
- name
- scientific_name
- description
- symptoms
- cause
- treatment
- prevention
- is_healthy
- is_active

Jika beberapa field sudah ada, gunakan existing.

Jangan membuat duplikat.

Tambahkan jumlah detection pada index.

Tampilkan dengan jelas:

AI Class:
Healthy Leaf

Nama:
Daun Sehat

AI class penting karena digunakan untuk mapping output EfficientNet.

==================================================
7. LAPORAN / EXPORT
==================================================

Tambahkan halaman:

Laporan

Fitur filter:

- tanggal mulai
- tanggal akhir
- penyakit
- user
- valid/invalid
- review status
- classification source

Tampilkan summary:

Total Deteksi
Valid
Invalid
Needs Retake
Healthy
Disease
Average Confidence

Tambahkan export minimal:

CSV

Jika project sudah memiliki package Excel yang cocok, boleh gunakan XLSX.
Jangan install dependency berat jika tidak diperlukan.

Export harus mengikuti filter yang sedang aktif.

==================================================
8. AUDIT LOG ADMIN
==================================================

Tambahkan audit log sederhana untuk aktivitas penting admin.

Catat minimal:

- admin login jika existing mudah
- edit user
- aktif/nonaktif user
- edit disease
- review detection
- koreksi detection

Data minimal:

user_id
action
subject_type
subject_id
description
created_at

Jangan menyimpan password/token/private credential.

Tambahkan halaman:

/admin/audit-logs

read-only.

==================================================
9. NAVIGATION
==================================================

Sidebar admin menjadi kira-kira:

Dashboard

Monitoring
- Deteksi
- Perlu Ditinjau

Master Data
- Penyakit
- Users

Laporan

System
- AI Service
- Audit Log

Gunakan menu active state yang jelas.

Pertahankan layout/style admin sekarang:
- clean
- modern
- white sidebar
- light gray content
- emerald accent
- rounded cards
- subtle border/shadow
- responsive

==================================================
10. API MOBILE HARUS TETAP AMAN
==================================================

Jangan merusak endpoint:

POST /api/register
POST /api/login
POST /api/logout
GET  /api/me

GET  /api/diseases
GET  /api/diseases/{disease}

POST /api/detections
GET  /api/detections
GET  /api/detections/{detection}

Flutter tetap hanya mengakses Laravel.

Laravel tetap yang mengakses FastAPI.

Jika perlu menambahkan informasi review pada GET detection, lakukan secara backward-compatible.

Contoh boleh menambahkan:

"review": {
    "status": "verified",
    "corrected_disease": null
}

tetapi jangan menghapus field existing.

==================================================
11. SECURITY
==================================================

Pastikan semua /admin/* hanya dapat diakses role admin.

User biasa tidak boleh membuka:

/admin/users
/admin/diseases
/admin/reviews
/admin/reports
/admin/audit-logs

Pastikan:
- CSRF tetap aktif
- Sanctum tetap bekerja
- mass assignment aman
- role tidak dapat diubah lewat API register
- password tetap hashed
- akun nonaktif tidak dapat login
- jangan expose token FastAPI atau secret .env

==================================================
12. DATABASE
==================================================

Sebelum membuat migration, inspect schema existing.

Jangan menambahkan field yang ternyata sudah ada.

Jika diperlukan, buat migration baru untuk field seperti:

users:
is_active

detections:
review_status
reviewed_by
reviewed_at
review_notes
corrected_disease_id

Tambahkan foreign key dan nullable behavior yang aman.

Gunakan enum/string sesuai style project existing.
Jangan over-engineer.

==================================================
13. TEST
==================================================

Tambahkan/update tests minimal untuk:

- user biasa tidak bisa akses admin
- admin bisa akses admin
- API register selalu role=user
- nonaktif user tidak bisa login
- detection review verified
- detection review corrected
- corrected_disease_id tersimpan
- hasil AI original tidak berubah setelah koreksi
- dashboard tidak error jika FastAPI offline
- invalid detection tidak dihitung sebagai penyakit

Setelah implementasi jalankan:

php artisan migrate
php artisan optimize:clear
php artisan test
npm run build
php artisan route:list

==================================================
14. PENTING: JANGAN OVER-REFACTOR
==================================================

Jangan rewrite seluruh project.

Gunakan model/controller/service/view existing sebanyak mungkin.

Tambahkan fitur secara incremental.

Prioritas:
1. stability
2. existing API compatibility
3. admin review
4. user management
5. reports
6. audit log
7. UI polish

==================================================
15. LAPORAN AKHIR
==================================================

Setelah selesai laporkan:

1. migration yang dibuat
2. model yang diubah
3. controller/service yang dibuat/diubah
4. route baru
5. view baru
6. fitur User Management
7. fitur Detection Review
8. fitur Perlu Ditinjau
9. fitur Report/Export
10. Audit Log
11. hasil php artisan test
12. hasil npm run build
13. perubahan pada API jika ada
14. TODO yang masih tersisa

Jangan berhenti hanya pada analisis.
Implementasikan sampai seluruh fitur utama selesai dan aplikasi tetap berjalan.