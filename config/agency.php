<?php

return [
    // Dipakai oleh DatabaseSeeder. Di production, ADMIN_PASSWORD wajib diisi.
    'admin_email' => env('ADMIN_EMAIL', 'admin@bing0.dev'),
    'admin_password' => env('ADMIN_PASSWORD'),

    // /api/login memberi token admin tanpa OTP. Default MATI; nyalakan hanya kalau memang perlu.
    'api_login_enabled' => (bool) env('API_LOGIN_ENABLED', false),
];
