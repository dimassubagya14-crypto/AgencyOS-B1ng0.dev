# Upgrade ke Laravel 12 + perbaikan keamanan

Isi paket ini: Laravel 11 -> 12, pembatas percobaan (rate limit) untuk login/OTP/form,
kolom jebakan anti-spam di form pesan jasa, header keamanan, dan /api/login dimatikan
(karena melewati OTP; nyalakan lagi dengan API_LOGIN_ENABLED=true bila benar-benar perlu).

Langkah (di komputer, terminal VS Code, folder project):

    git checkout -b upgrade-laravel-12
    (ekstrak Zip ke C:\xampp\htdocs lalu pilih Replace)
    composer update
    php artisan config:clear
    php artisan migrate        (seharusnya "Nothing to migrate")
    php artisan serve          (tes login admin, OTP, dan form /pesan-jasa)

Kalau semua normal:

    git add .
    git commit -m "Upgrade Laravel 12 + perbaikan keamanan"
    git push -u origin upgrade-laravel-12

Lalu di GitHub: Compare & pull request -> Merge. Webhook DomCloud akan memperbarui website.
composer.lock WAJIB ikut ter-commit (dibuat ulang oleh composer update).
