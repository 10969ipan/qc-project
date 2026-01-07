<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // ... (metode-metode lain seperti login, register, logout)

    public function login()
    {
        // Variabel untuk menampung data login yang tersimpan (Remember Me)
        $saved_email = '';
        $saved_password = '';
        $is_remembered = false;

        // Cek apakah ada cookie 'login_credentials'
        if (\Illuminate\Support\Facades\Cookie::has('login_credentials')) {
            try {
                // Decrypt dan decode data kredensial dari cookie
                $credentials = json_decode(\Illuminate\Support\Facades\Crypt::decryptString(\Illuminate\Support\Facades\Cookie::get('login_credentials')), true);
                if ($credentials) {
                    $saved_email = $credentials['email'];
                    $saved_password = $credentials['password'];
                    $is_remembered = true;
                }
            } catch (\Exception $e) {
                // Jika cookie tidak valid, abaikan saja
            }
        }

        return view('auth.login', compact('saved_email', 'saved_password', 'is_remembered'));
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $remember = $request->has('remember');

        // JANGAN lewatkan $remember ke Auth::attempt, karena kita ingin sesi berakhir saat browser ditutup
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            if ($remember) {
                // Jika user mencentang 'Remember Me', simpan kredensial dalam cookie yang terenkripsi selama 30 hari
                $cookieValue = \Illuminate\Support\Facades\Crypt::encryptString(json_encode([
                    'email' => $request->email,
                    'password' => $request->password
                ]));
                \Illuminate\Support\Facades\Cookie::queue('login_credentials', $cookieValue, 43200); // 30 hari (43200 menit)
            } else {
                // Jika tidak dicentang, hapus cookie kredensial jika ada
                \Illuminate\Support\Facades\Cookie::queue(\Illuminate\Support\Facades\Cookie::forget('login_credentials'));
            }

            $role = Auth::user()->role;

            session()->flash('success', 'Selamat datang kembali, ' . Auth::user()->name . '!');
            return redirect('/dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}