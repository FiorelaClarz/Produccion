<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PerfilController extends Controller
{
    /**
     * Cambia la contraseña del usuario
     */
    public function cambiarPassword(Request $request)
    {
        $request->validate([
            'clave_actual' => 'required',
            'clave_nueva' => 'required|string|min:6|confirmed',
        ]);

        $usuario = Auth::user();

        // Verificar que la contraseña actual sea correcta
        if (!Hash::check($request->clave_actual, $usuario->getAuthPassword())) {
            return response()->json(['error' => 'La contraseña actual no es correcta'], 422);
        }

        // Actualizar la contraseña
        $userToUpdate = Usuario::findOrFail($usuario->id_usuarios);
        $userToUpdate->clave = $request->clave_nueva; // Esto activará el mutador setClaveAttribute
        $userToUpdate->save();

        return response()->json(['success' => 'Contraseña actualizada correctamente']);
    }
}
