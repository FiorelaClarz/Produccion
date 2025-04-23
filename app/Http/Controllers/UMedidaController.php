<?php

namespace App\Http\Controllers;

use App\Models\UMedida;
use Illuminate\Http\Request;

class UMedidaController extends Controller
{
    public function index()
    {
        $umedidas = UMedida::where('is_deleted', false)
            ->orderBy('status', 'desc')
            ->orderBy('nombre')
            ->get();

        return view('umedidas.index', compact('umedidas'));
    }

    public function create()
    {
        return view('umedidas.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:45|unique:u_medidas,nombre',
            'status' => 'sometimes|boolean'
        ]);

        try {
            UMedida::create([
                'nombre' => $validated['nombre'],
                'status' => $validated['status'] ?? true,
                'is_deleted' => false,
                'created_at' => now()->timezone(config('app.timezone')),
                'updated_at' => now()->timezone(config('app.timezone'))
            ]);

            return redirect()->route('umedidas.index')
                ->with('success', 'Unidad de medida creada exitosamente');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error al crear unidad: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $umedida = UMedida::where('is_deleted', false)
            ->findOrFail($id);

        return view('umedidas.show', compact('umedida'));
    }

    public function edit($id)
    {
        $umedida = UMedida::where('is_deleted', false)
            ->findOrFail($id);

        return view('umedidas.edit', compact('umedida'));
    }

    public function update(Request $request, $id)
    {
        $umedida = UMedida::where('is_deleted', false)
            ->findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:45|unique:u_medidas,nombre,' . $umedida->id_u_medidas . ',id_u_medidas',
            'status' => 'sometimes|boolean'
        ]);

        try {
            $umedida->update([
                'nombre' => $validated['nombre'],
                'status' => $request->has('status'),
                'updated_at' => now()->timezone(config('app.timezone'))
            ]);

            return redirect()->route('umedidas.index')
                ->with('success', 'Unidad actualizada exitosamente');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error al actualizar unidad: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $umedida = UMedida::where('is_deleted', false)
            ->findOrFail($id);

        try {
            $umedida->delete(); // Esto activará el soft delete y el boot() del modelo
            return redirect()->route('umedidas.index')
                ->with('success', 'Unidad eliminada exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar unidad: ' . $e->getMessage());
        }
    }
}