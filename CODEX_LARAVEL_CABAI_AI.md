# CODEX / CURSOR AGENT TASK — Laravel 12 Backend + Dashboard Cabai AI

## Tujuan

Buat aplikasi **Laravel 12** sebagai backend utama, API untuk Flutter, dashboard admin, database penyakit, penyimpanan riwayat deteksi, dan gateway ke FastAPI AI Service yang sudah berjalan lokal di:

```text
http://127.0.0.1:8001
```

Arsitektur akhir:

```text
Flutter Mobile
      |
      | REST API + Bearer Token
      v
Laravel 12 :8000
      |
      +------ MySQL
      +------ Storage foto
      +------ FastAPI AI :8001
                  |
                  v
         chili_model_clean.keras
```

Flutter **hanya** berkomunikasi dengan Laravel. FastAPI tetap internal.

---

## Environment Lokal

```text
OS      : Windows 11
PHP     : 8.2
Laravel : 12
Node    : v24.13.1
NPM     : 11.8.0
Database: MySQL
Laravel : http://127.0.0.1:8000
FastAPI : http://127.0.0.1:8001
```

Jangan downgrade PHP, Node, NPM, atau Laravel tanpa alasan kuat.

---

# Pembagian Tanggung Jawab

## Laravel

Laravel bertanggung jawab untuk:

- autentikasi user
- autentikasi admin
- API utama untuk Flutter
- dashboard admin
- database penyakit
- detail penyakit
- gejala
- penyebab
- penanganan
- pencegahan
- menyimpan foto
- menyimpan riwayat deteksi
- mapping class AI ke data penyakit
- memanggil FastAPI
- validasi request
- authorization
- response JSON konsisten

## FastAPI

FastAPI hanya bertanggung jawab untuk:

- menerima image
- preprocessing
- inference AI
- prediction class
- confidence
- top predictions
- needs_retake

Laravel **tidak boleh menjalankan TensorFlow atau model AI**.

---

# Class Mapping AI

FastAPI memiliki class mapping resmi berikut, urutan jangan diubah:

```json
[
  "Bacterial Spot",
  "Cercospora Leaf Spot",
  "Curl Virus",
  "Healthy Leaf",
  "Nutrition Deficiency",
  "White spot"
]
```

Laravel harus mapping berdasarkan `class_name`, menggunakan field:

```text
ai_class_name
```

Jangan hanya mengandalkan `class_id`.

---

# Authentication

Gunakan **Laravel Sanctum** untuk API Flutter.

Flutter menggunakan:

```http
Authorization: Bearer {token}
```

Dashboard admin menggunakan session/web authentication.

Gunakan satu tabel `users` dengan field:

```text
role
```

Nilai:

```text
admin
user
```

Tidak perlu package roles/permissions besar untuk MVP.

---

# Database

Gunakan MySQL.

Contoh `.env.example`:

```env
APP_NAME="Cabai AI"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cabai_ai
DB_USERNAME=root
DB_PASSWORD=

AI_SERVICE_URL=http://127.0.0.1:8001
AI_SERVICE_TIMEOUT=30

FILESYSTEM_DISK=public
```

Jangan commit password nyata.

---

# Migration: users

Tambahkan field:

```text
role string default user
```

Minimal:

```text
id
name
email
password
role
timestamps
```

---

# Migration: diseases

Buat tabel:

```text
diseases
```

Fields:

```text
id
ai_class_name string unique
slug string unique
name string
scientific_name nullable
description text nullable
symptoms text nullable
cause text nullable
treatment text nullable
prevention text nullable
is_healthy boolean default false
is_active boolean default true
timestamps
```

Contoh:

```text
ai_class_name = Curl Virus
name = Virus Daun Keriting
```

`Healthy Leaf` harus memiliki:

```text
is_healthy = true
```

---

# Migration: detections

Buat tabel:

```text
detections
```

Fields:

```text
id
user_id nullable foreign
disease_id nullable foreign
image_path string
ai_class_id nullable integer
ai_class_name nullable string
confidence nullable decimal(8,6)
confidence_percent nullable decimal(6,2)
needs_retake boolean default false
message nullable text
top_predictions json nullable
raw_ai_response json nullable
status string default success
timestamps
```

`status` minimal:

```text
success
failed
```

Jika FastAPI gagal, jangan membuat prediction palsu.

---

# Model Relationships

```text
User hasMany Detection

Detection belongsTo User
Detection belongsTo Disease

Disease hasMany Detection
```

Gunakan casts:

```text
top_predictions => array
raw_ai_response => array
needs_retake => boolean
is_healthy => boolean
is_active => boolean
```

---

# Disease Seeder

Buat `DiseaseSeeder`.

Seed 6 `ai_class_name` EXACT:

```text
Bacterial Spot
Cercospora Leaf Spot
Curl Virus
Healthy Leaf
Nutrition Deficiency
White spot
```

Nama Indonesia awal:

```text
Bacterial Spot
→ Bercak Bakteri

Cercospora Leaf Spot
→ Bercak Daun Cercospora

Curl Virus
→ Virus Daun Keriting

Healthy Leaf
→ Daun Sehat

Nutrition Deficiency
→ Kekurangan Nutrisi

White spot
→ Bercak Putih
```

Admin dapat memperbaiki konten melalui dashboard.

Untuk `description`, `symptoms`, `cause`, `treatment`, `prevention`:

- isi data awal singkat dan konservatif
- jangan mengarang dosis pestisida/obat spesifik
- semua dapat diedit admin

Boleh buat `AdminSeeder` untuk local development.

Jika membuat akun dev, dokumentasikan jelas bahwa hanya untuk local.

---

# Konfigurasi FastAPI

Gunakan `.env`:

```env
AI_SERVICE_URL=http://127.0.0.1:8001
AI_SERVICE_TIMEOUT=30
```

Tambahkan ke config Laravel, misalnya `config/services.php`:

```php
'ai' => [
    'url' => env('AI_SERVICE_URL', 'http://127.0.0.1:8001'),
    'timeout' => (int) env('AI_SERVICE_TIMEOUT', 30),
],
```

Jangan hardcode `127.0.0.1:8001` di Controller.

---

# AiPredictionService

Buat:

```text
app/Services/AiPredictionService.php
```

Minimal method:

```php
predict(UploadedFile $image): array
health(): array
modelInfo(): array
```

`predict()` memanggil:

```http
POST {AI_SERVICE_URL}/predict
```

multipart field:

```text
image
```

Gunakan Laravel HTTP Client:

```php
Http::timeout(...)
    ->attach(...)
    ->post(...)
```

Handle:

- connection refused
- timeout
- FastAPI 4xx
- FastAPI 5xx
- invalid JSON
- response schema tidak sesuai

Jangan expose exception mentah ke Flutter.

Log detail internal secukupnya.

Jangan log binary image, token, atau password.

---

# Upload Foto

Laravel menerima:

```text
image
```

Validasi:

```text
required
image
mimes:jpg,jpeg,png,webp
max:10240
```

Maksimal 10 MB.

Simpan ke:

```text
storage/app/public/detections/YYYY/MM/
```

Gunakan random filename / UUID.

Dokumentasikan:

```powershell
php artisan storage:link
```

---

# Detection Flow

```text
Flutter upload
    ↓
Laravel validate
    ↓
save image
    ↓
AiPredictionService
    ↓
FastAPI /predict
    ↓
lookup Disease by ai_class_name
    ↓
save Detection
    ↓
return API response
```

Jika `needs_retake=true`, detection tetap boleh disimpan.

Jika `ai_class_name` tidak ditemukan:

```text
disease_id = null
```

Prediction tetap disimpan dan log warning.

Jika FastAPI gagal sebelum detection berhasil dibuat, bersihkan file upload agar tidak orphan jika praktis.

---

# FastAPI Response yang Diharapkan

Contoh:

```json
{
  "success": true,
  "needs_retake": false,
  "prediction": {
    "class_id": 2,
    "class_name": "Curl Virus",
    "confidence": 0.904637,
    "confidence_percent": 90.46
  },
  "top_predictions": [
    {
      "class_id": 2,
      "class_name": "Curl Virus",
      "confidence": 0.904637
    },
    {
      "class_id": 3,
      "class_name": "Healthy Leaf",
      "confidence": 0.064815
    },
    {
      "class_id": 1,
      "class_name": "Cercospora Leaf Spot",
      "confidence": 0.020611
    }
  ]
}
```

---

# API Routes Flutter

Gunakan prefix:

```text
/api
```

## Public

### POST /api/register

Input:

```json
{
  "name": "Daffa",
  "email": "user@example.com",
  "password": "password",
  "password_confirmation": "password"
}
```

Response:

```text
user
token
```

### POST /api/login

Input:

```json
{
  "email": "user@example.com",
  "password": "password",
  "device_name": "Infinix X6853"
}
```

Return:

```json
{
  "success": true,
  "data": {
    "user": {},
    "token": "..."
  }
}
```

## Protected Sanctum

### POST /api/logout

Revoke token aktif.

### GET /api/me

Return user login.

### GET /api/diseases

Return disease aktif.

### GET /api/diseases/{disease:slug}

Return detail disease.

### POST /api/detections

Multipart:

```text
image
```

Endpoint ini memanggil FastAPI.

Tambahkan throttle wajar, misalnya:

```text
10 requests/minute/user
```

### GET /api/detections

Histori user login, gunakan pagination.

### GET /api/detections/{detection}

User hanya boleh melihat detection miliknya sendiri.

Admin boleh melihat semua.

---

# Response POST /api/detections

Contoh:

```json
{
  "success": true,
  "message": "Deteksi berhasil.",
  "data": {
    "detection": {
      "id": 15,
      "image_url": "http://127.0.0.1:8000/storage/detections/...",
      "needs_retake": false,
      "prediction": {
        "class_id": 2,
        "class_name": "Curl Virus",
        "confidence": 0.904637,
        "confidence_percent": 90.46
      },
      "top_predictions": [
        {
          "class_id": 2,
          "class_name": "Curl Virus",
          "confidence": 0.904637
        }
      ],
      "disease": {
        "slug": "virus-daun-keriting",
        "name": "Virus Daun Keriting",
        "description": "...",
        "symptoms": "...",
        "cause": "...",
        "treatment": "...",
        "prevention": "..."
      }
    }
  }
}
```

Jika low confidence:

```json
{
  "success": true,
  "message": "Confidence terlalu rendah. Silakan ambil ulang foto.",
  "data": {
    "detection": {
      "needs_retake": true,
      "prediction": {},
      "top_predictions": []
    }
  }
}
```

Response harus konsisten.

---

# API Resources

Gunakan bila sesuai:

```text
UserResource
DiseaseResource
DetectionResource
```

Controller tetap tipis.

---

# Form Requests

Minimal pertimbangkan:

```text
LoginRequest
StoreDetectionRequest
StoreDiseaseRequest
UpdateDiseaseRequest
```

---

# Controllers

Struktur:

```text
app/Http/Controllers/
├── Api/
│   ├── AuthController.php
│   ├── DiseaseController.php
│   └── DetectionController.php
└── Admin/
    ├── DashboardController.php
    ├── DiseaseController.php
    └── DetectionController.php
```

Jangan mencampur API Flutter dengan dashboard admin.

---

# Authorization

Gunakan Policy atau authorization yang rapi untuk Detection.

Aturan:

```text
user:
- hanya detection sendiri

admin:
- semua detection
```

Jangan hanya percaya ID URL.

---

# Dashboard Admin

Buat dashboard web sederhana dengan:

```text
Blade + Vite
```

Tidak perlu React/Vue/Inertia untuk MVP.

Jika Breeze Blade kompatibel dengan Laravel 12 pada project ini, boleh gunakan.

Dashboard:

```text
/dashboard
```

Cards:

```text
Total User
Total Deteksi
Deteksi Hari Ini
Needs Retake
```

Tambahkan:

```text
Deteksi terbaru
Penyakit paling sering terdeteksi
AI Service Online / Offline
```

AI status berasal dari:

```http
GET FastAPI /health
```

Dashboard Laravel tetap harus terbuka walau FastAPI offline.

---

# Middleware Admin

Buat middleware:

```text
admin
```

Hanya:

```php
$user->role === 'admin'
```

yang dapat mengakses:

```text
/dashboard
/admin/*
```

Gunakan cara registrasi middleware yang benar untuk Laravel 12.

---

# Admin Disease CRUD

Route:

```text
/admin/diseases
```

Fitur:

```text
index
create
store
show
edit
update
```

Admin dapat edit:

```text
name
scientific_name
description
symptoms
cause
treatment
prevention
is_active
```

Hindari hard delete sebagai default.

Gunakan `is_active` untuk menonaktifkan.

`ai_class_name` sebaiknya tidak mudah diubah karena merupakan mapping AI.

---

# Admin Detection History

Route:

```text
/admin/detections
```

Index tampilkan:

```text
foto
user
hasil
confidence
needs_retake
tanggal
```

Detail:

```text
/admin/detections/{detection}
```

Tampilkan:

```text
image
prediction
top 3
disease detail
tanggal
user
```

Raw AI response hanya untuk development/debug jika memang perlu.

---

# AI Offline Handling

Jika FastAPI:

```text
offline
timeout
500
invalid JSON
```

Laravel return:

```http
503 Service Unavailable
```

```json
{
  "success": false,
  "message": "Layanan AI sedang tidak tersedia. Silakan coba kembali."
}
```

Jangan expose:

```text
Connection refused
stack trace
filesystem path
```

---

# Frontend Build

Environment:

```text
Node v24.13.1
NPM 11.8.0
```

Gunakan:

```powershell
npm install
npm run build
```

Development:

```powershell
npm run dev
```

Jangan menambah dependency frontend besar tanpa kebutuhan.

---

# Local Android / Flutter Note

Saat Laravel dipanggil dari HP fisik, `127.0.0.1:8000` di HP adalah HP itu sendiri.

Untuk testing Flutter di HP satu jaringan dengan laptop, Laravel dapat dijalankan:

```powershell
php artisan serve --host=0.0.0.0 --port=8000
```

Flutter menggunakan IP LAN laptop, contoh:

```text
http://192.168.1.10:8000/api
```

Jangan hardcode IP contoh ini.

FastAPI dari Laravel tetap:

```text
http://127.0.0.1:8001
```

karena Laravel dan FastAPI berjalan pada mesin yang sama.

---

# Testing

Gunakan test framework default Laravel.

## Authentication

Test:

```text
login sukses
login gagal
protected route butuh auth
logout
```

## Diseases

Test:

```text
list diseases
show by slug
```

## Detection

Gunakan:

```php
Http::fake()
```

Jangan bergantung FastAPI nyata dalam automated tests.

Test minimal:

```text
valid prediction
needs_retake true
FastAPI failure
unknown ai_class_name
invalid image
user tidak boleh lihat detection user lain
```

## Admin

Test:

```text
user biasa ditolak dari admin
admin dapat membuka dashboard
```

Mock FastAPI response contoh:

```json
{
  "success": true,
  "needs_retake": false,
  "prediction": {
    "class_id": 2,
    "class_name": "Curl Virus",
    "confidence": 0.904637,
    "confidence_percent": 90.46
  },
  "top_predictions": [
    {
      "class_id": 2,
      "class_name": "Curl Virus",
      "confidence": 0.904637
    }
  ]
}
```

---

# README

README harus menjelaskan setup Windows.

Contoh:

```powershell
composer install
copy .env.example .env
php artisan key:generate
```

Atur MySQL lalu:

```powershell
php artisan migrate --seed
php artisan storage:link
```

Frontend:

```powershell
npm install
npm run build
```

Run:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

FastAPI:

```text
http://127.0.0.1:8001
```

---

# Suggested Phases

## Phase 1

- Laravel config
- Sanctum
- users role
- Disease
- Detection
- migrations
- relationships
- seeders

## Phase 2

- AiPredictionService
- FastAPI health/predict
- error handling
- `Http::fake()` tests

## Phase 3

- register/login/logout/me
- diseases API
- detections upload
- detection history
- authorization

## Phase 4

- admin auth
- middleware admin
- dashboard
- disease CRUD
- detection history
- AI health indicator

## Phase 5

- tests
- npm build
- README
- live integration check

---

# Definition of Done

Selesai jika:

1. Laravel berjalan di `127.0.0.1:8000`.
2. MySQL migration sukses.
3. Seeder 6 class sukses.
4. Sanctum login sukses.
5. FastAPI URL configurable dari `.env`.
6. Laravel dapat memanggil FastAPI `127.0.0.1:8001`.
7. `POST /api/detections` menerima multipart image.
8. Foto tersimpan.
9. Detection tersimpan.
10. `ai_class_name` terhubung ke Disease.
11. Disease detail dikembalikan ke Flutter.
12. `needs_retake` ditangani.
13. FastAPI offline -> Laravel 503 aman.
14. User hanya dapat melihat history sendiri.
15. Admin dashboard aktif.
16. Disease CRUD aktif.
17. Admin detection history aktif.
18. AI status tampil tanpa merusak dashboard saat offline.
19. Automated tests lulus.
20. `npm run build` lulus.
21. README lengkap.
22. Tidak ada TensorFlow/Python di Laravel.
23. Tidak ada FastAPI URL hardcoded di controller.

---

# Instruksi untuk Codex / Cursor Agent

Implementasikan langsung di workspace Laravel yang sedang terbuka.

Sebelum coding:

1. Inspect struktur project.
2. Pastikan Laravel 12 dan PHP 8.2.
3. Jangan overwrite code existing tanpa alasan.
4. Gunakan Laravel 12 conventions.
5. Gunakan dependency minimal.
6. Jangan mengubah project FastAPI.
7. FastAPI diasumsikan sudah aktif di `127.0.0.1:8001`.
8. Gunakan `Http::fake()` untuk automated test AI.
9. Live integration dilakukan setelah test internal lulus.

Setelah implementasi:

1. Jalankan migration + seeder.
2. Jalankan `php artisan test`.
3. Jalankan `npm run build`.
4. Jalankan `php artisan route:list`.
5. Pastikan tidak ada syntax/import error.
6. Test API login.
7. Test live `POST /api/detections` jika FastAPI aktif.
8. Laporkan:
   - file dibuat/diubah
   - migrations
   - seeders
   - routes
   - hasil test
   - hasil npm build
   - hasil live FastAPI integration
   - akun admin local jika dibuat
   - command menjalankan project
