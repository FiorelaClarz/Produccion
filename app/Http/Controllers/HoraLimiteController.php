<?php

namespace App\Http\Controllers;

use App\Models\HoraLimite;
use Illuminate\Http\Request;

class HoraLimiteController extends Controller
{
    public function index()
    {
        $horaLimites = HoraLimite::where('is_deleted', false)->get();
        return view('hora_limites.index', compact('horaLimites'));
    }

    public function create()
    {
        return view('hora_limites.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'hora_limite' => 'required|string',
            'descripcion' => 'nullable|string',
            'status' => 'boolean'
        ]);

        HoraLimite::create($request->all());

        return redirect()->route('hora-limites.index')
            ->with('success', 'Hora límite creada exitosamente.');
    }

    public function show(HoraLimite $horaLimite)
    {
        return view('hora_limites.show', compact('horaLimite'));
    }

    public function edit(HoraLimite $horaLimite)
    {
        return view('hora_limites.edit', compact('horaLimite'));
    }

    public function update(Request $request, HoraLimite $horaLimite)
    {
        $request->validate([
            'hora_limite' => 'required|string',
            'descripcion' => 'nullable|string',
            'status' => 'boolean'
        ]);

        $horaLimite->update($request->all());

        return redirect()->route('hora-limites.index')
            ->with('success', 'Hora límite actualizada exitosamente.');
    }

    public function destroy(HoraLimite $horaLimite)
    {
        // Eliminación suave
        $horaLimite->update(['is_deleted' => true]);
        
        // O eliminación permanente
        // $horaLimite->delete();

        return redirect()->route('hora-limites.index')
            ->with('success', 'Hora límite eliminada exitosamente.');
    }
}