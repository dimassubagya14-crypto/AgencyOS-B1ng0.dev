<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function edit(): View
    {
        return view('admin.account.edit', [
            'user' => Auth::user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'current_password' => ['required', 'current_password'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.current_password' => 'Password saat ini salah.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.min' => 'Password baru minimal 8 karakter.',
        ]);

        $emailChanged = Str::lower($user->email) !== Str::lower($data['email']);

        $user->name = $data['name'];
        $user->email = $data['email'];

        if (!empty($data['password'])) {
            $user->password = $data['password'];
        }

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        ActivityLog::record(
            'update',
            'users',
            $user->id,
            'Kredensial akun admin diperbarui. Email baru: ' . $user->email . ($data['password'] ? ' | Password juga diganti.' : '')
        );

        return back()->with('success', 'Data akun admin berhasil diperbarui. Gunakan email/password baru pada login berikutnya.');
    }
}
