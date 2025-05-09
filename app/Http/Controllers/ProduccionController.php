<?php

namespace App\Http\Controllers;

use App\Models\ProduccionCabecera;
use App\Models\ProduccionDetalle;
use App\Models\PedidoDetalle;
use App\Models\RecetaCabecera;
use App\Models\EquipoCabecera;
use App\Models\RecetaDetalle;
use App\Models\Turno;
use App\Models\UMedida;
use App\Models\Area;
use App\Models\Usuario;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ProduccionController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();

        if ($usuario->id_roles == 3) {
            $fechaActual = Carbon::now()->toDateString();
            $idAreaUsuario = $usuario->id_areas;

            // Verificar si el usuario tiene un equipo activo
            $equipoActivo = EquipoCabecera::where('id_usuarios', $usuario->id_usuarios)
                ->where('status', true)
                ->where('is_deleted', false)
                ->whereDate('created_at', $fechaActual)
                ->with(['usuario', 'area', 'turno'])
                ->first();

            // Obtener solo recetas que no tienen producción enviada
            $pedidosDetalle = PedidoDetalle::with(['receta', 'receta.producto', 'receta.detalles', 'receta.detalles.producto', 'receta.instructivo', 'uMedida'])
                ->whereDate('created_at', $fechaActual)
                ->where('id_areas', $idAreaUsuario)
                ->where('is_deleted', false)
                ->whereHas('receta', function($query) {
                    $query->whereNotNull('id_recetas')
                        ->whereHas('producto')
                        ->whereHas('detalles');
                })
                ->whereDoesntHave('receta.produccionesDetalle', function($query) {
                    $query->where('es_enviado', true);
                })
                ->get(['id_pedidos_det', 'id_recetas', 'cantidad', 'es_personalizado', 'descripcion', 'foto_referencial', 'id_u_medidas', 'id_areas']);

            // Agrupar por receta y sumar cantidades
            $recetasAgrupadas = $pedidosDetalle->groupBy('id_recetas')->map(function ($items) {
                $receta = $items->first()->receta;

                return [
                    'cantidad_total' => $items->sum('cantidad'),
                    'es_personalizado' => $items->contains('es_personalizado', true),
                    'id_productos_api' => $items->first()->id_productos_api,
                    'id_u_medidas' => $items->first()->id_u_medidas,
                    'id_areas' => $items->first()->id_areas,
                    'receta' => $receta,
                    'pedidos' => $items
                ];
            })->filter(function ($item) {
                return !is_null($item['receta']) && $item['receta']->producto && $item['receta']->detalles;
            });

            $unidadesMedida = UMedida::activos()->get();

            return view('produccion.index-personal', compact('recetasAgrupadas', 'unidadesMedida', 'usuario', 'equipoActivo'));
        }

        // Para otros roles (administradores, etc.)
        $producciones = ProduccionCabecera::with(['usuario', 'turno', 'produccionesDetalle'])
            ->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->get();

        return view('produccion.index', compact('producciones'));
    }

public function guardarProduccionPersonal(Request $request)
{
    Log::info('Datos recibidos en guardarProduccionPersonal:', $request->all());
    Log::info('Inicio de guardarProduccionPersonal');
    Log::info('Datos recibidos:', $request->all());

    // Validación ajustada
    $validated = $request->validate([
        'id_equipos' => 'required|exists:equipos_cab,id_equipos_cab',
        'id_recetas_cab' => 'required|array',
        'id_recetas_cab.*' => 'exists:recetas_cab,id_recetas',
        'cantidad_producida_real' => 'required|array',
        'cantidad_producida_real.*' => 'numeric|min:0',
        'id_u_medidas_prodcc' => 'required|array',
        'id_u_medidas_prodcc.*' => 'exists:u_medidas,id_u_medidas',
        'es_iniciado' => 'sometimes|array',
        'es_terminado' => 'sometimes|array',
        'es_cancelado' => 'sometimes|array',
        'es_enviado' => 'required|array', // Cambiado a required
        'es_enviado.*' => 'required|boolean', // Asegurar que cada valor sea booleano
        'costo_diseño' => 'sometimes|array',
        'costo_diseño.*' => 'nullable|numeric|min:0',
        'observaciones' => 'sometimes|array',
        'observaciones.*' => 'nullable|string|max:500'
    ]);

    Log::info('Datos validados:', $validated);

    DB::beginTransaction();

    try {
        $equipo = EquipoCabecera::findOrFail($request->id_equipos);
        $usuario = auth()->user();

        // Crear la cabecera de producción
        $produccionCab = ProduccionCabecera::create([
            'id_equipos' => $equipo->id_equipos_cab,
            'id_turnos' => $equipo->id_turnos,
            'id_usuario' => $usuario->id_usuarios,
            'fecha' => Carbon::now()->format('Y-m-d'),
            'hora' => Carbon::now()->format('H:i:s'),
            'doc_interno' => 'PROD-' . Carbon::now()->format('YmdHis')
        ]);

        Log::info('Cabecera de producción creada:', ['id' => $produccionCab->id_produccion_cab]);

        // Procesar cada receta
        foreach ($request->id_recetas_cab as $idReceta) {
            $receta = RecetaCabecera::with(['detalles.producto'])->findOrFail($idReceta);
            
            // Obtener pedidos del día para esta receta
            $pedidos = PedidoDetalle::where('id_recetas', $idReceta)
                ->whereDate('created_at', Carbon::today())
                ->where('id_areas', $usuario->id_areas)
                ->get();

            $cantidadPedido = $pedidos->sum('cantidad');
            $cantidadEsperada = $cantidadPedido * $receta->constante_crecimiento;
            $cantidadProducida = $request->cantidad_producida_real[$idReceta] ?? $cantidadEsperada;

            // Buscar componente de harina
            $componenteHarina = $receta->detalles->first(function($item) {
                return $item->producto && stripos($item->producto->nombre, 'harina') !== false;
            });
            
            $cantHarina = $componenteHarina ? $componenteHarina->cantidad * $cantidadPedido : 0;

            // Calcular subtotal
            $subtotalReceta = 0;
            foreach ($receta->detalles as $detalle) {
                $subtotalReceta += $detalle->subtotal_receta * $cantidadPedido;
            }

            // Determinar estados con valores por defecto
    $esIniciado = (bool)($request->es_iniciado[$idReceta] ?? false);
    $esTerminado = (bool)($request->es_terminado[$idReceta] ?? false);
    $esCancelado = (bool)($request->es_cancelado[$idReceta] ?? false);
    $esEnviado = (bool)($request->es_enviado[$idReceta] ?? false);

    // Validar que no se pueda terminar sin iniciar
    if ($esTerminado && !$esIniciado) {
        throw new \Exception("No se puede terminar una producción sin iniciarla");
    }

    // Validar costo diseño para pedidos personalizados
    $costoDiseno = 0;
    $pedidosPersonalizados = $pedidos->where('es_personalizado', true);
    
    if ($pedidosPersonalizados->count() > 0 && $esTerminado) {
        foreach ($pedidosPersonalizados as $pedido) {
            $costo = $request->costo_diseño[$pedido->id_pedidos_det] ?? 0;
            if ($costo <= 0) {
                throw new \Exception("Debe ingresar un costo de diseño válido para los pedidos personalizados");
            }
            $costoDiseno += floatval($costo);
        }
    }

            // Crear detalle de producción
            $produccionDet = ProduccionDetalle::create([
                'id_produccion_cab' => $produccionCab->id_produccion_cab,
                'id_productos_api' => $receta->id_productos_api,
                'id_u_medidas' => $receta->id_u_medidas,
                'id_u_medidas_prodcc' => $request->id_u_medidas_prodcc[$idReceta],
                'id_recetas_cab' => $receta->id_recetas,
                'id_recetas_det' => $componenteHarina ? $componenteHarina->id_recetas_det : null,
                'id_areas' => $receta->id_areas,
                'cantidad_pedido' => $cantidadPedido,
                'cantidad_esperada' => $cantidadEsperada,
                'cantidad_producida_real' => $cantidadProducida,
                'es_iniciado' => $esIniciado,
                'es_terminado' => $esTerminado,
                'es_cancelado' => $esCancelado,
                'es_enviado' => $esEnviado,
                'costo_diseño' => $costoDiseno,
                'subtotal_receta' => $subtotalReceta,
                'total_receta' => $subtotalReceta + $costoDiseno,
                'cant_harina' => $cantHarina,
                'observaciones' => $request->observaciones[$idReceta] ?? null
            ]);

            Log::info('Detalle de producción creado:', [
                'id' => $produccionDet->id_produccion_det,
                'receta_id' => $idReceta,
                'cantidad_producida' => $cantidadProducida
            ]);

            // Actualizar estado de los pedidos
            $this->actualizarEstadosPedidos($pedidos, $esIniciado, $esTerminado, $esCancelado);
        }

        DB::commit();
        
        $enviados = $produccionCab->produccionesDetalle()->where('es_enviado', true)->count();
        $total = $produccionCab->produccionesDetalle()->count();

        return redirect()->route('produccion.index')
            ->with('success', "Producción registrada correctamente. Se enviaron {$enviados} de {$total} registros.");
            
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error al registrar la producción: ' . $e->getMessage(), [
            'exception' => $e,
            'trace' => $e->getTraceAsString()
        ]);
        return back()->with('error', 'Error al registrar la producción: ' . $e->getMessage())
                    ->withInput();
    }
}

// Métodos auxiliares
protected function calcularCostoDiseno($request, $idReceta)
{
    if (empty($request->costo_diseño)) return 0;
    
    $total = 0;
    foreach ($request->costo_diseño as $pedidoId => $costo) {
        $pedido = PedidoDetalle::find($pedidoId);
        if ($pedido && $pedido->id_recetas == $idReceta) {
            $total += (float)$costo;
        }
    }
    return $total;
}

protected function calcularSubtotal($receta, $cantidadPedido)
{
    return $receta->detalles->sum(fn($detalle) => $detalle->subtotal_receta * $cantidadPedido);
}

protected function calcularTotal($receta, $cantidadPedido, $request, $idReceta)
{
    $subtotal = $this->calcularSubtotal($receta, $cantidadPedido);
    $costoDiseno = $this->calcularCostoDiseno($request, $idReceta);
    return $subtotal + $costoDiseno;
}

protected function calcularHarina($receta, $cantidadPedido)
{
    $componenteHarina = $receta->detalles->first(function($item) {
        return $item->producto && stripos($item->producto->nombre, 'harina') !== false;
    });
    return $componenteHarina ? $componenteHarina->cantidad * $cantidadPedido : 0;
}

    protected function actualizarEstadosPedidos($pedidosDetalle, $esIniciado, $esTerminado, $esCancelado)
    {
        $estado = null;

        if ($esCancelado) {
            $estado = 5; // Cancelado
        } elseif ($esTerminado) {
            $estado = 4; // Terminado
        } elseif ($esIniciado) {
            $estado = 3; // En proceso
        }
        
        if ($estado !== null) {
            foreach ($pedidosDetalle as $pedidoDet) {
                $pedidoDet->update(['id_estados' => $estado]);
            }
        }
    }

    // Modificación en el método procesarRecetaProduccion()
    protected function procesarRecetaProduccion($idReceta, $key, $request, $produccionCabecera, $usuario)
    {
        // Obtener la receta cabecera con sus relaciones
        $recetaCabecera = RecetaCabecera::with(['producto', 'detalles.producto'])
            ->findOrFail($idReceta);

        // Obtener los detalles de pedidos para esta receta
        $pedidosDetalle = PedidoDetalle::where('id_recetas', $idReceta)
            ->whereDate('created_at', Carbon::now()->toDateString())
            ->where('id_areas', $usuario->id_areas)
            ->get();

        // Calcular cantidad total pedida
        $cantidadPedido = $pedidosDetalle->sum('cantidad');

        // Calcular cantidad esperada (cantidad pedida * constante crecimiento)
        $cantidadEsperada = $cantidadPedido * $recetaCabecera->constante_crecimiento;

        // Obtener el componente de harina si existe
        $recetaHarina = $recetaCabecera->detalles
            ->first(function ($detalle) {
                return $detalle->producto && stripos($detalle->producto->nombre, 'harina') !== false;
            });

        $cantHarina = $recetaHarina ? $recetaHarina->cantidad * $cantidadPedido : 0;

        // Calcular subtotal receta (suma de subtotales de todos los detalles * cantidad pedida)
        $subtotalReceta = 0;
        foreach ($recetaCabecera->detalles as $detalle) {
            $subtotalReceta += $detalle->subtotal_receta * $cantidadPedido;
        }

        // Determinar si hay algún pedido personalizado
        $esPersonalizado = $pedidosDetalle->contains('es_personalizado', true);

        // Determinar estados
        $esIniciado = isset($request->es_iniciado[$key]) && $request->es_iniciado[$key] == 'on';
        $esTerminado = isset($request->es_terminado[$key]) && $request->es_terminado[$key] == 'on';
        $esCancelado = isset($request->es_cancelado[$key]) && $request->es_cancelado[$key] == 'on';

        // Validar que no se pueda terminar sin iniciar
        if ($esTerminado && !$esIniciado) {
            throw new \Exception("No se puede terminar una producción sin iniciarla");
        }

        // Validar costo diseño para pedidos personalizados
        $costoDiseno = 0;
        if ($esPersonalizado && $esTerminado) {
            $costoDiseno = $request->costo_diseño[$key] ?? 0;
            if ($costoDiseno <= 0) {
                throw new \Exception("Debe ingresar un costo de diseño válido para pedidos personalizados");
            }
        }

        // Crear o actualizar el detalle de producción
        $produccionDetalle = ProduccionDetalle::updateOrCreate(
            [
                'id_produccion_cab' => $produccionCabecera->id_produccion_cab,
                'id_recetas_cab' => $idReceta
            ],
            [
                'id_productos_api' => $recetaCabecera->id_productos_api,
                'id_u_medidas' => $pedidosDetalle->first()->id_u_medidas,
                'id_u_medidas_prodcc' => $request->id_u_medidas_prodcc[$key],
                'id_recetas_det' => $recetaHarina ? $recetaHarina->id_recetas_det : null,
                'id_areas' => $usuario->id_areas,
                'cantidad_pedido' => $cantidadPedido,
                'cantidad_esperada' => $cantidadEsperada,
                'cantidad_producida_real' => $request->cantidad_producida_real[$key],
                'es_iniciado' => $esIniciado,
                'es_terminado' => $esTerminado,
                'es_cancelado' => $esCancelado,
                'costo_diseño' => $esPersonalizado ? $costoDiseno : 0,
                'subtotal_receta' => $subtotalReceta,
                'total_receta' => $subtotalReceta + ($esPersonalizado ? $costoDiseno : 0),
                'cant_harina' => $cantHarina
            ]
        );

        // Actualizar estados de los pedidos
        $this->actualizarEstadosPedidos($pedidosDetalle, $esIniciado, $esTerminado, $esCancelado);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $turnos = Turno::where('status', true)->where('is_deleted', false)->get();
        $equipos = EquipoCabecera::where('status', true)->where('is_deleted', false)->get();

        return view('produccion.create', compact('turnos', 'equipos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_equipos' => 'required|exists:equipos_cab,id_equipos',
            'id_turnos' => 'required|exists:turnos,id_turnos',
            'fecha' => 'required|date',
            'hora' => 'required',
            'doc_interno' => 'nullable|string|max:50'
        ]);

        $produccion = ProduccionCabecera::create([
            'id_equipos' => $request->id_equipos,
            'id_turnos' => $request->id_turnos,
            'id_usuario' => Auth::id(),
            'fecha' => $request->fecha,
            'hora' => $request->hora,
            'doc_interno' => $request->doc_interno
        ]);

        return redirect()->route('produccion.show', $produccion->id_produccion_cab)
            ->with('success', 'Producción creada exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(ProduccionCabecera $produccion)
    {
        $produccion->load(['usuario', 'turno', 'equipo', 'produccionesDetalle']);

        return view('produccion.show', compact('produccion'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProduccionCabecera $produccion)
    {
        $turnos = Turno::where('status', true)->where('is_deleted', false)->get();
        $equipos = EquipoCabecera::where('status', true)->where('is_deleted', false)->get();

        return view('produccion.edit', compact('produccion', 'turnos', 'equipos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProduccionCabecera $produccion)
    {
        $request->validate([
            'id_equipos' => 'required|exists:equipos_cab,id_equipos',
            'id_turnos' => 'required|exists:turnos,id_turnos',
            'fecha' => 'required|date',
            'hora' => 'required',
            'doc_interno' => 'nullable|string|max:50'
        ]);

        $produccion->update([
            'id_equipos' => $request->id_equipos,
            'id_turnos' => $request->id_turnos,
            'fecha' => $request->fecha,
            'hora' => $request->hora,
            'doc_interno' => $request->doc_interno
        ]);

        return redirect()->route('produccion.show', $produccion->id_produccion_cab)
            ->with('success', 'Producción actualizada exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProduccionCabecera $produccion)
    {
        $produccion->delete();

        return redirect()->route('produccion.index')
            ->with('success', 'Producción eliminada exitosamente');
    }

    /**
     * Guarda la producción realizada por el personal
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
   

    /**
     * Exportar producción a PDF
     */
    public function exportarPdf($id)
    {
        $produccion = ProduccionCabecera::with(['usuario', 'turno', 'equipo', 'produccionesDetalle'])
            ->findOrFail($id);

        $pdf = PDF::loadView('produccion.pdf', compact('produccion'));

        return $pdf->download('produccion-' . $produccion->id_produccion_cab . '.pdf');
    }

    /**
     * Obtener datos para gráficos de producción
     */
    public function obtenerDatosGraficos(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', Carbon::now()->subWeek()->toDateString());
        $fechaFin = $request->input('fecha_fin', Carbon::now()->toDateString());

        $datos = ProduccionDetalle::select(
            DB::raw('DATE(produccion_cab.fecha) as fecha'),
            DB::raw('SUM(cantidad_producida_real) as total_producido'),
            DB::raw('SUM(cantidad_esperada) as total_esperado'),
            'areas.nombre as area'
        )
            ->join('produccion_cab', 'produccion_det.id_produccion_cab', '=', 'produccion_cab.id_produccion_cab')
            ->join('areas', 'produccion_det.id_areas', '=', 'areas.id_areas')
            ->whereBetween('produccion_cab.fecha', [$fechaInicio, $fechaFin])
            ->where('produccion_det.es_cancelado', false)
            ->groupBy('fecha', 'area')
            ->orderBy('fecha')
            ->get();

        return response()->json($datos);
    }

    /**
     * Mostrar reportes de producción
     */
    public function reportes(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', Carbon::now()->subWeek()->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', Carbon::now()->format('Y-m-d'));

        $datosGraficos = ProduccionDetalle::select(
            DB::raw('DATE(produccion_cab.fecha) as fecha'),
            DB::raw('SUM(cantidad_producida_real) as total_producido'),
            DB::raw('SUM(cantidad_esperada) as total_esperado'),
            'areas.nombre as area'
        )
            ->join('produccion_cab', 'produccion_det.id_produccion_cab', '=', 'produccion_cab.id_produccion_cab')
            ->join('areas', 'produccion_det.id_areas', '=', 'areas.id_areas')
            ->whereBetween('produccion_cab.fecha', [$fechaInicio, $fechaFin])
            ->where('produccion_det.es_cancelado', false)
            ->groupBy('fecha', 'area')
            ->orderBy('fecha')
            ->get();

        return view('produccion.reportes', compact('fechaInicio', 'fechaFin', 'datosGraficos'));
    }
}
