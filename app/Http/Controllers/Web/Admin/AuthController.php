<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        $step = $request->session()->get('admin_login_step', 'password');

        // Langkah OTP hanya sah kalau OTP-nya benar-benar ada dan belum kedaluwarsa.
        // Kalau tidak, mundur ke langkah sebelumnya supaya tidak "nyangkut" di langkah 3.
        if ($step === 'otp'
            && (! $request->session()->get('admin_otp_hash')
                || now()->timestamp > (int) $request->session()->get('admin_otp_expires_at', 0))) {
            $request->session()->forget(['admin_otp_hash', 'admin_otp_expires_at', 'admin_otp_attempts', 'admin_otp_email', 'admin_login_user_id']);
            $step = $request->session()->get('admin_password_verified') ? 'email' : 'password';
            $request->session()->put('admin_login_step', $step);
        }

        return view('admin.login', [
            'step' => $step,
            'otpExpiresAt' => $request->session()->get('admin_otp_expires_at'),
            'otpEmail' => $request->session()->get('admin_otp_email'),
        ]);
    }

    /** Step 1: password only. The matching admin is kept server-side in the session. */
    public function verifyPassword(Request $request): RedirectResponse
    {
        $request->validate(['password' => ['required', 'string']]);

        $matches = User::query()
            ->where('role', 'admin')
            ->where('is_active', true)
            ->get()
            ->filter(fn (User $user) => Hash::check($request->string('password')->toString(), $user->password));

        if ($matches->isEmpty()) {
            ActivityLog::record('login_password_failed', 'auth', null, 'Password admin salah');
            return back()->withErrors(['password' => 'Password salah.'])->withInput();
        }

        $request->session()->put([
            'admin_login_step' => 'email',
            'admin_password_verified' => true,
            'admin_password_candidate_ids' => $matches->pluck('id')->values()->all(),
        ]);
        $request->session()->forget(['admin_otp_hash', 'admin_otp_expires_at', 'admin_otp_attempts', 'admin_otp_email', 'admin_login_user_id']);

        ActivityLog::record('login_password_verified', 'auth', null, 'Password admin benar; menunggu email OTP. IP: '.$request->ip());

        return redirect()->route('admin.login');
    }

    /** Step 2: email selects the already password-verified admin account. */
    public function sendOtp(Request $request): RedirectResponse
    {
        if (! $request->session()->get('admin_password_verified')) {
            return redirect()->route('admin.login')->withErrors(['password' => 'Mulai dari password terlebih dahulu.']);
        }

        $data = $request->validate(['email' => ['required', 'email']]);
        $candidateIds = $request->session()->get('admin_password_candidate_ids', []);

        $user = User::query()
            ->whereIn('id', $candidateIds)
            ->where('role', 'admin')
            ->where('is_active', true)
            ->whereRaw('LOWER(email) = ?', [Str::lower($data['email'])])
            ->first();

        if (! $user) {
            ActivityLog::record('login_email_rejected', 'auth', null, 'Email login ditolak: '.$data['email'].' | IP: '.$request->ip());
            return back()->withErrors(['email' => 'Email tidak cocok dengan akun admin yang password-nya sudah diverifikasi.']);
        }

        // Only one live admin device/session at a time. A stale session (>15 min idle) is released.
        $activeSessionId = $user->active_session_id;
        if ($activeSessionId && $activeSessionId !== $request->session()->getId()) {
            $existing = \DB::table('sessions')->where('id', $activeSessionId)->first();
            $isRecent = $existing && ((int) $existing->last_activity >= now()->subMinutes(15)->timestamp);
            if ($isRecent) {
                ActivityLog::record('login_blocked_device', 'auth', $user->id, 'Login ditolak karena akun sedang aktif di perangkat lain. Email: '.$user->email.' | IP baru: '.$request->ip());
                return back()->withErrors(['email' => 'Login ditolak. Akun ini sedang aktif di perangkat lain. Silakan logout dari perangkat tersebut terlebih dahulu.']);
            }
            $user->forceFill([
                'active_session_id' => null,
                'active_session_ip' => null,
                'active_session_user_agent' => null,
            ])->save();
        }

        $otp = (string) random_int(100000, 999999);

        // Kirim email DULU. Sesi OTP baru disimpan setelah email benar-benar terkirim,
        // supaya kegagalan kirim email tidak meninggalkan user di "Langkah 3" tanpa OTP.
        try {
            Mail::raw("Kode OTP B!ng0.dev Anda: {$otp}\n\nKode ini berlaku selama 5 menit dan hanya dapat digunakan sekali.", function ($message) use ($user) {
                $message->to($user->email)->subject('Kode OTP Login Admin — B!ng0.dev');
            });
        } catch (\Throwable $e) {
            report($e);
            ActivityLog::record('otp_send_failed', 'auth', $user->id, 'Gagal mengirim OTP ke '.$user->email.' | IP: '.$request->ip());

            return back()->withErrors(['email' => 'OTP gagal dikirim. Periksa pengaturan email server (Resend) lalu coba lagi.']);
        }

        $request->session()->put([
            'admin_login_step' => 'otp',
            'admin_login_user_id' => $user->id,
            'admin_otp_email' => $user->email,
            'admin_otp_hash' => Hash::make($otp),
            'admin_otp_expires_at' => now()->addMinutes(5)->timestamp,
            'admin_otp_attempts' => 0,
        ]);

        ActivityLog::record('otp_sent', 'auth', $user->id, 'OTP dikirim ke '.$user->email.' | IP: '.$request->ip());

        return redirect()->route('admin.login')->with('success', 'OTP telah dikirim ke '.$user->email.'. Cek inbox email Anda.');
    }

    /** Step 3: verify one-time code, then create the real authenticated session. */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate(['otp' => ['required', 'digits:6']]);

        $userId = $request->session()->get('admin_login_user_id');
        $user = User::query()->whereKey($userId)->where('role', 'admin')->where('is_active', true)->first();
        $attempts = (int) $request->session()->get('admin_otp_attempts', 0);
        $expiresAt = (int) $request->session()->get('admin_otp_expires_at', 0);
        $hash = $request->session()->get('admin_otp_hash');

        if (! $user || ! $hash) {
            return redirect()->route('admin.login')->withErrors(['otp' => 'Sesi OTP tidak ditemukan. Mulai login lagi.']);
        }

        if ($attempts >= 5) {
            return redirect()->route('admin.login')->withErrors(['otp' => 'Batas percobaan OTP tercapai. Mulai login lagi.']);
        }

        if (now()->timestamp > $expiresAt) {
            $request->session()->forget(['admin_otp_hash', 'admin_otp_expires_at', 'admin_otp_attempts']);
            $request->session()->put('admin_login_step', 'email');
            return redirect()->route('admin.login')->withErrors(['otp' => 'OTP sudah kedaluwarsa. Kirim OTP baru.']);
        }

        if (! Hash::check($request->string('otp')->toString(), $hash)) {
            $attempts++;
            $request->session()->put('admin_otp_attempts', $attempts);
            ActivityLog::record('otp_failed', 'auth', $user->id, 'OTP salah (percobaan '.$attempts.') | IP: '.$request->ip());
            return back()->withErrors(['otp' => 'OTP salah. Percobaan tersisa '.max(0, 5 - $attempts).'.']);
        }

        // Re-check single-device lock immediately before authentication to prevent races.
        if ($user->active_session_id) {
            $existing = \DB::table('sessions')->where('id', $user->active_session_id)->first();
            if ($existing && $user->active_session_id !== $request->session()->getId() && (int) $existing->last_activity >= now()->subMinutes(15)->timestamp) {
                ActivityLog::record('login_blocked_device', 'auth', $user->id, 'OTP benar tetapi login ditolak karena perangkat lain masih aktif. IP: '.$request->ip());
                return redirect()->route('admin.login')->withErrors(['otp' => 'Login ditolak. Akun sedang aktif di perangkat lain.']);
            }
        }

        Auth::login($user);
        $request->session()->regenerate();
        $newSessionId = $request->session()->getId();

        $user->forceFill([
            'active_session_id' => $newSessionId,
            'active_session_ip' => $request->ip(),
            'active_session_user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
            'last_login_at' => now(),
        ])->save();

        $request->session()->put('admin_last_activity', now()->timestamp);
        $request->session()->forget([
            'admin_login_step', 'admin_password_verified', 'admin_password_candidate_ids',
            'admin_login_user_id', 'admin_otp_email', 'admin_otp_hash', 'admin_otp_expires_at', 'admin_otp_attempts',
        ]);

        ActivityLog::record('login', 'users', $user->id, "{$user->name} login berhasil via password + email OTP | Email: {$user->email} | IP: {$request->ip()} | User-Agent: ".Str::limit((string) $request->userAgent(), 200));

        return redirect()->route('admin.dashboard');
    }

    public function resendOtp(Request $request): RedirectResponse
    {
        return $this->sendOtp($request);
    }

    public function resetLogin(Request $request): RedirectResponse
    {
        foreach ([
            'admin_login_step', 'admin_password_verified', 'admin_password_candidate_ids',
            'admin_login_user_id', 'admin_otp_email', 'admin_otp_hash', 'admin_otp_expires_at', 'admin_otp_attempts',
        ] as $key) {
            $request->session()->forget($key);
        }

        return redirect()->route('admin.login');
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user) {
            if ($user->active_session_id === $request->session()->getId()) {
                $user->forceFill([
                    'active_session_id' => null,
                    'active_session_ip' => null,
                    'active_session_user_agent' => null,
                ])->save();
            }
            ActivityLog::record('logout', 'users', $user->id, "{$user->name} logout | IP: ".$request->ip());
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
