<?php

namespace App\Http\Controllers;

use App\Models\Tienda;
use Illuminate\Http\Request;

class TiendaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tiendas = Tienda::where('is_deleted', false)
            ->orderBy('status', 'desc') // Activos primero
            ->orderBy('nombre', 'asc')
            ->get();

        return view('tiendas.index', compact('tiendas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('tiendas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:45',
        ]);

        try {
            Tienda::create([
                'nombre' => $validated['nombre'],
                'status' => true,
                'created_at_datetime' => now()->timezone(config('app.timezone')),
                'updated_at_datetime' => now()->timezone(config('app.timezone')),
                'is_deleted' => false,
            ]);

            return redirect()->route('tiendas.index')->with('success', 'Tienda creada exitosamente.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error al crear tienda: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $tienda = Tienda::where('id_tiendas', $id)
                      ->where('is_deleted', false)
                      ->firstOrFail();
                      
        return view('tiendas.show', compact('tienda'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $tienda = Tienda::where('id_tiendas', $id)
                      ->where('is_deleted', false)
                      ->firstOrFail();
                      
        return view('tiendas.edit', compact('tienda'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:45',
            'codigo_tienda' => 'nullable|string|max:10',
            'status' => 'required|boolean',
        ]);

        try {
            $tienda = Tienda::findOrFail($id);
            $tienda->update([
                'nombre' => $validated['nombre'],
                'codigo_tienda' => $validated['codigo_tienda'],
                'status' => $validated['status'],
                'updated_at_datetime' => now()->timezone(config('app.timezone'))
            ]);

            return redirect()->route('tiendas.index')
                ->with('success', 'Tienda actualizada exitosamente');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error al actualizar tienda: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $tienda = Tienda::findOrFail($id);
            $tienda->delete();
            $tienda->update([
                'status' => false,
                'is_deleted' => true,
                'updated_at_datetime' => now()->timezone(config('app.timezone'))
            ]);
            return redirect()->route('tiendas.index')
                ->with('success', 'Tienda eliminada exitosamente. Se ha marcado como inactiva.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar tienda: ' . $e->getMessage());
        }
    }
}