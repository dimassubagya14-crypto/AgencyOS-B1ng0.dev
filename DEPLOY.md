# Panduan Deploy AgencyOS ke Render (gratis)

## 0. Tes OTP di komputer dulu
Di file `.env` (lokal) ubah 1 baris ini, lalu jalankan `php artisan config:clear`:

    MAIL_FROM_ADDRESS="onboarding@resend.dev"

Catatan: dengan alamat ini, OTP hanya bisa dikirim ke **email yang dipakai mendaftar di Resend**.
Email itu harus sama dengan email akun admin (kolom email di tabel users).

## 1. Siapkan database (gratis, MySQL)
Contoh: TiDB Cloud (pilih region Singapore). Catat: host, port (4000), nama database, username, password.

## 2. Upload ke GitHub
Ekstrak Zip revisi ini ke folder project (timpa file lama), lalu:

    git add .
    git commit -m "Siap deploy: OTP via Resend, Dockerfile, render.yaml"
    git push

Pastikan `.env` TIDAK ikut (memang sudah di .gitignore).

## 3. Buat Web Service di Render
1. render.com -> New -> **Blueprint** -> pilih repo project -> Apply.
2. Isi variabel yang diminta:
   - APP_KEY: hasil `php artisan key:generate --show` (di komputer)
   - APP_URL: https://nama-service.onrender.com (isi setelah tahu alamatnya, lalu Save)
   - DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD: dari database
   - RESEND_API_KEY: kunci dari Resend
   - ADMIN_EMAIL: email admin (sama dengan email akun Resend)
   - ADMIN_PASSWORD: password admin baru (jangan "password")
3. Tunggu build selesai, buka tab Logs kalau ada error.

## 4. Setelah deploy pertama berhasil
Ubah `RUN_SEED` menjadi `false` di Environment, supaya data awal tidak dibuat ulang.

## Batasan versi gratis
- Web tidur setelah 15 menit sepi (request pertama lambat).
- Foto upload (portfolio/tim) HILANG saat service restart/deploy ulang. Untuk live,
  pindahkan penyimpanan ke Cloudinary/S3 atau pakai hosting biasa.
- Mau kirim OTP ke email selain milikmu: verifikasi domain di Resend, lalu ganti MAIL_FROM_ADDRESS.
