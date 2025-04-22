<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $areas = Area::where('is_deleted', false)
                    ->orderBy('status', 'desc') // Activos primero
                    ->orderBy('nombre', 'asc')
                    ->get();
                    
        return view('areas.index', compact('areas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('areas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:30',
            'descripcion' => 'required|string',
        ]);

        Area::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            // create_date, last_update, created_at_datetime, etc. se llenan automáticamente
        ]);

        return redirect()->route('areas.index')->with('success', 'Área creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $area = Area::where('id_areas', $id)
                  ->where('is_deleted', false)
                  ->firstOrFail();
                  
        return view('areas.show', compact('area'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $area = Area::where('id_areas', $id)
                  ->where('is_deleted', false)
                  ->firstOrFail();
                  
        return view('areas.edit', compact('area'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:30',
            'descripcion' => 'required|string',
            'status' => 'required|boolean',
        ]);

        $area = Area::findOrFail($id);
        $area->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'status' => $request->status,
            // last_update y updated_at_datetime se actualizan automáticamente en el modelo
        ]);

        return redirect()->route('areas.index')->with('success', 'Área actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $area = Area::findOrFail($id);
        
        // Esto activará el evento deleting del modelo que actualiza is_deleted, status, deleted_at, etc.
        $area->delete();

        return redirect()->route('areas.index')->with('success', 'Área eliminada exitosamente.');
    }
}