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
    // En el método index():
    public function index()
    {
        // Obtener recetas activas (status=true) primero, luego las inactivas
        $recetasActivas = RecetaCabecera::with(['area', 'producto', 'uMedida'])
            ->where('is_deleted', false)
            ->where('status', true)
            ->orderBy('nombre')
            ->get();

        $recetasInactivas = RecetaCabecera::with(['area', 'producto', 'uMedida'])
            ->where('is_deleted', false)
            ->where('status', false)
            ->orderBy('nombre')
            ->get();

        // Combinar las colecciones manteniendo el orden
        $recetas = $recetasActivas->merge($recetasInactivas);

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
        // Validación inicial del JSON de ingredientes
        $ingredientesData = $request->input('ingredientes');

        // Debug: Verificar los datos recibidos
        Log::info('Datos recibidos en store:', $request->all());
        Log::info('Ingredientes recibidos:', ['ingredientes' => $ingredientesData]);

        try {
            $ingredientes = json_decode($ingredientesData, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Error decodificando ingredientes:', ['error' => json_last_error_msg()]);
                return back()->with('error', 'Formato de ingredientes inválido')->withInput();
            }
        } catch (\Exception $e) {
            Log::error('Excepción al decodificar ingredientes:', ['error' => $e->getMessage()]);
            return back()->with('error', 'Error procesando ingredientes')->withInput();
        }

        // Validación de campos principales
        $validatedData = $request->validate([
            'id_areas' => 'required|exists:areas,id_areas',
            'id_productos_api' => 'required|exists:productos,id_item',
            'nombre' => 'required|string|max:200',
            'cant_rendimiento' => 'required|numeric|min:0.01',
            'id_u_medidas' => 'required|exists:u_medidas,id_u_medidas',
            'constante_crecimiento' => 'required|numeric|min:0',
            'constante_peso_lata' => 'required|numeric|min:0'
        ]);
        Log::info('Longitud del nombre:', ['length' => strlen($request->nombre)]);
        try {
            DB::beginTransaction();

            // Verificar receta existente
            if (RecetaCabecera::where('id_productos_api', $request->id_productos_api)
                ->where('is_deleted', false)->exists()
            ) {
                return back()->with('error', 'Ya existe una receta para este producto')->withInput();
            }

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

            // Log de la cabecera creada
            Log::info('Cabecera de receta creada:', [
                'id' => $cabecera->id_recetas,
                'data' => $cabecera->toArray()
            ]);

            // Procesar ingredientes
            $detalles = [];
            foreach ($ingredientes as $ingrediente) {
                $producto = Producto::find($ingrediente['id_productos_api']);

                if (!$producto) {
                    Log::error('Producto no encontrado para ingrediente:', ['id' => $ingrediente['id_productos_api']]);
                    throw new \Exception("Producto no encontrado para ingrediente: " . $ingrediente['id_productos_api']);
                }

                $detalle = RecetaDetalle::create([
                    'id_recetas_cab' => $cabecera->id_recetas,
                    'id_productos_api' => $ingrediente['id_productos_api'],
                    'nombre' => $producto->nombre,
                    'cantidad' => $ingrediente['cantidad'],
                    'cant_presentacion' => $ingrediente['cant_presentacion'],
                    'id_u_medidas' => $ingrediente['id_u_medidas'],
                    'costo_unitario' => $producto->costo,
                    'subtotal_receta' => ($ingrediente['cantidad'] / $ingrediente['cant_presentacion']) * $producto->costo
                ]);

                $detalles[] = $detalle->toArray();
            }

            // Log de los detalles creados
            Log::info('Detalles de receta creados:', [
                'cabecera_id' => $cabecera->id_recetas,
                'detalles' => $detalles
            ]);

            DB::commit();

            // Redirección con mensaje de éxito y opción para agregar más
            return redirect()->route('recetas.index')
                ->with('success', 'Receta creada exitosamente')
                ->with('show_continue_modal', true); // Flag para mostrar el modal

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear receta:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'ingredientes_data' => $ingredientes ?? null
            ]);
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

            // Actualizar cabecera
            $receta->update([
                'id_areas' => $request->id_areas,
                'id_productos_api' => $request->id_productos_api,
                'nombre' => $request->nombre,
                'cant_rendimiento' => $request->cant_rendimiento,
                'id_u_medidas' => $request->id_u_medidas,
                'constante_crecimiento' => $request->constante_crecimiento,
                'constante_peso_lata' => $request->constante_peso_lata
            ]);

            // Procesar ingredientes
            $ingredientes = json_decode($request->ingredientes, true);
            $ingredientesActualesIds = [];

            // Obtener los detalles existentes
            $detallesExistentes = $receta->detalles()->get()->keyBy('id_productos_api');

            foreach ($ingredientes as $ingrediente) {
                $producto = Producto::find($ingrediente['id_productos_api']);

                if (!$producto) {
                    throw new \Exception("Producto no encontrado para ingrediente: " . $ingrediente['id_productos_api']);
                }

                // Calcular subtotal
                $subtotal = ($ingrediente['cantidad'] / $ingrediente['cant_presentacion']) * $producto->costo;

                if (isset($ingrediente['esNuevo']) && $ingrediente['esNuevo']) {
                    // Crear nuevo ingrediente
                    RecetaDetalle::create([
                        'id_recetas_cab' => $receta->id_recetas,
                        'id_productos_api' => $ingrediente['id_productos_api'],
                        'nombre' => $producto->nombre,
                        'cantidad' => $ingrediente['cantidad'],
                        'cant_presentacion' => $ingrediente['cant_presentacion'],
                        'id_u_medidas' => $ingrediente['id_u_medidas'],
                        'costo_unitario' => $producto->costo,
                        'subtotal_receta' => $subtotal
                    ]);
                } else {
                    // Actualizar ingrediente existente si fue modificado
                    if (isset($ingrediente['fueModificado']) && $ingrediente['fueModificado']) {
                        if (isset($detallesExistentes[$ingrediente['id_productos_api']])) {
                            $detallesExistentes[$ingrediente['id_productos_api']]->update([
                                'cantidad' => $ingrediente['cantidad'],
                                'cant_presentacion' => $ingrediente['cant_presentacion'],
                                'id_u_medidas' => $ingrediente['id_u_medidas'],
                                'costo_unitario' => $producto->costo,
                                'subtotal_receta' => $subtotal
                            ]);
                        }
                    }
                }

                $ingredientesActualesIds[] = $ingrediente['id_productos_api'];
            }

            // Eliminar ingredientes que ya no están en la receta
            $receta->detalles()
                ->whereNotIn('id_productos_api', $ingredientesActualesIds)
                ->delete();

            DB::commit();

            return redirect()->route('recetas.index')
                ->with('success', 'Receta actualizada exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar receta:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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
                    'subtotal' => ($request->cantidad / $request->cant_presentacion) * $producto->costo
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


    // Nuevo método para cambiar el estado:
    public function toggleStatus($id)
    {
        try {
            $receta = RecetaCabecera::findOrFail($id);
            $receta->update([
                'status' => !$receta->status
            ]);

            return back()->with('success', 'Estado de la receta actualizado correctamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al cambiar el estado: ' . $e->getMessage());
        }
    }
}
