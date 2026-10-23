# B!ng0.dev — AgencyOS Laravel

## Mode aset frontend
Project ini **tidak membutuhkan Vite/NPM untuk menjalankan website**.

CSS produksi sudah tersedia langsung di:
- `public/css/app.css`
- `public/js/admin.js`

Blade memanggil aset dengan `asset(...)`, sehingga URL CSS/JS mengikuti host yang sedang membuka Laravel. Ini cocok untuk:
- `http://127.0.0.1:8000`
- `http://IP-LAN:8000`
- VS Code Dev Tunnel / port forwarding 8000

Jangan jalankan `npm run dev` untuk menjalankan website ini.

## Menjalankan di XAMPP
Buka CMD:

```bat
cd C:\xampp\htdocs\agencyos-laravel-fullstack
php artisan optimize:clear
php artisan serve --host=0.0.0.0 --port=8000
```

Buka di laptop:
`http://127.0.0.1:8000`

Dari HP satu jaringan:
`http://IP-LAPTOP:8000`

Jika memakai VS Code Port Forwarding/Dev Tunnel, forward **port 8000** dan buka URL tunnel tersebut.

## Database
1. Jalankan MySQL dari XAMPP.
2. Buat database `agencyos`.
3. Pastikan `.env` berisi koneksi MySQL yang benar.
4. Untuk instalasi baru:

```bat
php artisan migrate
php artisan db:seed
```

Jika database sudah pernah dimigrasikan, gunakan:

```bat
php artisan migrate
php artisan optimize:clear
```

## Login admin awal
Seeder membuat:
- Email: `admin@bing0.dev`
- Password: `password`

Setelah berhasil masuk, buka **Admin → Akun Admin** untuk mengganti email/password default.

Login admin tetap menggunakan alur:
1. Password
2. Email akun
3. OTP ke email
4. Dashboard

Untuk OTP email sungguhan, `.env` harus memakai SMTP. Jika `MAIL_MAILER=log`, OTP ditulis ke log Laravel dan tidak masuk inbox.

## Gambar portfolio
Gambar disimpan di `storage/app/public/portfolios/...`.

Website menggunakan endpoint `/media/...` untuk menampilkan file, sehingga tidak bergantung pada symbolic link `public/storage`. Ini juga menghindari masalah gambar saat memakai Dev Tunnel.

Fitur gambar:
- thumbnail
- banner
- multiple gallery
- hapus thumbnail/banner
- hapus gambar galeri satu per satu
- gambar baru ditambahkan tanpa menghapus galeri lama

## Export portfolio
Admin → Portfolio menyediakan:
- PDF
- PowerPoint

PowerPoint dibuat sebagai `.pptx` dan dapat berisi:
- cover project
- ringkasan
- masalah/solusi/dampak
- fitur & teknologi
- gallery
- testimonial
- slide penutup B!ng0.dev

## Catatan
Folder lama `agencyos-laravel-new` dan `node_modules` sengaja tidak disertakan pada paket bersih karena merupakan duplikasi/artefak eksperimen Vite dan tidak diperlukan untuk mode frontend statis project ini.
