<?php

namespace App\Http\Controllers;

use App\Models\Turno;
use Illuminate\Http\Request;

class TurnoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $turnos = Turno::where('is_deleted', false)->get();
        return view('turnos.index', compact('turnos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('turnos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:20',
        ]);

        Turno::create([
            'nombre' => $request->nombre,
            'create_date' => now(),
            'last_update' => now(),
            'status' => true,
            'is_deleted' => false
        ]);

        return redirect()->route('turnos.index')->with('success', 'Turno creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $turno = Turno::findOrFail($id);
        return view('turnos.show', compact('turno'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $turno = Turno::findOrFail($id);
        return view('turnos.edit', compact('turno'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:20',
            'status' => 'required|boolean',
        ]);

        $turno = Turno::findOrFail($id);
        $turno->update([
            'nombre' => $request->nombre,
            'last_update' => now(),
            'status' => $request->status
        ]);

        return redirect()->route('turnos.index')->with('success', 'Turno actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $turno = Turno::findOrFail($id);
        $turno->update([
            'is_deleted' => true,
            'last_update' => now()
        ]);

        return redirect()->route('turnos.index')->with('success', 'Turno eliminado exitosamente.');
    }
}