# Upgrade ke Laravel 12 + keamanan + Portofolio Pribadi + Aplikasi PWA (v5)

Isi paket:
- Laravel 11 -> 12
- Pembatas percobaan (rate limit) login/OTP/form, kolom jebakan anti-spam, header keamanan
- /api/login dimatikan (melewati OTP; nyalakan dengan API_LOGIN_ENABLED=true bila perlu)
- Email penugasan: kegagalan kirim tidak lagi jadi layar 500
- FITUR BARU: Layar loading beranimasi tulisan "B!ng0.dev" + "Memuat..." (publik, admin, halaman login)
- FITUR BARU: Pasang sebagai aplikasi (PWA): tombol "Pasang Aplikasi", ikon, halaman offline
  (website publik dan panel admin bisa dipasang terpisah)
- FITUR BARU: Portofolio Pribadi anggota tim (admin: Anggota Tim -> Portofolio; publik: /tim/{slug})

Langkah (di komputer, terminal VS Code, folder project):

    git checkout main
    git pull
    git checkout -b upgrade-laravel-12
    (ekstrak Zip ke C:\xampp\htdocs lalu pilih Replace)
    composer update
    php artisan config:clear
    php artisan migrate        (akan menjalankan 1 migrasi baru: ..._add_personal_portfolio_to_team_members)
    php artisan serve          (tes login, form /pesan-jasa, dan Portofolio Pribadi)

Kalau semua normal:

    git add .
    git commit -m "Upgrade Laravel 12, keamanan, portofolio pribadi"
    git push -u origin upgrade-laravel-12

Lalu di GitHub: Compare & pull request -> Merge. Webhook DomCloud memperbarui website
dan menjalankan migrasi otomatis. composer.lock WAJIB ikut ter-commit.
