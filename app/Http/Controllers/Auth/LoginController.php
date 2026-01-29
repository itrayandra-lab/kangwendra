<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        Log::channel('auth')->info('Login form accessed', [
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now(),
        ]);

        if (Auth::check()) {
            Log::channel('auth')->info('Already authenticated user accessed login form', [
                'user_id' => Auth::id(),
                'email' => Auth::user()->email,
                'ip' => request()->ip(),
                'timestamp' => now(),
            ]);
            return redirect('/portal/home')->with('info', 'Anda masih login, pastikan logout untuk login akun lain.');
        }

        return view('pages.auth.login');
    }

    public function login(Request $request)
    {
        Log::channel('auth')->info('Login attempt started', [
            'email' => $request->input('email'),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now(),
        ]);

        $this->ensureIsNotRateLimited($request);

        $credentials = $request->validate(
            [
                'email' => ['required', 'email', 'max:255'],
                'password' => ['required', 'string', 'min:3'],
            ],
            [
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'email.max' => 'Email maksimal 255 karakter.',
                'password.required' => 'Password wajib diisi.',
                'password.min' => 'Password minimal 3 karakter.',
            ],
        );

        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            Log::channel('auth')->warning('Login failed - user not found', [
                'email' => $credentials['email'],
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now(),
            ]);
            RateLimiter::hit($this->throttleKey($request));
            return back()->with('error', 'Email atau password salah. Silakan coba lagi.');
        }

        if ($user->status !== 'active') {
            Log::channel('auth')->warning('Login failed - inactive user', [
                'user_id' => $user->id,
                'email' => $user->email,
                'status' => $user->status,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now(),
            ]);
            return back()->with('error', 'Akun Anda tidak aktif. Silakan hubungi admin.');
        }

        $remember = $request->boolean('remember-me');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            Log::channel('auth')->info('Login successful', [
                'user_id' => $user->id,
                'email' => $user->email,
                'remember' => $remember,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now(),
            ]);

            RateLimiter::clear($this->throttleKey($request));

            return redirect()->intended('/portal/home')->with('success', 'Login berhasil! Selamat datang kembali.');
        }

        Log::channel('auth')->warning('Login failed - invalid credentials', [
            'email' => $credentials['email'],
            'user_id' => $user->id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now(),
        ]);

        RateLimiter::hit($this->throttleKey($request));

        return back()->with('error', 'Email atau password salah. Silakan coba lagi.');
    }

    protected function ensureIsNotRateLimited(Request $request)
    {
        $maxAttempts = 5;

        if (RateLimiter::tooManyAttempts($this->throttleKey($request), $maxAttempts)) {
            $seconds = RateLimiter::availableIn($this->throttleKey($request));
            $minutes = ceil($seconds / 60);

            Log::channel('auth')->warning('Login rate limited', [
                'email' => $request->input('email'),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'attempts' => RateLimiter::attempts($this->throttleKey($request)),
                'available_in_seconds' => $seconds,
                'timestamp' => now(),
            ]);

            return back()
                ->with('warning', "Terlalu banyak percobaan login. Tunggu {$minutes} menit sebelum mencoba lagi.")
                ->onlyInput('email');
        }
    }

    protected function throttleKey(Request $request)
    {
        return strtolower($request->input('email')) . '|' . $request->ip();
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        
        Log::channel('auth')->info('User logout', [
            'user_id' => $user ? $user->id : null,
            'email' => $user ? $user->email : null,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now(),
        ]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/portal/login')->with('success', 'Anda telah logout dari sistem.');
    }
}
