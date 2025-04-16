<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RolController extends Controller
{
    /**
     * Listar todos los roles activos
     */
    public function index()
    {
        $rols = Rol::where('is_deleted', false)
                 ->orderBy('nombre')
                 ->get();
        
        return view('rols.index', compact('rols')); // Usando 'rols' para coincidir con tu tabla
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
            'nombre' => 'required|string|max:45|unique:rols,nombre', // Tabla 'rols'
            'status' => 'sometimes|boolean'
        ]);

        try {
            $rol = Rol::create([
                'nombre' => $validated['nombre'],
                'status' => $validated['status'] ?? true,
                'create_date' => now(),
                'last_update' => now(),
                'is_deleted' => false
            ]);

            return redirect()->route('rols.index')
                   ->with('success', 'Rol creado exitosamente');
                   
        } catch (\Exception $e) {
            return back()->withInput()
                   ->with('error', 'Error al crear rol: '.$e->getMessage());
        }
    }

    /**
     * Mostrar detalles de un rol
     */
    public function show($id)
    {
        $rol = Rol::where('is_deleted', false)
               ->findOrFail($id);
               
        return view('rols.show', compact('rol'));
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
            'nombre' => 'required|string|max:45|unique:rols,nombre,'.$rol->id_roles.',id_roles', // Tabla 'rols'
            'status' => 'required|boolean'
        ]);

        try {
            $rol->update([
                'nombre' => $validated['nombre'],
                'status' => $validated['status'],
                'last_update' => now()
            ]);

            return redirect()->route('rols.index')
                   ->with('success', 'Rol actualizado exitosamente');
                   
        } catch (\Exception $e) {
            return back()->withInput()
                   ->with('error', 'Error al actualizar rol: '.$e->getMessage());
        }
    }

    /**
     * Eliminar rol (soft delete)
     */
    public function destroy($id)
    {
        $rol = Rol::findOrFail($id);

        try {
            $rol->update([
                'is_deleted' => true,
                'last_update' => now()
            ]);

            return redirect()->route('rols.index')
                   ->with('success', 'Rol eliminado exitosamente');
                   
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar rol: '.$e->getMessage());
        }
    }
}