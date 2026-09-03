<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user()->role);
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return $this->redirectByRole(Auth::user()->role);
        }

        return back()->withErrors(['email' => 'Email atau password salah.'])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Berhasil logout.');
    }

    private function redirectByRole(string $role)
    {
        $user = Auth::user();
        $routes = $role === 'user'
            ? [['module.dashboard', 'user.dashboard'], ['module.checklist', 'user.checklist.index'], ['module.daily-activity', 'user.daily-activities.index'], ['module.asset', 'user.assets.index'], ['module.history', 'user.history']]
            : [['module.dashboard', 'admin.dashboard'], ['module.checklist', 'admin.forms.index'], ['module.daily-activity', 'admin.daily-activities.index'], ['module.history', 'admin.dashboard']];

        foreach ($routes as [$permission, $route]) {
            if ($user->hasModuleAccess(str_replace('module.', '', $permission))) {
                return redirect()->route($route);
            }
        }

        Auth::logout();

        return redirect()->route('login')->withErrors(['email' => 'Akun belum memiliki akses modul.']);
    }
}
