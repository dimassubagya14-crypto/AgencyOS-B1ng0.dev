<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminSecurity
{
    private const IDLE_MINUTES = 15;

    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        $sessionId = $request->session()->getId();
        $lastActivity = (int) $request->session()->get('admin_last_activity', now()->timestamp);

        if ($lastActivity && now()->timestamp - $lastActivity > self::IDLE_MINUTES * 60) {
            ActivityLog::record('auto_logout', 'auth', $user->id, "Auto logout setelah ".self::IDLE_MINUTES." menit tidak aktif | IP: ".$request->ip());
            $this->releaseSession($user, $sessionId);
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('admin.login')->withErrors(['password' => 'Sesi berakhir karena 15 menit tidak ada aktivitas. Silakan login kembali.']);
        }

        if ($user->active_session_id !== $sessionId) {
            ActivityLog::record('session_rejected', 'auth', $user->id, 'Sesi ditolak karena sesi aktif akun berbeda | IP: '.$request->ip());
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('admin.login')->withErrors(['password' => 'Sesi ini sudah tidak aktif. Silakan login kembali.']);
        }

        $request->session()->put('admin_last_activity', now()->timestamp);

        return $next($request);
    }

    private function releaseSession($user, string $sessionId): void
    {
        if ($user->active_session_id === $sessionId) {
            $user->forceFill([
                'active_session_id' => null,
                'active_session_ip' => null,
                'active_session_user_agent' => null,
            ])->save();
        }
    }
}
