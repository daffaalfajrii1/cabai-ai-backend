# Cabai AI Laravel Backend

Backend Laravel 12 untuk aplikasi Cabai AI. Flutter hanya memanggil Laravel lewat REST API + Bearer Token. Laravel menyimpan user, penyakit, foto, riwayat deteksi, dan meneruskan inferensi gambar ke FastAPI AI Service lokal.

## Environment

- PHP 8.2
- Laravel 12
- Node v24.13.1
- NPM 11.8.0
- MySQL database: `cabai_ai`
- Laravel: `http://127.0.0.1:8000`
- FastAPI AI Service: `http://127.0.0.1:8001`

## Setup Windows

```powershell
composer install
copy .env.example .env
php artisan key:generate
```

Pastikan `.env` memakai MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cabai_ai
DB_USERNAME=root
DB_PASSWORD=

AI_SERVICE_URL=http://127.0.0.1:8001
AI_SERVICE_TIMEOUT=30
FILESYSTEM_DISK=public
APP_URL=http://127.0.0.1:8000
```

Jalankan migration, seeder, dan storage link:

```powershell
php artisan migrate --seed
php artisan storage:link
```

Frontend:

```powershell
npm install
npm run build
```

Run Laravel:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

Jika PHP lokal menampilkan notice upload temp sebelum JSON response, perbaiki `upload_tmp_dir` di `php.ini` atau jalankan server lokal dengan temp folder project:

```powershell
New-Item -ItemType Directory -Force storage\app\tmp
php -d upload_tmp_dir=D:\cabe-ai-backend\storage\app\tmp -d display_errors=stderr -S 127.0.0.1:8000 -t public public\index.php
```

Run untuk HP fisik satu jaringan:

```powershell
php artisan serve --host=0.0.0.0 --port=8000
```

Flutter memakai IP LAN laptop untuk Laravel, sedangkan Laravel tetap memanggil FastAPI di `http://127.0.0.1:8001`.

## Akun Admin Development

Seeder membuat akun admin lokal:

- Email: `admin@cabai-ai.local`
- Password: `password`

Akun ini hanya untuk development lokal. Ganti password atau hapus akun ini sebelum production.

## API

Public:

- `POST /api/register`
- `POST /api/login`

Protected dengan Sanctum Bearer Token:

- `POST /api/logout`
- `GET /api/me`
- `GET /api/diseases`
- `GET /api/diseases/{slug}`
- `POST /api/detections`
- `GET /api/detections`
- `GET /api/detections/{detection}`

Login:

```powershell
curl.exe -X POST http://127.0.0.1:8000/api/login `
  -H "Accept: application/json" `
  -H "Content-Type: application/json" `
  -d "{\"email\":\"admin@cabai-ai.local\",\"password\":\"password\",\"device_name\":\"local\"}"
```

Upload deteksi:

```powershell
curl.exe -X POST http://127.0.0.1:8000/api/detections `
  -H "Accept: application/json" `
  -H "Authorization: Bearer TOKEN" `
  -F "image=@C:\path\to\leaf.jpg"
```

## Dashboard Admin

- Login: `http://127.0.0.1:8000/login`
- Dashboard: `http://127.0.0.1:8000/dashboard`
- CRUD penyakit: `http://127.0.0.1:8000/admin/diseases`
- Riwayat deteksi: `http://127.0.0.1:8000/admin/detections`

Dashboard tetap terbuka walau FastAPI offline. Status AI ditampilkan dari endpoint FastAPI `/health`.

## Test

Automated test memakai `Http::fake()` untuk response AI.

```powershell
php artisan test
```
