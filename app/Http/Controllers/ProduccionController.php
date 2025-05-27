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
    public function indexPersonal()
    {
        // Verificar que el usuario tenga el rol de Personal (id_roles === 3)
        $usuario = Auth::user();
        if ($usuario->id_roles !== 3) {
            return redirect()->route('produccion.index')
                ->with('error', 'No tienes permisos para acceder a esta vista. Usa la vista de administrador.');
        }
        
        $estadoActual = request()->query('estado', 'pendientes');
        $fechaActual = Carbon::now()->toDateString();
        $idAreaUsuario = $usuario->id_areas;

        // Verificar equipo activo del usuario
        $equipoActivo = EquipoCabecera::where('id_usuarios', $usuario->id_usuarios)
            ->where('status', true)
            ->where('is_deleted', false)
            ->whereDate('created_at', $fechaActual)
            ->with(['usuario', 'area', 'turno'])
            ->first();

        // Obtener todos los pedidos del día para el área del usuario
        $pedidosDetalle = PedidoDetalle::with([
                'receta', 
                'receta.producto', 
                'receta.detalles', 
                'receta.detalles.producto', 
                'receta.instructivo', 
                'uMedida'
            ])
            ->whereDate('created_at', $fechaActual)
            ->where('id_areas', $idAreaUsuario)
            ->where('is_deleted', false)
            ->whereHas('receta', function($query) {
                $query->whereNotNull('id_recetas')
                    ->whereHas('producto')
                    ->whereHas('detalles');
            })
            ->get();

        // Separar pedidos por estado
        $pedidosPendientes = $pedidosDetalle->filter(function($pedido) {
            return $pedido->id_estados == null || $pedido->id_estados < 4;
        });

        $pedidosTerminados = $pedidosDetalle->filter(function($pedido) {
            return $pedido->id_estados == 4;
        });

        $pedidosCancelados = $pedidosDetalle->filter(function($pedido) {
            return $pedido->id_estados == 5;
        });

        // Agrupar recetas por estado según el filtro
        $recetasFiltradas = $this->agruparRecetasPorEstado(
            $estadoActual, 
            $pedidosPendientes, 
            $pedidosTerminados, 
            $pedidosCancelados
        );

        $unidadesMedida = UMedida::activos()->get();

        return view('produccion.index-personal', [
            'recetasAgrupadas' => $recetasFiltradas,
            'unidadesMedida' => $unidadesMedida,
            'usuario' => $usuario,
            'equipoActivo' => $equipoActivo,
            'estadoActual' => $estadoActual,
            'totalPendientes' => $pedidosPendientes->count(),
            'totalTerminados' => $pedidosTerminados->count(),
            'totalCancelados' => $pedidosCancelados->count()
        ]);
    }

    /**
     * Vista para administradores que muestra todos los pedidos organizados por áreas
     */
    public function indexAdmin()
    {
        $usuario = Auth::user();
        
        // Verificar que el usuario tenga el rol de Administrador (id_roles === 1)
        if ($usuario->id_roles !== 1) {
            return redirect()->route('produccion.index')
                ->with('error', 'No tienes permisos para acceder a esta vista de administrador.');
        }
        
        $estadoActual = request()->query('estado', 'pendientes');
        $fechaActual = Carbon::now()->toDateString();
        
        // Obtener todas las áreas para el administrador
        $areas = Area::where('status', true)
            ->where('is_deleted', false)
            ->get();
        
        // Obtener todos los pedidos del día sin filtrar por área
        $pedidosDetalle = PedidoDetalle::with([
                'receta', 
                'receta.producto', 
                'receta.detalles', 
                'receta.detalles.producto', 
                'receta.instructivo', 
                'uMedida'
            ])
            ->whereDate('created_at', $fechaActual)
            ->where('is_deleted', false)
            ->whereHas('receta', function($query) {
                $query->whereNotNull('id_recetas')
                    ->whereHas('producto')
                    ->whereHas('detalles');
            })
            ->get();

        // Separar pedidos por estado
        $pedidosPendientes = $pedidosDetalle->filter(function($pedido) {
            return $pedido->id_estados == null || $pedido->id_estados < 4;
        });

        $pedidosTerminados = $pedidosDetalle->filter(function($pedido) {
            return $pedido->id_estados == 4;
        });

        $pedidosCancelados = $pedidosDetalle->filter(function($pedido) {
            return $pedido->id_estados == 5;
        });

        // Agrupar recetas por estado según el filtro
        $recetasFiltradas = $this->agruparRecetasPorEstado(
            $estadoActual, 
            $pedidosPendientes, 
            $pedidosTerminados, 
            $pedidosCancelados
        );

        // Agrupar por áreas para visualización organizada
        $recetasPorArea = [];
        foreach ($areas as $area) {
            $recetasArea = $recetasFiltradas->filter(function($receta, $idReceta) use ($area) {
                return isset($receta['id_areas']) && $receta['id_areas'] == $area->id_areas;
            });
            
            if ($recetasArea->count() > 0) {
                $recetasPorArea[$area->id_areas] = [
                    'area' => $area,
                    'recetas' => $recetasArea
                ];
            }
        }

        $unidadesMedida = UMedida::activos()->get();
        
        // Verificar equipo activo del usuario (aunque no lo necesite para operar)
        $equipoActivo = EquipoCabecera::where('id_usuarios', $usuario->id_usuarios)
            ->where('status', true)
            ->where('is_deleted', false)
            ->whereDate('created_at', $fechaActual)
            ->with(['usuario', 'area', 'turno'])
            ->first();

        return view('produccion.index-admin', [
            'recetasAgrupadas' => $recetasFiltradas,
            'recetasPorArea' => $recetasPorArea,
            'unidadesMedida' => $unidadesMedida,
            'usuario' => $usuario,
            'equipoActivo' => $equipoActivo,
            'estadoActual' => $estadoActual,
            'totalPendientes' => $pedidosPendientes->count(),
            'totalTerminados' => $pedidosTerminados->count(),
            'totalCancelados' => $pedidosCancelados->count(),
            'areas' => $areas
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $producciones = ProduccionCabecera::with(['usuario', 'turno', 'equipo'])
            ->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->paginate(10);

        return view('produccion.index', compact('producciones'));
    }

    /**
     * Agrupa las recetas por estado (pendientes, terminados, cancelados)
     */
    protected function agruparRecetasPorEstado($estadoActual, $pedidosPendientes, $pedidosTerminados, $pedidosCancelados)
    {
        switch ($estadoActual) {
            case 'terminados':
                return $this->agruparPedidosPorReceta($pedidosTerminados, 'terminado');
            case 'cancelados':
                return $this->agruparPedidosPorReceta($pedidosCancelados, 'cancelado');
            default:
                return $this->agruparPedidosPorReceta($pedidosPendientes, null);
        }
    }

    /**
     * Agrupa los pedidos por receta y determina el estado general
     */
    protected function agruparPedidosPorReceta($pedidos, $estadoForzado = null)
    {
        return $pedidos->groupBy('id_recetas')->map(function ($items) use ($estadoForzado) {
            $receta = $items->first()->receta;
            
            $estadoGeneral = $estadoForzado ?: $this->determinarEstadoGeneral($items);
            
            // Obtener componente de harina
            $componenteHarina = $receta->detalles->first(function($item) {
                return $item->producto && stripos($item->producto->nombre, 'harina') !== false;
            });
            
            return [
                'cantidad_total' => $items->sum('cantidad'),
                'es_personalizado' => $items->contains('es_personalizado', true),
                'id_productos_api' => $items->first()->id_productos_api,
                'id_u_medidas' => $items->first()->id_u_medidas,
                'id_areas' => $items->first()->id_areas,
                'receta' => $receta,
                'pedidos' => $items,
                'estado_general' => $estadoGeneral,
                'estados_individuales' => $items->map(function($item) {
                    return $this->determinarEstadoIndividual($item->id_estados);
                }),
                'cantidad_producida_real' => $items->first()->cantidad_producida_real ?? null,
                'observaciones' => $items->first()->observaciones ?? null,
                'costo_diseño' => $items->where('es_personalizado', true)->sum('costo_diseño') ?? 0,
                'componente_harina' => $componenteHarina,
                'estados_pedidos' => $items->pluck('id_estados')
            ];
        })->filter(function ($item) {
            return !is_null($item['receta']) && $item['receta']->producto && $item['receta']->detalles;
        });
    }

    protected function determinarEstadoIndividual($idEstado)
    {
        switch ($idEstado) {
            case 5:
                return 'cancelado';
            case 4:
                return 'terminado';
            case 3:
                return 'en_proceso';
            default:
                return 'pendiente';
        }
    }

    /**
     * Determina el estado general de un grupo de pedidos
     */
    protected function determinarEstadoGeneral($pedidos)
    {
        if ($pedidos->where('id_estados', 5)->count() > 0) {
            return 'cancelado';
        }
        if ($pedidos->where('id_estados', 4)->count() > 0) {
            return 'terminado';
        }
        if ($pedidos->where('id_estados', 3)->count() > 0) {
            return 'en_proceso';
        }
        return 'pendiente';
    }

    /**
     * Guarda la producción realizada por el personal
     */
    public function guardarProduccionPersonal(Request $request)
    {
        DB::beginTransaction();
        try {
            Log::info("Inicio de guardarProduccionPersonal", ['user_id' => Auth::id()]);
            
            $equipo = EquipoCabecera::findOrFail($request->id_equipos);
            $usuario = auth()->user();
            $fechaActual = Carbon::now()->format('Y-m-d');

            // Validación de estados con logs
            $recetasTerminadas = array_filter($request->es_terminado ?? []);
            $recetasCanceladas = array_filter($request->es_cancelado ?? []);
            $personalizadosTerminados = array_filter($request->es_terminado_personalizado ?? []);
            $personalizadosCancelados = array_filter($request->es_cancelado_personalizado ?? []);

            // Validar observaciones para pedidos cancelados
            $this->validarObservacionesCancelacion($request, $recetasCanceladas, $personalizadosCancelados);

            Log::debug("Estados recibidos", [
                'recetas_terminadas' => array_keys($recetasTerminadas),
                'recetas_canceladas' => array_keys($recetasCanceladas),
                'personalizados_terminados' => array_keys($personalizadosTerminados),
                'personalizados_cancelados' => array_keys($personalizadosCancelados)
            ]);

            // Verificar si hay algo para procesar
            $hayQueProcesar = !empty($recetasTerminadas) || !empty($recetasCanceladas) || 
                             !empty($personalizadosTerminados) || !empty($personalizadosCancelados);

            if (!$hayQueProcesar) {
                $errorMsg = "Intento de guardar producción sin estados activos";
                Log::warning($errorMsg);
                throw new \Exception($errorMsg);
            }

            // Buscar o crear cabecera de producción
            $produccionCab = $this->obtenerOCrearCabeceraProduccion($equipo, $usuario, $fechaActual);
            Log::info("Cabecera de producción", ['produccion_cab_id' => $produccionCab->id_produccion_cab]);

            // Procesar recetas normales (no personalizadas)
            $recetasProcesadas = array_unique(array_merge(array_keys($recetasTerminadas), array_keys($recetasCanceladas)));
            Log::info("Procesando recetas normales", ['recetas' => $recetasProcesadas]);
            
            foreach ($recetasProcesadas as $idReceta) {
                Log::info("Procesando receta ID: {$idReceta}");
                $this->procesarRecetaProduccion($idReceta, $request, $produccionCab, $usuario);
            }

            // Procesar pedidos personalizados individualmente
            $personalizadosProcesados = array_unique(array_merge(array_keys($personalizadosTerminados), array_keys($personalizadosCancelados)));
            Log::info("Procesando pedidos personalizados", ['pedidos' => $personalizadosProcesados]);
            
            foreach ($personalizadosProcesados as $idPedido) {
                Log::info("Procesando pedido personalizado ID: {$idPedido}");
                $pedido = PedidoDetalle::find($idPedido);
                if ($pedido) {
                    $this->procesarPedidoPersonalizado($request, $produccionCab, $pedido->receta, $pedido);
                } else {
                    Log::error("Pedido personalizado no encontrado", ['pedido_id' => $idPedido]);
                }
            }

            DB::commit();
            Log::info("Producción guardada exitosamente");
            
            return redirect()->route('produccion.index-personal', ['estado' => 'pendientes'])
                ->with('success', "Producción registrada correctamente");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al guardar producción: " . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all()
            ]);
            
            return back()->with('error', 'Error al registrar la producción: ' . $e->getMessage());
        }
    }

    /**
     * Guarda la producción registrada por el administrador
     */
    public function guardarProduccionAdmin(Request $request)
    {
        DB::beginTransaction();
        try {
            Log::info("Inicio de guardarProduccionAdmin", ['user_id' => Auth::id()]);
            
            $usuario = auth()->user();
            
            // Verificar que sea un administrador
            if ($usuario->id_roles !== 1) {
                throw new \Exception("No tienes permisos para realizar esta acción. Se requiere rol de administrador.");
            }
            
            $equipo = EquipoCabecera::findOrFail($request->id_equipos);
            $fechaActual = Carbon::now()->format('Y-m-d');

            // Validación de estados con logs
            $recetasTerminadas = array_filter($request->es_terminado ?? []);
            $recetasCanceladas = array_filter($request->es_cancelado ?? []);
            $personalizadosTerminados = array_filter($request->es_terminado_personalizado ?? []);
            $personalizadosCancelados = array_filter($request->es_cancelado_personalizado ?? []);

            // Validar observaciones para pedidos cancelados
            $this->validarObservacionesCancelacion($request, $recetasCanceladas, $personalizadosCancelados);

            Log::debug("Estados recibidos por administrador", [
                'recetas_terminadas' => array_keys($recetasTerminadas),
                'recetas_canceladas' => array_keys($recetasCanceladas),
                'personalizados_terminados' => array_keys($personalizadosTerminados),
                'personalizados_cancelados' => array_keys($personalizadosCancelados)
            ]);

            // Verificar si hay algo para procesar
            $hayQueProcesar = !empty($recetasTerminadas) || !empty($recetasCanceladas) || 
                             !empty($personalizadosTerminados) || !empty($personalizadosCancelados);

            if (!$hayQueProcesar) {
                $errorMsg = "Intento de guardar producción sin estados activos";
                Log::warning($errorMsg);
                throw new \Exception($errorMsg);
            }

            // Buscar o crear cabecera de producción
            $produccionCab = $this->obtenerOCrearCabeceraProduccion($equipo, $usuario, $fechaActual);
            Log::info("Cabecera de producción (Admin)", ['produccion_cab_id' => $produccionCab->id_produccion_cab]);

            // Procesar recetas normales (no personalizadas)
            $recetasProcesadas = array_unique(array_merge(array_keys($recetasTerminadas), array_keys($recetasCanceladas)));
            Log::info("Procesando recetas normales (Admin)", ['recetas' => $recetasProcesadas]);
            
            foreach ($recetasProcesadas as $idReceta) {
                Log::info("Admin procesando receta ID: {$idReceta}");
                $this->procesarRecetaProduccionAdmin($idReceta, $request, $produccionCab, $usuario);
            }

            // Procesar pedidos personalizados individualmente
            $personalizadosProcesados = array_unique(array_merge(array_keys($personalizadosTerminados), array_keys($personalizadosCancelados)));
            Log::info("Procesando pedidos personalizados (Admin)", ['pedidos' => $personalizadosProcesados]);
            
            foreach ($personalizadosProcesados as $idPedido) {
                Log::info("Admin procesando pedido personalizado ID: {$idPedido}");
                $pedido = PedidoDetalle::find($idPedido);
                if ($pedido) {
                    $this->procesarPedidoPersonalizadoAdmin($request, $produccionCab, $pedido->receta, $pedido);
                } else {
                    Log::error("Pedido personalizado no encontrado", ['pedido_id' => $idPedido]);
                }
            }

            DB::commit();
            Log::info("Producción guardada exitosamente por administrador");
            
            return redirect()->route('produccion.index-admin', ['estado' => 'pendientes'])
                ->with('success', "Producción registrada correctamente por administrador");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al guardar producción (Admin): " . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all()
            ]);
            
            return back()->with('error', 'Error al registrar la producción: ' . $e->getMessage());
        }
    }

    /**
     * Valida que existan observaciones para los pedidos cancelados
     */
    protected function validarObservacionesCancelacion($request, $recetasCanceladas, $personalizadosCancelados)
    {
        $errores = [];

        // Validar observaciones para recetas normales canceladas
        foreach ($recetasCanceladas as $idReceta => $valor) {
            $observacion = $request->observaciones[$idReceta] ?? null;
            if (empty($observacion)) {
                $errores[] = "Debe ingresar una observación para la receta #{$idReceta} que está siendo cancelada";
            }
        }

        // Validar observaciones para pedidos personalizados cancelados
        foreach ($personalizadosCancelados as $idPedido => $valor) {
            $observacion = $request->observaciones_personalizado[$idPedido] ?? null;
            if (empty($observacion)) {
                $errores[] = "Debe ingresar una observación para el pedido personalizado #{$idPedido} que está siendo cancelado";
            }
        }

        if (!empty($errores)) {
            throw new \Exception(implode("\n", $errores));
        }
    }

    /**
     * Obtiene o crea la cabecera de producción
     */
    protected function obtenerOCrearCabeceraProduccion($equipo, $usuario, $fechaActual)
    {
        $produccionCab = ProduccionCabecera::where('id_equipos', $equipo->id_equipos_cab)
            ->where('id_usuario', $usuario->id_usuarios)
            ->where('id_turnos', $equipo->id_turnos)
            ->whereDate('fecha', $fechaActual)
            ->first();

        if (!$produccionCab) {
            $produccionCab = ProduccionCabecera::create([
                'id_equipos' => $equipo->id_equipos_cab,
                'id_turnos' => $equipo->id_turnos,
                'id_usuario' => $usuario->id_usuarios,
                'fecha' => $fechaActual,
                'hora' => Carbon::now()->format('H:i:s'),
                'doc_interno' => 'PROD-' . Carbon::now()->format('YmdHis')
            ]);
        }

        return $produccionCab;
    }

    /**
     * Procesa una receta completa (solo pedidos no personalizados)
     */
    protected function procesarRecetaProduccion($idReceta, $request, $produccionCab, $usuario)
    {
        $receta = RecetaCabecera::with(['detalles.producto'])->findOrFail($idReceta);
        
        // Obtener solo pedidos no personalizados
        $pedidos = PedidoDetalle::where('id_recetas', $idReceta)
            ->whereDate('created_at', Carbon::today())
            ->where('id_areas', $usuario->id_areas)
            ->where('es_personalizado', false) // Solo pedidos no personalizados
            ->get();

        if ($pedidos->count() > 0) {
            $this->procesarPedidosNormales($request, $produccionCab, $receta, $pedidos, $idReceta);
        }
    }

    /**
     * Procesa una receta completa para el administrador (sin restricción de área)
     */
    protected function procesarRecetaProduccionAdmin($idReceta, $request, $produccionCab, $usuario)
    {
        $receta = RecetaCabecera::with(['detalles.producto'])->findOrFail($idReceta);
        
        // Obtener solo pedidos no personalizados SIN filtrar por área
        $pedidos = PedidoDetalle::where('id_recetas', $idReceta)
            ->whereDate('created_at', Carbon::today())
            ->where('es_personalizado', false) // Solo pedidos no personalizados
            ->get();

        Log::debug("Admin procesando receta para todos los pedidos", [
            'receta_id' => $idReceta,
            'cantidad_pedidos' => $pedidos->count()
        ]);

        if ($pedidos->count() > 0) {
            $this->procesarPedidosNormales($request, $produccionCab, $receta, $pedidos, $idReceta);
        }
    }

    /**
     * Procesa un pedido personalizado para el administrador (sin restricción de área)
     */
    protected function procesarPedidoPersonalizadoAdmin($request, $produccionCab, $receta, $pedido)
    {
        // Validar que el pedido tenga los datos necesarios
        if (!$pedido || !$receta) {
            Log::error("Error al procesar pedido personalizado (Admin): Datos incompletos", [
                'pedido_id' => $pedido->id_pedidos_det ?? null,
                'receta_id' => $receta->id_recetas ?? null
            ]);
            return null;
        }

        $esIniciado = (bool)($request->es_iniciado_personalizado[$pedido->id_pedidos_det] ?? false);
        $esTerminado = (bool)($request->es_terminado_personalizado[$pedido->id_pedidos_det] ?? false);
        $esCancelado = (bool)($request->es_cancelado_personalizado[$pedido->id_pedidos_det] ?? false);

        // Solo procesar si tiene algún estado activo
        if (!$esIniciado && !$esTerminado && !$esCancelado) {
            Log::info("Pedido personalizado {$pedido->id_pedidos_det} no procesado - Sin estados activos");
            return null;
        }

        // Validar que solo un estado esté activo
        if ($esTerminado && $esCancelado) {
            throw new \Exception("No se puede terminar y cancelar el mismo pedido");
        }

        $cantidadPedido = $pedido->cantidad;
        $cantidadEsperada = ($receta->id_areas == 1)
            ? $cantidadPedido * $receta->constante_peso_lata
            : $cantidadPedido;
        
        // Obtener cantidad producida real para este pedido
        $cantidadProducida = (float)($request->cantidad_producida_real_personalizado[$pedido->id_pedidos_det] ?? $cantidadPedido);
        
        // Obtener costo diseño para este pedido específico
        $costoDiseno = (float)($request->costo_diseño[$pedido->id_pedidos_det] ?? 0);

        // --- LÓGICA PARA CANCELADOS ---
        if ($esCancelado && !$esIniciado) {
            $cantidadEsperada = 0;
            $cantidadProducida = 0;
            $costoDiseno = 0;
        }
        // --------------------------------

        // Validar costo diseño si se está terminando
        if ($esTerminado && $costoDiseno <= 0) {
            throw new \Exception("Debe ingresar un costo de diseño válido para el pedido personalizado #{$pedido->id_pedidos_det}");
        }

        // Obtener ID del componente de harina
        $idHarina = $request->id_recetas_det_harina_personalizado[$pedido->id_pedidos_det] ?? null;

        // Calcular subtotal y total
        $subtotalReceta = ($esCancelado && !$esIniciado) ? 0 : $this->calcularSubtotal($receta, $cantidadEsperada);
        $totalReceta = ($esCancelado && !$esIniciado) ? 0 : ($this->calcularSubtotal($receta, $cantidadEsperada) + $costoDiseno);
        $cantHarina = ($esCancelado && !$esIniciado) ? 0 : $this->calcularHarina($receta, $cantidadEsperada);

        // Crear detalle de producción individual para este pedido personalizado
        $detalle = ProduccionDetalle::create([
            'id_produccion_cab' => $produccionCab->id_produccion_cab,
            'id_productos_api' => $receta->id_productos_api,
            'id_u_medidas' => $pedido->id_u_medidas,
            'id_u_medidas_prodcc' => $pedido->id_u_medidas,
            'id_recetas_cab' => $receta->id_recetas,
            'id_areas' => $receta->id_areas,
            'cantidad_pedido' => $cantidadPedido,
            'cantidad_esperada' => $cantidadEsperada,
            'cantidad_producida_real' => $cantidadProducida,
            'es_iniciado' => $esIniciado,
            'es_terminado' => $esTerminado,
            'es_cancelado' => $esCancelado,
            'costo_diseño' => $costoDiseno,
            'subtotal_receta' => $subtotalReceta,
            'total_receta' => $totalReceta,
            'cant_harina' => $cantHarina,
            'id_recetas_det_harina' => $idHarina,
            'observaciones' => $request->observaciones_personalizado[$pedido->id_pedidos_det] ?? null,
            'pedidos_ids' => [$pedido->id_pedidos_det]
        ]);

        // Actualizar estado de este pedido individual
        $this->actualizarEstadosPedidos(collect([$pedido]), $esIniciado, $esTerminado, $esCancelado);

        Log::info("Registrado detalle de producción para pedido personalizado (Admin)", [
            'produccion_det_id' => $detalle->id_produccion_det,
            'pedido_id' => $pedido->id_pedidos_det
        ]);

        return $detalle;
    }
    protected function procesarPedidosNormales($request, $produccionCab, $receta, $pedidos, $idReceta)
    {
        Log::info("Iniciando procesamiento de pedidos normales para receta ID: {$idReceta}");
        
        // Determinar estados
        $esIniciado = (bool)($request->es_iniciado[$idReceta] ?? false);
        $esTerminado = (bool)($request->es_terminado[$idReceta] ?? false);
        $esCancelado = (bool)($request->es_cancelado[$idReceta] ?? false);

        Log::debug("Estados para receta {$idReceta}:", [
            'iniciado' => $esIniciado,
            'terminado' => $esTerminado,
            'cancelado' => $esCancelado
        ]);

        // Validar estados
        if ($esTerminado && !$esIniciado) {
            $errorMsg = "No se puede terminar la receta {$idReceta} sin iniciarla primero";
            Log::error($errorMsg);
            throw new \Exception($errorMsg);
        }

        // Obtener ID del componente de harina
        $idHarina = $request->id_recetas_det_harina[$idReceta] ?? null;
        Log::debug("Componente harina para receta {$idReceta}:", ['id_harina' => $idHarina]);

        // Solo crear registro si está iniciado, terminado o cancelado
        if ($esIniciado || $esTerminado || $esCancelado) {
            // Obtener solo los pedidos que están siendo actualizados
            $pedidosAActualizar = $pedidos->filter(function($pedido) {
                return !in_array($pedido->id_estados, [4, 5]); // Solo pedidos no terminados ni cancelados
            });

            if ($pedidosAActualizar->isEmpty()) {
                Log::info("No hay pedidos nuevos para procesar en la receta {$idReceta}");
                return null;
            }

            Log::info("Procesando pedidos nuevos", [
                'total_pedidos' => $pedidosAActualizar->count(),
                'pedidos_ids' => $pedidosAActualizar->pluck('id_pedidos_det')
            ]);

            // Sumar cantidades de todos los pedidos
            $cantidadPedidoTotal = $pedidosAActualizar->sum('cantidad');
            $cantidadEsperadaTotal = ($receta->id_areas == 1)
                ? $cantidadPedidoTotal * $receta->constante_peso_lata
                : $cantidadPedidoTotal;

            // Obtener la cantidad producida real del campo oculto o del input
            $cantidadProducida = null;
            if ($request->has("cantidad_producida_real_hidden.{$idReceta}")) {
                $cantidadProducida = (float)$request->input("cantidad_producida_real_hidden.{$idReceta}");
                Log::debug("Cantidad producida obtenida del campo oculto", [
                    'receta_id' => $idReceta,
                    'cantidad' => $cantidadProducida
                ]);
            } else if ($request->has("cantidad_producida_real.{$idReceta}")) {
                $cantidadProducida = (float)$request->input("cantidad_producida_real.{$idReceta}");
                Log::debug("Cantidad producida obtenida del input", [
                    'receta_id' => $idReceta,
                    'cantidad' => $cantidadProducida
                ]);
            }
            if (!$cantidadProducida) {
                $cantidadProducida = $cantidadEsperadaTotal;
                Log::debug("Usando cantidad esperada total como fallback", [
                    'receta_id' => $idReceta,
                    'cantidad' => $cantidadProducida
                ]);
            }

            // Calcular subtotal y total
            $subtotalReceta = $this->calcularSubtotal($receta, $cantidadEsperadaTotal);
            $totalReceta = $subtotalReceta; // No hay costo diseño en no personalizados
            $cantHarina = $this->calcularHarina($receta, $cantidadEsperadaTotal);

            // --- LÓGICA PARA CANCELADOS ---
            if ($esCancelado && !$esIniciado) {
                $cantidadEsperadaTotal = 0;
                $cantidadProducida = 0;
                $subtotalReceta = 0;
                $totalReceta = 0;
                $cantHarina = 0;
            }
            // Si esCancelado y esIniciado pero no esTerminado, se guardan los valores reales (ya calculados)
            // Si esCancelado, esIniciado y esTerminado, se guardan los valores tal como están (ya calculados)
            // --------------------------------

            // Obtener todos los IDs de pedidos para este grupo
            $pedidosIds = $pedidosAActualizar->pluck('id_pedidos_det')->toArray();

            // Observaciones: obtener la observación directamente del request usando el idReceta
            $observacion = $request->observaciones[$idReceta] ?? null;

            // Crear detalle de producción
            $detalle = ProduccionDetalle::create([
                'id_produccion_cab' => $produccionCab->id_produccion_cab,
                'id_productos_api' => $receta->id_productos_api,
                'id_u_medidas' => $pedidosAActualizar->first()->id_u_medidas,
                'id_u_medidas_prodcc' => $request->id_u_medidas_prodcc[$idReceta] ?? $pedidosAActualizar->first()->id_u_medidas,
                'id_recetas_cab' => $receta->id_recetas,
                'id_areas' => $receta->id_areas,
                'cantidad_pedido' => $cantidadPedidoTotal,
                'cantidad_esperada' => $cantidadEsperadaTotal,
                'cantidad_producida_real' => $cantidadProducida,
                'es_iniciado' => $esIniciado,
                'es_terminado' => $esTerminado,
                'es_cancelado' => $esCancelado,
                'costo_diseño' => 0,
                'subtotal_receta' => $subtotalReceta,
                'total_receta' => $totalReceta,
                'cant_harina' => $cantHarina,
                'id_recetas_det_harina' => $idHarina,
                'observaciones' => $observacion,
                'pedidos_ids' => $pedidosIds
            ]);

            // Actualizar estado de todos los pedidos agrupados
            $this->actualizarEstadosPedidos($pedidosAActualizar, $esIniciado, $esTerminado, $esCancelado);

            return true;
        }
        
        Log::warning("Receta {$idReceta} no fue procesada - No tenía estados activos");
        return null;
    }

    protected function determinarEstadoAAplicar($esIniciado, $esTerminado, $esCancelado)
    {
        if ($esCancelado) return 'cancelado';
        if ($esTerminado) return 'terminado';
        if ($esIniciado) return 'en_proceso';
        return 'pendiente';
    }

    /**
     * Actualiza el estado de los pedidos según la producción con logs detallados
     */
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
            $actualizados = 0;
            $noActualizados = 0;
            
            foreach ($pedidosDetalle as $pedidoDet) {
                // Solo actualizar pedidos que no tienen estado final
                if (!in_array($pedidoDet->id_estados, [4, 5])) {
                    $pedidoDet->update(['id_estados' => $estado]);
                    $actualizados++;
                    Log::debug("Actualizado estado del pedido", [
                        'pedido_id' => $pedidoDet->id_pedidos_det,
                        'nuevo_estado' => $estado,
                        'estado_anterior' => $pedidoDet->id_estados
                    ]);
                } else {
                    $noActualizados++;
                    Log::debug("Pedido no actualizado - Ya tenía estado final", [
                        'pedido_id' => $pedidoDet->id_pedidos_det,
                        'estado_actual' => $pedidoDet->id_estados,
                        'estado_intentado' => $estado
                    ]);
                }
            }
            
            Log::info("Resumen actualización estados pedidos", [
                'total_pedidos' => $pedidosDetalle->count(),
                'actualizados' => $actualizados,
                'no_actualizados' => $noActualizados,
                'estado_aplicado' => $estado
            ]);
        } else {
            Log::warning("No se actualizaron pedidos - Estado no determinado", [
                'iniciado' => $esIniciado,
                'terminado' => $esTerminado,
                'cancelado' => $esCancelado
            ]);
        }
    }

    /**
     * Procesa un pedido personalizado individual
     */
    protected function procesarPedidoPersonalizado($request, $produccionCab, $receta, $pedido)
    {
        $esIniciado = (bool)($request->es_iniciado_personalizado[$pedido->id_pedidos_det] ?? false);
        $esTerminado = (bool)($request->es_terminado_personalizado[$pedido->id_pedidos_det] ?? false);
        $esCancelado = (bool)($request->es_cancelado_personalizado[$pedido->id_pedidos_det] ?? false);

        // Solo procesar si tiene algún estado activo
        if (!$esIniciado && !$esTerminado && !$esCancelado) {
            Log::info("Pedido personalizado {$pedido->id_pedidos_det} no procesado - Sin estados activos");
            return null;
        }

        // Validar que solo un estado esté activo
        if ($esTerminado && $esCancelado) {
            throw new \Exception("No se puede terminar y cancelar el mismo pedido");
        }

        $cantidadPedido = $pedido->cantidad;
        $cantidadEsperada = ($receta->id_areas == 1)
            ? $cantidadPedido * $receta->constante_peso_lata
            : $cantidadPedido;
        
        // Obtener cantidad producida real para este pedido
        $cantidadProducida = (float)($request->cantidad_producida_real_personalizado[$pedido->id_pedidos_det] ?? $cantidadPedido);
        
        // Obtener costo diseño para este pedido específico
        $costoDiseno = (float)($request->costo_diseño[$pedido->id_pedidos_det] ?? 0);

        // --- LÓGICA PARA CANCELADOS ---
        if ($esCancelado && !$esIniciado) {
            $cantidadEsperada = 0;
            $cantidadProducida = 0;
            $costoDiseno = 0;
        }
        // Si esCancelado y esIniciado pero no esTerminado, se guardan los valores reales (ya calculados)
        // Si esCancelado, esIniciado y esTerminado, se guardan los valores tal como están (ya calculados)
        // --------------------------------

        // Validar costo diseño si se está terminando
        if ($esTerminado && $costoDiseno <= 0) {
            throw new \Exception("Debe ingresar un costo de diseño válido para el pedido personalizado #{$pedido->id_pedidos_det}");
        }

        // Obtener ID del componente de harina
        $idHarina = $request->id_recetas_det_harina_personalizado[$pedido->id_pedidos_det] ?? null;

        // Calcular subtotal y total
        $subtotalReceta = ($esCancelado && !$esIniciado) ? 0 : $this->calcularSubtotal($receta, $cantidadEsperada);
        $totalReceta = ($esCancelado && !$esIniciado) ? 0 : ($this->calcularSubtotal($receta, $cantidadEsperada) + $costoDiseno);
        $cantHarina = ($esCancelado && !$esIniciado) ? 0 : $this->calcularHarina($receta, $cantidadEsperada);

        // Crear detalle de producción individual para este pedido personalizado
        $detalle = ProduccionDetalle::create([
            'id_produccion_cab' => $produccionCab->id_produccion_cab,
            'id_productos_api' => $receta->id_productos_api,
            'id_u_medidas' => $pedido->id_u_medidas,
            'id_u_medidas_prodcc' => $pedido->id_u_medidas,
            'id_recetas_cab' => $receta->id_recetas,
            'id_pedidos_det' => $pedido->id_pedidos_det,
            'id_areas' => $receta->id_areas,
            'cantidad_pedido' => $cantidadPedido,
            'cantidad_esperada' => $cantidadEsperada,
            'cantidad_producida_real' => $cantidadProducida,
            'es_iniciado' => $esIniciado,
            'es_terminado' => $esTerminado,
            'es_cancelado' => $esCancelado,
            'costo_diseño' => $costoDiseno,
            'subtotal_receta' => $subtotalReceta,
            'total_receta' => $totalReceta,
            'cant_harina' => $cantHarina,
            'id_recetas_det_harina' => $idHarina,
            'observaciones' => $request->observaciones_personalizado[$pedido->id_pedidos_det] ?? null,
            'pedidos_ids' => [$pedido->id_pedidos_det]
        ]);

        // Actualizar estado de este pedido individual
        $this->actualizarEstadosPedidos(collect([$pedido]), $esIniciado, $esTerminado, $esCancelado);

        return $detalle;
    }

    /**
     * Calcula el subtotal de una receta para una cantidad dada
     */
    protected function calcularSubtotal($receta, $cantidadPedido)
    {
        $subtotal = 0;
        foreach ($receta->detalles as $detalle) {
            $subtotal += $detalle->subtotal_receta * $cantidadPedido;
        }
        return $subtotal;
    }

    /**
     * Calcula la cantidad de harina necesaria para una receta
     */
    protected function calcularHarina($receta, $cantidadPedido)
    {
        $componenteHarina = $receta->detalles->first(function ($item) {
            return $item->producto && stripos($item->producto->nombre, 'harina') !== false;
        });
        return $componenteHarina ? $componenteHarina->cantidad * $cantidadPedido : 0;
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
     * Exportar producción a Excel
     */
    public function exportarExcel(Request $request)
    {
        $fechaInicio = Carbon::parse($request->input('fecha_inicio', Carbon::now()->startOfDay()));
        $fechaFin = Carbon::parse($request->input('fecha_fin', Carbon::now()->endOfDay()));
        $estado = $request->input('estado', 'todos');

        // Consulta base
        $query = ProduccionDetalle::with(['recetaCabecera.producto', 'recetaCabecera.instructivo', 'area', 'produccionCabecera.usuario'])
            ->join('produccion_cab', 'produccion_det.id_produccion_cab', '=', 'produccion_cab.id_produccion_cab')
            ->whereBetween('produccion_cab.fecha', [$fechaInicio, $fechaFin]);

        // Aplicar filtro de estado
        if ($estado !== 'todos') {
            switch ($estado) {
                case 'pendientes':
                    $query->where('es_terminado', false)->where('es_cancelado', false);
                    break;
                case 'terminados':
                    $query->where('es_terminado', true);
                    break;
                case 'cancelados':
                    $query->where('es_cancelado', true);
                    break;
            }
        }

        $producciones = $query->orderBy('produccion_cab.fecha', 'desc')
            ->orderBy('produccion_cab.hora', 'desc')
            ->get();

        // Crear el archivo Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Establecer el título de la hoja
        $sheet->setTitle('Producciones');

        // Establecer encabezados
        $headers = [
            'A1' => 'Fecha',
            'B1' => 'Hora',
            'C1' => 'Producto',
            'D1' => 'Receta',
            'E1' => 'Área',
            'F1' => 'Usuario Responsable',
            'G1' => 'Cant. Pedido',
            'H1' => 'Cant. Producida',
            'I1' => 'Estado',
            'J1' => 'Subtotal',
            'K1' => 'Costo Diseño',
            'L1' => 'Total'
        ];

        // Aplicar estilos a los encabezados
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('CCCCCC');
        }

        // Llenar datos
        $row = 2;
        foreach ($producciones as $produccion) {
            $sheet->setCellValue('A' . $row, Carbon::parse($produccion->fecha)->format('d/m/Y'));
            $sheet->setCellValue('B' . $row, $produccion->hora);
            $sheet->setCellValue('C' . $row, $produccion->recetaCabecera->producto->nombre ?? 'N/A');
            $sheet->setCellValue('D' . $row, $produccion->recetaCabecera->nombre ?? 'N/A');
            $sheet->setCellValue('E' . $row, $produccion->area->nombre ?? 'N/A');
            $sheet->setCellValue('F' . $row, $produccion->produccionCabecera->usuario->nombre_personal ?? 'N/A');
            $sheet->setCellValue('G' . $row, number_format($produccion->cantidad_pedido, 2));
            $sheet->setCellValue('H' . $row, number_format($produccion->cantidad_producida_real, 2));
            $sheet->setCellValue('I' . $row, $produccion->es_terminado ? 'Terminado' : ($produccion->es_cancelado ? 'Cancelado' : 'Pendiente'));
            $sheet->setCellValue('J' . $row, number_format($produccion->subtotal_receta, 2));
            $sheet->setCellValue('K' . $row, number_format($produccion->costo_diseño, 2));
            $sheet->setCellValue('L' . $row, number_format($produccion->total_receta, 2));
            $row++;
        }

        // Autoajustar el ancho de las columnas
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Crear el archivo
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'produccion_' . Carbon::now()->format('YmdHis') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    /**
     * Exportar producción a PDF
     */
    public function exportarPdf(Request $request)
    {
        $fechaInicio = Carbon::parse($request->input('fecha_inicio', Carbon::now()->startOfDay()));
        $fechaFin = Carbon::parse($request->input('fecha_fin', Carbon::now()->endOfDay()));
        $estado = $request->input('estado', 'todos');

        // Consulta base
        $query = ProduccionDetalle::with(['recetaCabecera.producto', 'recetaCabecera.instructivo'])
            ->join('produccion_cab', 'produccion_det.id_produccion_cab', '=', 'produccion_cab.id_produccion_cab')
            ->whereBetween('produccion_cab.fecha', [$fechaInicio, $fechaFin]);

        // Aplicar filtro de estado
        if ($estado !== 'todos') {
            switch ($estado) {
                case 'pendientes':
                    $query->where('es_terminado', false)->where('es_cancelado', false);
                    break;
                case 'terminados':
                    $query->where('es_terminado', true);
                    break;
                case 'cancelados':
                    $query->where('es_cancelado', true);
                    break;
            }
        }

        $producciones = $query->orderBy('produccion_cab.fecha', 'desc')
            ->orderBy('produccion_cab.hora', 'desc')
            ->get();

        // Configurar DOMPDF para evitar el uso de fuentes externas
        $pdf = PDF::loadView('produccion.pdf-periodos', compact('producciones', 'fechaInicio', 'fechaFin', 'estado'))
            ->setPaper('A4', 'landscape')
            ->setOptions([
                'defaultFont' => 'Helvetica',
                'fontCache' => storage_path('fonts'),
                'tempDir' => storage_path('fonts'),
                'chroot' => base_path(),
                'isFontSubsettingEnabled' => false
            ]);

        return $pdf->download('produccion_' . Carbon::now()->format('YmdHis') . '.pdf');
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

    /**
     * Muestra la vista de producción por períodos
     */
    public function indexPorPeriodos(Request $request)
    {
        $fechaInicio = Carbon::parse($request->input('fecha_inicio', Carbon::now()->startOfDay()));
        $fechaFin = Carbon::parse($request->input('fecha_fin', Carbon::now()->endOfDay()));
        $estado = $request->input('estado', 'todos');

        // Consulta base
        $query = ProduccionDetalle::with([
                'recetaCabecera.producto', 
                'recetaCabecera.instructivo', 
                'recetaCabecera.detalles.producto',
                'area', 
                'produccionCabecera.usuario'
            ])
            ->join('produccion_cab', 'produccion_det.id_produccion_cab', '=', 'produccion_cab.id_produccion_cab')
            ->select('produccion_det.*', 'produccion_cab.fecha', 'produccion_cab.hora', 'produccion_det.updated_at', 'produccion_det.cantidad_esperada')
            ->whereBetween('produccion_cab.fecha', [$fechaInicio, $fechaFin]);

        // Aplicar filtro de estado
        if ($estado !== 'todos') {
            switch ($estado) {
                case 'pendientes':
                    $query->where('es_terminado', false)->where('es_cancelado', false);
                    break;
                case 'terminados':
                    $query->where('es_terminado', true);
                    break;
                case 'cancelados':
                    $query->where('es_cancelado', true);
                    break;
            }
        }

        // Obtener totales
        $totalProducciones = $query->count();
        $totalTerminadas = (clone $query)->where('es_terminado', true)->count();
        $totalPendientes = (clone $query)->where('es_terminado', false)->where('es_cancelado', false)->count();
        $totalCanceladas = (clone $query)->where('es_cancelado', true)->count();

        // Obtener producciones sin paginación
        $producciones = $query->orderBy('produccion_cab.fecha', 'desc')
            ->orderBy('produccion_cab.hora', 'desc')
            ->get();

        return view('produccion.index-periodos', compact(
            'producciones',
            'fechaInicio',
            'fechaFin',
            'estado',
            'totalProducciones',
            'totalTerminadas',
            'totalPendientes',
            'totalCanceladas'
        ));
    }

    /**
     * Obtiene las fechas según el período seleccionado
     */
    protected function obtenerFechasPorPeriodo($periodo)
    {
        $hoy = Carbon::now();
        
        switch ($periodo) {
            case 'ayer':
                $inicio = $hoy->copy()->subDay()->startOfDay();
                $fin = $hoy->copy()->subDay()->endOfDay();
                break;
            case 'semana':
                $inicio = $hoy->copy()->subWeek()->startOfDay();
                $fin = $hoy->copy()->endOfDay();
                break;
            default: // hoy
                $inicio = $hoy->copy()->startOfDay();
                $fin = $hoy->copy()->endOfDay();
                break;
        }

        return [
            'inicio' => $inicio,
            'fin' => $fin
        ];
    }

    public function obtenerObservacion(Request $request)
    {
        try {
            $idPedido = (int) $request->id_pedido;
            Log::info('Buscando observación para pedido', ['idPedido' => $idPedido]);

            $produccionDet = \App\Models\ProduccionDetalle::whereRaw('pedidos_ids::jsonb @> ?', [json_encode([$idPedido])])
                ->where('es_cancelado', true)
                ->first();

            Log::info('Resultado consulta produccion_det', ['produccionDet' => $produccionDet]);

            if ($produccionDet && $produccionDet->observaciones) {
                return response()->json([
                    'success' => true,
                    'observacion' => $produccionDet->observaciones
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No se encontró observación para este pedido'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener observación: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la observación: ' . $e->getMessage()
            ], 500);
        }
    }

    public function obtenerDetallesPedidos(Request $request)
    {
        try {
            $pedidosIds = $request->pedidos_ids;
            
            $pedidos = PedidoDetalle::with(['pedidoCabecera.usuario', 'pedidoCabecera.tienda'])
                ->whereIn('id_pedidos_det', $pedidosIds)
                ->get();

            return response()->json($pedidos);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener los detalles de los pedidos: ' . $e->getMessage()
            ], 500);
        }
    }
}

