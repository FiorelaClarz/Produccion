<?php

namespace App\Http\Controllers;

use App\Models\Estado;
use Illuminate\Http\Request;

class EstadoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $estados = Estado::where('is_deleted', false)->get();
        return view('estados.index', compact('estados'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('estados.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:45',
        ]);

        Estado::create([
            'nombre' => $request->nombre,
            'create_date' => now(),
            'last_update' => now(),
            'status' => true,
            'is_deleted' => false
        ]);

        return redirect()->route('estados.index')->with('success', 'Estado creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $estado = Estado::where('id_estados', $id)
                      ->where('is_deleted', false)
                      ->firstOrFail();
                      
        return view('estados.show', compact('estado'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $estado = Estado::findOrFail($id);
        return view('estados.edit', compact('estado'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:45',
            'status' => 'required|boolean',
        ]);

        $estado = Estado::findOrFail($id);
        $estado->update([
            'nombre' => $request->nombre,
            'last_update' => now(),
            'status' => $request->status
        ]);

        return redirect()->route('estados.index')->with('success', 'Estado actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $estado = Estado::findOrFail($id);
        $estado->update([
            'is_deleted' => true,
            'last_update' => now()
        ]);

        return redirect()->route('estados.index')->with('success', 'Estado eliminado exitosamente.');
    }
}