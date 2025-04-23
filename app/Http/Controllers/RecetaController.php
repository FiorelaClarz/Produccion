<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Producto;
use App\Models\RecetaCabecera;
use App\Models\RecetaDetalle;
use App\Models\UMedida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RecetaController extends Controller
{
    public function index()
    {
        $recetas = RecetaCabecera::with(['area', 'producto', 'uMedida'])
            ->where('is_deleted', false)
            ->get();

        return view('recetas.index', compact('recetas'));
    }

    public function create()
    {
        $areas = Area::where('status', true)->whereNull('deleted_at')->get();
        $unidades = UMedida::all();
        return view('recetas.create', compact('areas', 'unidades'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_areas' => 'required|exists:areas,id_areas',
            'producto_nombre' => 'required',
            'id_productos_api' => 'required|exists:productos,id_item',
            'nombre' => 'required|string|max:20',
            'cant_rendimiento' => 'required|numeric',
            'id_u_medidas' => 'required|exists:u_medidas,id_u_medidas',
            'constante_crecimiento' => 'required|numeric',
            'constante_peso_lata' => 'required|numeric',
            'ingredientes' => 'required|array|min:1',
            'ingredientes.*.id_productos_api' => 'required|exists:productos,id_item',
            'ingredientes.*.cantidad' => 'required|numeric',
            'ingredientes.*.cant_presentacion' => 'required|integer',
            'ingredientes.*.id_u_medidas' => 'required|exists:u_medidas,id_u_medidas'
        ]);

        // Crear cabecera
        $cabecera = RecetaCabecera::create([
            'id_areas' => $request->id_areas,
            'id_productos_api' => $request->id_productos_api,
            'nombre' => $request->nombre,
            'cant_rendimiento' => $request->cant_rendimiento,
            'id_u_medidas' => $request->id_u_medidas,
            'constante_crecimiento' => $request->constante_crecimiento,
            'constante_peso_lata' => $request->constante_peso_lata,
            'status' => true,
            'is_deleted' => false
        ]);

        // Crear detalles
        foreach ($request->ingredientes as $ingrediente) {
            $producto = Producto::find($ingrediente['id_productos_api']);

            RecetaDetalle::create([
                'id_recetas_cab' => $cabecera->id_recetas,
                'id_productos_api' => $ingrediente['id_productos_api'],
                'nombre' => $producto->nombre,
                'cantidad' => $ingrediente['cantidad'],
                'cant_presentacion' => $ingrediente['cant_presentacion'],
                'id_u_medidas' => $ingrediente['id_u_medidas'],
                'costo_unitario' => $producto->costo,
                'subtotal_receta' => $ingrediente['cantidad'] * $producto->costo
            ]);
        }

        return redirect()->route('recetas.index')->with('success', 'Receta creada exitosamente');
    }

    public function show($id)
    {
        $receta = RecetaCabecera::with(['area', 'producto', 'uMedida', 'detalles.producto', 'detalles.uMedida'])
            ->findOrFail($id);

        return view('recetas.show', compact('receta'));
    }

    public function edit($id)
    {
        $receta = RecetaCabecera::with(['detalles'])->findOrFail($id);
        $areas = Area::where('status', true)->whereNull('deleted_at')->get();
        $unidades = UMedida::all();

        return view('recetas.edit', compact('receta', 'areas', 'unidades'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_areas' => 'required|exists:areas,id_areas',
            'producto_nombre' => 'required',
            'id_productos_api' => 'required|exists:productos,id_item',
            'nombre' => 'required|string|max:20',
            'cant_rendimiento' => 'required|numeric',
            'id_u_medidas' => 'required|exists:u_medidas,id_u_medidas',
            'constante_crecimiento' => 'required|numeric',
            'constante_peso_lata' => 'required|numeric'
        ]);

        $receta = RecetaCabecera::findOrFail($id);

        $receta->update([
            'id_areas' => $request->id_areas,
            'id_productos_api' => $request->id_productos_api,
            'nombre' => $request->nombre,
            'cant_rendimiento' => $request->cant_rendimiento,
            'id_u_medidas' => $request->id_u_medidas,
            'constante_crecimiento' => $request->constante_crecimiento,
            'constante_peso_lata' => $request->constante_peso_lata
        ]);

        return redirect()->route('recetas.index')->with('success', 'Receta actualizada exitosamente');
    }

    public function destroy($id)
    {
        $receta = RecetaCabecera::findOrFail($id);
        $receta->update(['is_deleted' => true]);
        $receta->delete();

        return redirect()->route('recetas.index')->with('success', 'Receta eliminada exitosamente');
    }

    // Métodos para AJAX
    public function buscarProductos(Request $request)
    {
        $term = $request->get('term');

        // Depuración - verifica que el término llegue
        Log::debug("Búsqueda de productos con término: " . $term);

        try {
            $productos = Producto::where(function ($query) use ($term) {
                $query->where('nombre', 'ILIKE', "%$term%")
                    ->orWhere('codigo', 'ILIKE', "%$term%");
            })
                ->take(10)
                ->get(['id_item as id', 'nombre as text', 'costo', 'codigo']);

            Log::debug("Resultados encontrados: " . $productos->count());

            return response()->json($productos);
        } catch (\Exception $e) {
            Log::error("Error en búsqueda de productos: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function agregarIngrediente(Request $request)
    {
        $request->validate([
            'id_productos_api' => 'required|exists:productos,id_item',
            'cantidad' => 'required|numeric|min:0.01',
            'cant_presentacion' => 'required|integer|min:1',
            'id_u_medidas' => 'required|exists:u_medidas,id_u_medidas'
        ]);

        try {
            $producto = Producto::findOrFail($request->id_productos_api);
            $uMedida = UMedida::findOrFail($request->id_u_medidas);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $producto->id_item,
                    'nombre' => $producto->nombre,
                    'cantidad' => $request->cantidad,
                    'cant_presentacion' => $request->cant_presentacion,
                    'u_medida' => $uMedida->nombre,
                    'costo_unitario' => $producto->costo,
                    'subtotal' => $request->cantidad * $producto->costo
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener información del producto: ' . $e->getMessage()
            ], 500);
        }
    }
}
