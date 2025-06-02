<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Producto;
use App\Models\RecetaCabecera;
use App\Models\RecetaDetalle;
use App\Models\PedidoDetalle;
use App\Models\ProduccionDetalle;
use App\Models\UMedida;
use App\Models\RecetaInstructivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RecetaController extends Controller
{
    /**
     * Muestra el listado de recetas ordenadas por estado (activas primero) y nombre
     * Filtra las recetas según el rol del usuario:
     * - Admin (rol 1): ve todas las recetas
     * - Supervisor (rol 2): ve todas las recetas pero sin acciones
     * - Personal (rol 3): solo ve recetas de su área
     * 
     * @return \Illuminate\View\View Vista index con las recetas filtradas según rol
     */
    public function index()
    {
        // Obtener el usuario autenticado y su rol
        $user = auth()->user();
        $idRol = $user->id_roles;
        $idArea = $user->id_areas;
        
        // Consulta base para recetas no eliminadas
        $query = RecetaCabecera::with(['area', 'producto', 'uMedida'])
            ->where('is_deleted', false);
            
        // Si es personal (rol 3), filtrar por su área
        if ($idRol == 3) {
            $query->where('id_areas', $idArea);
        }
        
        // Obtener recetas activas ordenadas por nombre
        $recetasActivas = (clone $query)
            ->where('status', true)
            ->orderBy('nombre')
            ->get();

        // Obtener recetas inactivas ordenadas por nombre
        $recetasInactivas = (clone $query)
            ->where('status', false)
            ->orderBy('nombre')
            ->get();

        // Combinar las colecciones manteniendo el orden (activas primero)
        $recetas = $recetasActivas->merge($recetasInactivas);
        
        // Pasar el rol del usuario a la vista para controlar los botones de acción
        return view('recetas.index', compact('recetas', 'idRol'));
    }

    /**
     * Muestra el formulario para crear una nueva receta
     * 
     * @return \Illuminate\View\View Vista create con áreas y unidades de medida
     */
    public function create()
    {
        // Obtener áreas activas no eliminadas
        $areas = Area::where('status', true)->whereNull('deleted_at')->get();
        // Obtener todas las unidades de medida
        $unidades = UMedida::all();

        return view('recetas.create', compact('areas', 'unidades'));
    }

    /**
     * Almacena una nueva receta en la base de datos
     * 
     * @param  \Illuminate\Http\Request  $request Datos del formulario
     * @return \Illuminate\Http\RedirectResponse Redirección con mensaje de éxito/error
     */
    public function store(Request $request)
    {
        // Validación inicial del JSON de ingredientes
        $ingredientesData = $request->input('ingredientes');

        // Registrar datos recibidos para depuración
        Log::info('Datos recibidos en store:', $request->all());
        Log::info('Ingredientes recibidos:', ['ingredientes' => $ingredientesData]);

        try {
            // Decodificar JSON de ingredientes
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
            DB::beginTransaction(); // Iniciar transacción

            // Verificar si ya existe una receta activa para este producto
            if (RecetaCabecera::where('id_productos_api', $request->id_productos_api)
                ->where('is_deleted', false)->exists()
            ) {
                return back()->with('error', 'Ya existe una receta para este producto')->withInput();
            }

            // Obtener el costo total de la receta del formulario
            $costoRecetaTotal = $request->input('costo_receta', 0);
            Log::info('Costo total de la receta recibido:', ['costo_receta' => $costoRecetaTotal]);

            // Crear cabecera de la receta
            $cabecera = RecetaCabecera::create([
                'id_areas' => $request->id_areas,
                'id_productos_api' => $request->id_productos_api,
                'nombre' => $request->nombre,
                'cant_rendimiento' => $request->cant_rendimiento,
                'id_u_medidas' => $request->id_u_medidas,
                'constante_crecimiento' => $request->constante_crecimiento,
                'constante_peso_lata' => $request->constante_peso_lata,
                'costo_receta' => $costoRecetaTotal, // Guardar el costo total de la receta
                'status' => true, // Por defecto se crea como activa
                'is_deleted' => false // No eliminada
            ]);

            // Registrar creación de cabecera
            Log::info('Cabecera de receta creada:', [
                'id' => $cabecera->id_recetas,
                'data' => $cabecera->toArray()
            ]);

            // Procesar cada ingrediente de la receta
            $detalles = [];
            foreach ($ingredientes as $ingrediente) {
                $producto = Producto::find($ingrediente['id_productos_api']);

                if (!$producto) {
                    Log::error('Producto no encontrado para ingrediente:', ['id' => $ingrediente['id_productos_api']]);
                    throw new \Exception("Producto no encontrado para ingrediente: " . $ingrediente['id_productos_api']);
                }

                // Crear detalle de receta
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

            // Registrar detalles creados
            Log::info('Detalles de receta creados:', [
                'cabecera_id' => $cabecera->id_recetas,
                'detalles' => $detalles
            ]);

            DB::commit(); // Confirmar transacción

            // Redireccionar con mensaje de éxito y flag para mostrar modal de continuar
            return redirect()->route('recetas.index')
                ->with('success', 'Receta creada exitosamente')
                ->with('show_continue_modal', true);
        } catch (\Exception $e) {
            DB::rollBack(); // Revertir transacción en caso de error
            Log::error('Error al crear receta:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'ingredientes_data' => $ingredientes ?? null
            ]);
            return back()->with('error', 'Error al crear la receta: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Muestra los detalles de una receta específica
     * 
     * @param  int  $id ID de la receta
     * @return \Illuminate\View\View Vista show con los datos de la receta
     */
    public function show($id)
    {
        $receta = RecetaCabecera::with(['instructivo', 'area', 'producto', 'uMedida', 'detalles.producto', 'detalles.uMedida'])
            ->findOrFail($id);
        
        return view('recetas.show', compact('receta'));
    }

    /**
     * Muestra el formulario para editar una receta existente
     * 
     * @param  int  $id ID de la receta
     * @return \Illuminate\View\View Vista edit con los datos de la receta
     */
    public function edit($id)
    {
        // Obtener receta con sus detalles
        $receta = RecetaCabecera::with(['detalles'])->findOrFail($id);
        // Obtener áreas activas no eliminadas
        $areas = Area::where('status', true)->whereNull('deleted_at')->get();
        // Obtener todas las unidades de medida
        $unidades = UMedida::all();

        return view('recetas.edit', compact('receta', 'areas', 'unidades'));
    }

    /**
     * Actualiza una receta existente en la base de datos
     * 
     * @param  \Illuminate\Http\Request  $request Datos del formulario
     * @param  int  $id ID de la receta
     * @return \Illuminate\Http\RedirectResponse Redirección con mensaje de éxito/error
     */
    public function update(Request $request, $id)
    {
        // Validar datos del formulario
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
            DB::beginTransaction(); // Iniciar transacción

            // Obtener receta existente
            $receta = RecetaCabecera::findOrFail($id);

            // Actualizar datos principales de la receta
            $receta->update([
                'id_areas' => $request->id_areas,
                'id_productos_api' => $request->id_productos_api,
                'nombre' => $request->nombre,
                'cant_rendimiento' => $request->cant_rendimiento,
                'id_u_medidas' => $request->id_u_medidas,
                'constante_crecimiento' => $request->constante_crecimiento,
                'constante_peso_lata' => $request->constante_peso_lata,
                'costo_receta' => $request->costo_receta
            ]);

            // Procesar ingredientes del formulario
            $ingredientes = json_decode($request->ingredientes, true);
            $ingredientesActualesIds = [];

            // Obtener detalles existentes indexados por ID de producto
            $detallesExistentes = $receta->detalles()->get()->keyBy('id_productos_api');

            foreach ($ingredientes as $ingrediente) {
                $producto = Producto::find($ingrediente['id_productos_api']);

                if (!$producto) {
                    throw new \Exception("Producto no encontrado para ingrediente: " . $ingrediente['id_productos_api']);
                }

                // Calcular subtotal del ingrediente
                $subtotal = ($ingrediente['cantidad'] / $ingrediente['cant_presentacion']) * $producto->costo;

                if (isset($ingrediente['esNuevo']) && $ingrediente['esNuevo']) {
                    // Crear nuevo detalle de ingrediente
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

            // Eliminar ingredientes que ya no están en la receta (usando SoftDelete)
            $detallesAEliminar = $receta->detalles()
                ->whereNotIn('id_productos_api', $ingredientesActualesIds)
                ->whereNull('deleted_at')
                ->get();
            
            // Registrar los ingredientes que se eliminarán
            Log::info('Ingredientes a eliminar:', [
                'cantidad' => $detallesAEliminar->count(),
                'ids' => $detallesAEliminar->pluck('id_recetas_det')->toArray()
            ]);
            
            // Eliminar uno por uno para asegurar que se aplique el SoftDelete
            foreach ($detallesAEliminar as $detalle) {
                Log::info('Eliminando ingrediente:', [
                    'id' => $detalle->id_recetas_det,
                    'producto' => $detalle->nombre
                ]);
                $detalle->delete(); // Esto establece deleted_at
            }

            // Recalcular el costo total de la receta considerando solo ingredientes no eliminados
            $costoTotal = $receta->detalles()
                ->whereNull('deleted_at')
                ->sum('subtotal_receta');
                
            Log::info('Costo total recalculado:', ['costo' => $costoTotal]);
            
            // Actualizar el costo total de la receta
            $receta->update([
                'costo_receta' => $costoTotal
            ]);
            
            // ¡CORREGIDO! Faltaba confirmar la transacción
            DB::commit();
            
            // Redireccionar con mensaje de éxito
            return redirect()->route('recetas.index')
                ->with('success', 'Receta actualizada exitosamente');
                
        } catch (\Exception $e) {
            DB::rollBack(); // Revertir transacción en caso de error
            Log::error('Error al actualizar receta:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error al actualizar la receta: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Elimina (soft delete) una receta específica
     * 
     * @param  int  $id ID de la receta
     * @return \Illuminate\Http\RedirectResponse Redirección con mensaje de éxito/error
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction(); // Iniciar transacción

            // Obtener receta y marcarla como eliminada
            $receta = RecetaCabecera::findOrFail($id);
            $receta->update([
                'is_deleted' => true, // Marcar como eliminada
                'deleted_at' => now() // Registrar fecha de eliminación
            ]);
            $receta->delete(); // Soft delete

            DB::commit(); // Confirmar transacción

            return redirect()->route('recetas.index')
                ->with('success', 'Receta eliminada exitosamente');
        } catch (\Exception $e) {
            DB::rollBack(); // Revertir transacción en caso de error
            return back()->with('error', 'Error al eliminar la receta: ' . $e->getMessage());
        }
    }

    // =============================================
    // MÉTODOS AJAX PARA INTERACCIÓN EN TIEMPO REAL
    // =============================================

    /**
     * Busca productos coincidentes con el término de búsqueda (AJAX)
     * 
     * @param  \Illuminate\Http\Request  $request Término de búsqueda
     * @return \Illuminate\Http\JsonResponse Lista de productos coincidentes
     */
    public function buscarProductos(Request $request)
    {
        // Validar término de búsqueda (mínimo 2 caracteres)
        $request->validate(['term' => 'required|string|min:2']);

        // Buscar productos por nombre o código (case insensitive)
        $productos = Producto::where(function ($query) use ($request) {
            $query->where('nombre', 'ILIKE', "%{$request->term}%")
                ->orWhere('codigo', 'ILIKE', "%{$request->term}%");
        })
            ->take(15) // Limitar a 15 resultados
            ->get([
                'id_item as id',
                'nombre as text',
                DB::raw('CAST(costo AS DECIMAL(10,2)) as costo'),
                'codigo'
            ]);

        return response()->json($productos);
    }

    /**
     * Procesa un ingrediente para agregar a la receta (AJAX)
     * 
     * @param  \Illuminate\Http\Request  $request Datos del ingrediente
     * @return \Illuminate\Http\JsonResponse Datos del ingrediente procesado o error
     */
    public function agregarIngrediente(Request $request)
    {
        // Validar datos del ingrediente
        $request->validate([
            'id_productos_api' => 'required|exists:productos,id_item',
            'cantidad' => 'required|numeric|min:0.01',
            'cant_presentacion' => 'required|integer|min:1',
            'id_u_medidas' => 'required|exists:u_medidas,id_u_medidas'
        ]);

        try {
            // Obtener producto y unidad de medida
            $producto = Producto::findOrFail($request->id_productos_api);
            $uMedida = UMedida::findOrFail($request->id_u_medidas);

            // Calcular subtotal
            $subtotal = ($request->cantidad / $request->cant_presentacion) * $producto->costo;

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $producto->id_item,
                    'nombre' => $producto->nombre,
                    'cantidad' => $request->cantidad,
                    'cant_presentacion' => $request->cant_presentacion,
                    'u_medida' => $uMedida->nombre,
                    'costo_unitario' => $producto->costo,
                    'subtotal' => $subtotal
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

    /**
     * Verifica si un producto ya tiene receta asociada (AJAX)
     * 
     * @param  \Illuminate\Http\Request  $request ID del producto
     * @return \Illuminate\Http\JsonResponse Indicador booleano de existencia
     */
    public function verificarProducto(Request $request)
    {
        // Validar ID del producto
        $request->validate([
            'id_producto' => 'required|integer|exists:productos,id_item'
        ]);

        // Verificar si existe receta activa para el producto
        $tieneReceta = RecetaCabecera::where('id_productos_api', $request->id_producto)
            ->where('is_deleted', false) // Solo recetas no eliminadas
            ->exists();

        return response()->json(['tiene_receta' => $tieneReceta]);
    }

    /**
     * Cambia el estado (activo/inactivo) de una receta
     * 
     * @param  int  $id ID de la receta
     * @return \Illuminate\Http\RedirectResponse Redirección con mensaje de éxito/error
     */
    public function toggleStatus($id)
    {
        try {
            // Obtener receta y cambiar su estado
            $receta = RecetaCabecera::findOrFail($id);
            $receta->update([
                'status' => !$receta->status // Invertir estado actual
            ]);

            return back()->with('success', 'Estado de la receta actualizado correctamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al cambiar el estado: ' . $e->getMessage());
        }
    }

// Mostrar formulario para crear instructivo
public function showCreateInstructivo($idReceta)
{
    $receta = RecetaCabecera::with(['producto', 'detalles.producto', 'detalles.uMedida'])
        ->findOrFail($idReceta);
    
    return view('recetas.create-instructivo', compact('receta'));
}

// Guardar instructivo
// En el método storeInstructivo, actualiza la validación:
public function storeInstructivo(Request $request, $idReceta)
{
    Log::info('Datos recibidos en storeInstructivo:', $request->all());
    
    $validated = $request->validate([
        'titulo' => 'required|string|max:200',
        'pasos' => 'required|array|min:1',
        'pasos.*.contenido' => 'required|string',
        'pasos.*.ingredientes' => 'present|array', // Cambiado de 'sometimes' a 'present'
    ]);

    try {
        DB::beginTransaction();

        $receta = RecetaCabecera::findOrFail($idReceta);
        
        // Desactivar instructivos previos
        RecetaInstructivo::where('id_recetas', $idReceta)
            ->update(['is_active' => false]);

        // Obtener la próxima versión
        $version = RecetaInstructivo::where('id_recetas', $idReceta)->max('version') ?? 0;
        $version++;

        // Asegurar que todos los ingredientes sean arrays válidos
        $pasos = collect($validated['pasos'])->map(function($paso) {
            return [
                'contenido' => $paso['contenido'],
                'ingredientes' => is_array($paso['ingredientes']) ? $paso['ingredientes'] : []
            ];
        })->toArray();

        // Crear nuevo instructivo
        $instructivo = RecetaInstructivo::create([
            'id_recetas' => $idReceta,
            'titulo' => $validated['titulo'],
            'instrucciones' => $pasos,
            'version' => $version,
            'is_active' => true
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'redirect' => route('recetas.show', $idReceta)
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error al crear instructivo:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request' => $request->all()
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Error al crear el instructivo: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Muestra el instructivo de una receta adaptado a la cantidad producida
 *
 * @param  \Illuminate\Http\Request  $request
 * @param  int|null  $id
 * @return \Illuminate\Http\Response
 */
public function showInstructivo(Request $request, $id = null)
{
    // Obtener el ID de la receta
    $idReceta = $id ?? $request->query('id_receta');
    
    if (!$idReceta && $request->route('id')) {
        $idReceta = $request->route('id');
    }

    if (!$idReceta) {
        abort(400, 'Parámetro id_receta es requerido');
    }

    // Obtener la receta con relaciones
    $receta = RecetaCabecera::with(['instructivo', 'area', 'uMedida', 'detalles.producto', 'detalles.uMedida'])
        ->findOrFail($idReceta);
    
    if (!$receta->instructivo) {
        return redirect()->route('recetas.show', $receta->id_recetas)
            ->with('warning', 'Esta receta no tiene instructivo registrado');
    }
    
    // Obtener el ID de la producción específica si se proporciona
    $idProduccionDet = $request->query('id_produccion_det');
    
    // Obtener el ID del pedido si se proporciona
    $idPedido = $request->query('id_pedido');
    
    // Obtener cantidades del request
    $cantidadPedido = $request->query('cantidad_pedido');
    $cantidadEsperada = $request->query('cantidad_esperada');
    
    // Si no se proporcionaron las cantidades, calcularlas
    if (!$cantidadPedido || !$cantidadEsperada) {
        // Si se proporciona el ID de producción específica, usar ese dato
        if ($idProduccionDet) {
            $produccion = ProduccionDetalle::findOrFail($idProduccionDet);
            $cantidadPedido = $produccion->cantidad_pedido;
            $cantidadEsperada = $produccion->cantidad_esperada;
        } else if ($idPedido) {
            // Si es un pedido específico, obtener su cantidad
            $pedido = PedidoDetalle::where('id_pedidos_det', $idPedido)
                ->where('id_recetas', $idReceta)
                ->first();
                
            if (!$pedido) {
                abort(404, 'Pedido no encontrado');
            }
            
            $cantidadPedido = $pedido->cantidad;
            $cantidadEsperada = ($receta->id_areas == 1)
                ? $cantidadPedido * $receta->constante_peso_lata
                : $cantidadPedido;
        } else {
            // Si no se proporciona ningún ID específico, sumar todos los pedidos del día
            $cantidadPedido = PedidoDetalle::where('id_recetas', $idReceta)
                ->whereDate('created_at', Carbon::today())
                ->where('is_deleted', false)
                ->sum('cantidad');

            $cantidadEsperada = ($receta->id_areas == 1)
                ? $cantidadPedido * $receta->constante_peso_lata
                : $cantidadPedido;
        }
    }
    
    // Calcular el factor según el área
    $factor = $cantidadEsperada / $receta->cant_rendimiento;
    
    // Adaptar cantidades de ingredientes
    $ingredientesAdaptados = $receta->detalles->map(function($detalle) use ($factor) {
        return [
            'id' => $detalle->id_recetas_det,
            'nombre' => $detalle->producto->nombre,
            'cantidad' => $detalle->cantidad * $factor,
            'u_medida' => $detalle->uMedida->nombre,
            'costo_unitario' => $detalle->costo_unitario,
            'cantidad_base' => $detalle->cantidad,
            'factor' => $factor
        ];
    });
    
    if ($request->ajax()) {
        return view('recetas.partials.instructivo-modal', [
            'instructivo' => $receta->instructivo,
            'receta' => $receta,
            'ingredientesAdaptados' => $ingredientesAdaptados,
            'cantidadProduccion' => $cantidadPedido,
            'cantidadEsperada' => $cantidadEsperada,
            'factor' => $factor
        ]);
    }
    
    return view('recetas.show-instructivo', [
        'instructivo' => $receta->instructivo,
        'receta' => $receta,
        'ingredientesAdaptados' => $ingredientesAdaptados,
        'cantidadProduccion' => $cantidadPedido,
        'cantidadEsperada' => $cantidadEsperada,
        'factor' => $factor
    ]);
}

/**
 * Muestra el formulario para editar un instructivo existente
 * 
 * @param int $idReceta ID de la receta
 * @param int $idInstructivo ID del instructivo
 * @return \Illuminate\View\View Vista de edición del instructivo
 */
public function editInstructivo($idReceta, $idInstructivo)
{
    $receta = RecetaCabecera::with(['producto', 'detalles.producto', 'detalles.uMedida'])
        ->findOrFail($idReceta);
    
    $instructivo = RecetaInstructivo::findOrFail($idInstructivo);
    
    return view('recetas.edit-instructivo', compact('receta', 'instructivo'));
}

/**
 * Actualiza un instructivo existente
 * 
 * @param \Illuminate\Http\Request $request Datos del formulario
 * @param int $idReceta ID de la receta
 * @param int $idInstructivo ID del instructivo
 * @return \Illuminate\Http\JsonResponse Respuesta JSON con resultado
 */
public function updateInstructivo(Request $request, $idReceta, $idInstructivo)
{
    Log::info('Datos recibidos en updateInstructivo:', $request->all());
    
    $validated = $request->validate([
        'titulo' => 'required|string|max:200',
        'pasos' => 'required|array|min:1',
        'pasos.*.contenido' => 'required|string',
        'pasos.*.ingredientes' => 'present|array',
    ]);

    try {
        DB::beginTransaction();

        $instructivo = RecetaInstructivo::where('id_recetas', $idReceta)
            ->findOrFail($idInstructivo);
        
        // Asegurar que todos los ingredientes sean arrays válidos
        $pasos = collect($validated['pasos'])->map(function($paso) {
            return [
                'contenido' => $paso['contenido'],
                'ingredientes' => is_array($paso['ingredientes']) ? $paso['ingredientes'] : []
            ];
        })->toArray();

        // Actualizar instructivo
        $instructivo->update([
            'titulo' => $validated['titulo'],
            'instrucciones' => $pasos
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'redirect' => route('recetas.show', $idReceta)
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error al actualizar instructivo:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request' => $request->all()
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Error al actualizar el instructivo: ' . $e->getMessage()
        ], 500);
    }
}
}

