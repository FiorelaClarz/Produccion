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
            'password' => $request->clave, // Laravel espera un campo 'password' por defecto
        ];

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            // Redireccionar según el rol
            if (Auth::user()->id_roles == 1) {
                return redirect()->intended('dashboard');
            } else {
                return redirect('/')->with('success', 'Bienvenido');
            }
        }

        return back()->withErrors([
            'clave' => 'Las credenciales proporcionadas no son correctas.',
        ])->withInput();

        // Buscar el usuario primero para verificar si está activo
        // $usuario = Usuario::where('dni_personal', $request->dni_personal)
        //     ->where('is_deleted', false)
        //     ->first();

        // if (!$usuario) {
        //     return back()->withErrors([
        //         'dni_personal' => 'El DNI no está registrado o la cuenta ha sido eliminada.',
        //     ]);
        // }

        // if (!$usuario->status) {
        //     return back()->withErrors([
        //         'dni_personal' => 'La cuenta está desactivada.',
        //     ]);
        // }

        // if (Auth::attempt($credentials, $request->filled('remember'))) {
        //     $request->session()->regenerate();

        //     return redirect()->intended('dashboard');
        // }

        // return back()->withErrors([
        //     'clave' => 'La contraseña proporcionada no es correcta.',
        // ]);
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
        } elseif ($user->id_roles == 2) { // Supervisor
            return '/gerencia/dashboard';
        }

        return '/dashboard';
    }
}
