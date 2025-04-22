<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use Illuminate\Http\Request;

class RolController extends Controller
{
    /**
     * Listar todos los roles activos no eliminados
     */
    public function index()
    {


        // Temporalmente para diagnóstico:
        // dd($rols->toArray()); 

        // Versión final (cuando funcione):
        $rols = Rol::where('is_deleted', false)
            ->orderBy('status', 'desc')
            ->orderBy('nombre')
            ->get();

        return view('rols.index', compact('rols'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        return view('rols.create');
    }

    /**
     * Almacenar nuevo rol
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:45|unique:rols,nombre',
            'status' => 'sometimes|boolean'
        ]);

        try {
            Rol::create([
                'nombre' => $validated['nombre'],
                'status' => $validated['status'] ?? true,
                'created_at' => now()->timezone(config('app.timezone')),
                'updated_at' => now()->timezone(config('app.timezone')),
                'is_deleted' => false,

            ]);

            return redirect()->route('rols.index')
                ->with('success', 'Rol creado exitosamente');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error al crear rol: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $rol = Rol::where('is_deleted', false)
            ->findOrFail($id);

        return view('rols.edit', compact('rol'));
    }

    /**
     * Actualizar rol existente
     */
    public function update(Request $request, $id)
    {
        $rol = Rol::where('is_deleted', false)
            ->findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:45|unique:rols,nombre,' . $rol->id_roles . ',id_roles',
            'status' => 'sometimes|boolean'
        ]);

        try {
            $rol->update([
                'nombre' => $validated['nombre'],
                'status' => $request->has('status'), // true si está marcado, false si no
                'updated_at' => now()->timezone(config('app.timezone'))
            ]);

            return redirect()->route('rols.index')
                ->with('success', 'Rol actualizado exitosamente');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error al actualizar rol: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar rol (marcar como eliminado)
     */
    public function destroy($id)
    {
        $rol = Rol::where('is_deleted', false)
            ->findOrFail($id);
        $rol->delete();
        try {
            $rol->update([
                'is_deleted' => true,
                'status' => false,
                'deleted_at' => now()->timezone(config('app.timezone')),
                'updated_at' => now()->timezone(config('app.timezone'))
            ]);

            return redirect()->route('rols.index')
                ->with('success', 'Rol eliminado exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar rol: ' . $e->getMessage());
        }
    }
}
