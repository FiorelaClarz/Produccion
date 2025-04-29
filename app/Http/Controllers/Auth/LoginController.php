<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Usuario;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'dni_personal' => 'required|string',
            'clave' => 'required|string',
        ]);

        $credentials = [
            'dni_personal' => $request->dni_personal,
            'password' => $request->clave,
        ];

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            // Redireccionar según el rol
            switch (Auth::user()->id_roles) {
                case 1: // Admin
                    return redirect()->route('dashboard');
                case 2: // Gerencia
                    return redirect()->route('home');
                case 3: // Rol 3
                    return redirect()->route('home');
                case 4: // Rol 4
                    return redirect()->route('home');
                default:
                    return redirect()->route('home');
            }
        }

        return back()->withErrors([
            'clave' => 'Las credenciales proporcionadas no son correctas.',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    protected function redirectTo()
    {
        // Redirigir según el rol del usuario
        $user = Auth::user();

        if ($user->id_roles == 1) { // Admin
            return '/admin/dashboard';
        } elseif ($user->id_roles == 2) { // Gerencia
            return '/gerencia/dashboard';
        }

        return '/dashboard';
    }
}
