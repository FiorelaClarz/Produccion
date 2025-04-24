<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Producto;
use App\Models\RecetaCabecera;
use App\Models\RecetaDetalle;
use App\Models\UMedida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'id_productos_api' => 'required|exists:productos,id_item',
            'nombre' => 'required|string|max:50',
            'cant_rendimiento' => 'required|numeric|min:0.01',
            'id_u_medidas' => 'required|exists:u_medidas,id_u_medidas',
            'constante_crecimiento' => 'required|numeric|min:0',
            'constante_peso_lata' => 'required|numeric|min:0',
            'ingredientes' => 'required|array|min:1',
            'ingredientes.*.id_productos_api' => 'required|exists:productos,id_item',
            'ingredientes.*.cantidad' => 'required|numeric|min:0.01',
            'ingredientes.*.cant_presentacion' => 'required|integer|min:1',
            'ingredientes.*.id_u_medidas' => 'required|exists:u_medidas,id_u_medidas'
        ], [
            'id_productos_api.required' => 'Debe seleccionar un producto principal',
            'ingredientes.required' => 'Debe agregar al menos un ingrediente',
            'ingredientes.*.cantidad.min' => 'La cantidad mínima es 0.01',
            'ingredientes.*.cant_presentacion.min' => 'La presentación mínima es 1'
        ]);

        try {
            DB::beginTransaction();

            // Verificar si ya existe la receta para este producto
            $recetaExistente = RecetaCabecera::where('id_productos_api', $request->id_productos_api)
                ->where('is_deleted', false)
                ->exists();

            if ($recetaExistente) {
                return back()->with('error', 'Ya existe una receta para este producto')->withInput();
            }

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

            foreach ($request->ingredientes as $ingrediente) {
                $producto = Producto::findOrFail($ingrediente['id_productos_api']);
                $uMedida = UMedida::findOrFail($ingrediente['id_u_medidas']);

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

            DB::commit();
            return redirect()->route('recetas.index')->with('success', 'Receta creada exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear receta: ' . $e->getMessage());
            return back()->with('error', 'Error al crear la receta: ' . $e->getMessage())->withInput();
        }
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
            'id_productos_api' => 'required|exists:productos,id_item',
            'nombre' => 'required|string|max:50',
            'cant_rendimiento' => 'required|numeric|min:0.01',
            'id_u_medidas' => 'required|exists:u_medidas,id_u_medidas',
            'constante_crecimiento' => 'required|numeric|min:0',
            'constante_peso_lata' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();
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

            DB::commit();
            return redirect()->route('recetas.index')->with('success', 'Receta actualizada exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar la receta: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $receta = RecetaCabecera::findOrFail($id);
            $receta->update(['is_deleted' => true]);
            $receta->delete();
            DB::commit();

            return redirect()->route('recetas.index')->with('success', 'Receta eliminada exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar la receta: ' . $e->getMessage());
        }
    }

    // Métodos AJAX
    public function buscarProductos(Request $request)
    {
        $request->validate(['term' => 'required|string|min:2']);

        $productos = Producto::where(function ($query) use ($request) {
            $query->where('nombre', 'ILIKE', "%{$request->term}%")
                ->orWhere('codigo', 'ILIKE', "%{$request->term}%");
        })
            ->take(15)
            ->get(['id_item as id', 'nombre as text', DB::raw('CAST(costo AS DECIMAL(10,2)) as costo'), 'codigo']);

        return response()->json($productos);
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
            Log::error("Error agregando ingrediente: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el ingrediente'
            ], 500);
        }
    }

    public function verificarProducto(Request $request)
    {
        $request->validate([
            'id_producto' => 'required|integer|exists:productos,id_item'
        ]);

        $tieneReceta = RecetaCabecera::where('id_productos_api', $request->id_producto)
            ->where('is_deleted', false)
            ->exists();

        return response()->json(['tiene_receta' => $tieneReceta]);
    }
}
