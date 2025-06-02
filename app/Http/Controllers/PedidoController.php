<?php

namespace App\Http\Controllers;

use App\Models\PedidoCabecera;
use App\Models\PedidoDetalle;
use App\Models\RecetaCabecera;
use App\Models\Area;
use App\Models\UMedida;
use App\Models\HoraLimite;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class PedidoController extends Controller
{
    public function index(Request $request)
    {
        $usuario = Auth::user();

        // Obtener la hora límite activa actual
        $horaLimiteActual = $this->getHoraLimiteActiva();

        // Calcular si estamos dentro del tiempo permitido para pedidos
        $horaActual = now();
        $horaInicioPedidos = $horaLimiteActual ? Carbon::parse($horaLimiteActual->hora_limite)->subHour() : null;
        $horaFinPedidos = $horaLimiteActual ? Carbon::parse($horaLimiteActual->hora_limite) : null;

        $dentroDeHoraPermitida = $horaLimiteActual ?
            ($horaActual->gte($horaInicioPedidos) && $horaActual->lte($horaFinPedidos)) :
            false;

        $query = PedidoCabecera::with([
            'usuario',
            'tienda',
            'pedidosDetalle' => function ($query) {
                $query->where('is_deleted', false);
            },
            'pedidosDetalle.area',
            'pedidosDetalle.estado',
            'pedidosDetalle.uMedida',
            'horaLimite'
        ])
            ->where('is_deleted', false)
            ->whereNull('deleted_at');

        // Filtro por rol de usuario
        if ($usuario->id_roles == 4) { // Rol operador
            $query->where('id_tiendas_api', $usuario->id_tiendas_api);
        } elseif ($usuario->id_roles != 1 && $usuario->id_roles != 2) { // Si no es admin ni rol 2 ni operador
            $query->where('id_usuarios', $usuario->id_usuarios);
        }
        // Los usuarios con id_roles 1 (admin) y 2 pueden ver todos los pedidos

        // Ordenamiento
        $query->orderBy('fecha_last_update', 'desc')
            ->orderBy('hora_last_update', 'desc');

        // Determinar el filtro a aplicar
        $filter = $request->filter ?? 'today';
        $today = Carbon::today();

        switch ($filter) {
            case 'today':
                $query->whereDate('fecha_last_update', $today);
                break;
            case 'yesterday':
                $query->whereDate('fecha_last_update', $today->copy()->subDay());
                break;
            case 'week':
                // Si se proporciona un rango de fechas personalizado
                if ($request->has('start_date') && $request->has('end_date')) {
                    $startDate = Carbon::parse($request->start_date)->startOfDay();
                    $endDate = Carbon::parse($request->end_date)->endOfDay();
                    $query->whereBetween('fecha_last_update', [$startDate, $endDate]);
                } else {
                    // Comportamiento predeterminado: última semana
                    $query->whereBetween('fecha_last_update', [$today->copy()->subWeek(), $today]);
                }
                break;
            case 'custom':
                if ($request->has('custom_date')) {
                    $query->whereDate('fecha_last_update', $request->custom_date);
                }
                break;
        }

        $pedidos = $query->paginate(10);

        // Pasar más información a la vista para el control de botones
        return view('pedidos.index', compact(
            'pedidos',
            'dentroDeHoraPermitida',
            'horaLimiteActual',
            'horaInicioPedidos',
            'horaFinPedidos',
            'filter',
            'usuario'
        ));
    }

    /**
     * Obtiene la hora límite activa actual basada en la hora del sistema
     */
    protected function getHoraLimiteActiva()
    {
        $horaActual = now();

        // Obtener todas las horas límite activas ordenadas
        $horasLimite = HoraLimite::where('status', true)
            ->where('is_deleted', false)
            ->orderBy('hora_limite', 'asc')
            ->get();

        foreach ($horasLimite as $horaLimite) {
            $horaLimiteCarbon = Carbon::parse($horaLimite->hora_limite);
            $horaInicio = $horaLimiteCarbon->copy()->subHour();

            // Si la hora actual está entre inicio (1 hora antes) y la hora límite
            if ($horaActual->between($horaInicio, $horaLimiteCarbon)) {
                return $horaLimite;
            }
        }

        // Si no encontramos una hora límite activa, devolver la próxima
        foreach ($horasLimite as $horaLimite) {
            if ($horaActual->lt(Carbon::parse($horaLimite->hora_limite))) {
                return $horaLimite;
            }
        }

        // Si no hay ninguna hora límite futura, devolver la última del día
        return $horasLimite->last() ?? null;
    }

    public function create()
    {
        try {
            // Verificar si estamos dentro del tiempo permitido
            $horaLimite = $this->getHoraLimiteActiva();
            $horaActual = now();
            $horaInicioPedidos = Carbon::parse($horaLimite->hora_limite)->subHour();

            if ($horaActual->lt($horaInicioPedidos)) {
                return redirect()->route('pedidos.index')
                    ->with('error', 'Los pedidos para este turno se habilitarán a las ' . $horaInicioPedidos->format('H:i'));
            }

            if ($horaActual->gt(Carbon::parse($horaLimite->hora_limite))) {
                return redirect()->route('pedidos.index')
                    ->with('error', 'El tiempo para crear pedidos para este turno ha terminado');
            }

            $usuario = Usuario::with(['tienda', 'area'])
                ->findOrFail(Auth::id());

            $areas = Area::where('status', true)
                ->where('is_deleted', false)
                ->get();

            $unidades = UMedida::where('status', true)
                ->where('is_deleted', false)
                ->get();

            // Calcular tiempo restante
            $horaLimiteCarbon = Carbon::parse($horaLimite->hora_limite);
            $tiempoRestante = $horaActual->diff($horaLimiteCarbon);

            return view('pedidos.create', compact(
                'usuario',
                'areas',
                'unidades',
                'horaLimite',
                'tiempoRestante'
            ));
        } catch (\Exception $e) {
            Log::error('Error en PedidoController@create: ' . $e->getMessage());
            return redirect()->route('pedidos.index')
                ->with('error', 'Error al cargar el formulario de pedido');
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // Validar los datos recibidos
            $validator = Validator::make($request->all(), [
                'detalles' => 'required|array|min:1',
                'detalles.*.id_areas' => 'required|exists:areas,id_areas',
                'detalles.*.id_recetas' => 'nullable|exists:recetas_cab,id_recetas',
                'detalles.*.cantidad' => 'required|numeric|min:0.1',
                'detalles.*.id_u_medidas' => 'required|exists:u_medidas,id_u_medidas',
                'id_hora_limite' => 'required|exists:hora_limites,id_hora_limite',
                'detalles.*.foto_referencial' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'detalles.*.descripcion' => 'required_if:detalles.*.es_personalizado,true',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $usuario = Auth::user();
            $ahora = Carbon::now();
            $horaLimite = HoraLimite::find($request->id_hora_limite);

            // Validar la hora límite
            $horaFinPedidos = Carbon::createFromFormat('H:i:s', $horaLimite->hora_limite);
            $horaInicioPedidos = (clone $horaFinPedidos)->subHour();

            $currentTime = Carbon::createFromFormat('H:i:s', $ahora->format('H:i:s'));

            if (!$currentTime->between($horaInicioPedidos, $horaFinPedidos)) {
                return response()->json([
                    'success' => false, 
                    'message' => 'No es posible realizar pedidos en este momento. El horario permitido es de ' . 
                                 $horaInicioPedidos->format('H:i') . ' a ' . $horaFinPedidos->format('H:i')
                ], 403);
            }

            // Crear cabecera del pedido
            $pedidoCab = PedidoCabecera::create([
                'id_usuarios' => $usuario->id_usuarios,
                'id_tiendas_api' => $usuario->id_tiendas_api,
                'fecha_created' => $ahora->toDateString(),
                'hora_created' => $ahora->toTimeString(),
                'fecha_last_update' => $ahora->toDateString(),
                'hora_last_update' => $ahora->toTimeString(),
                'esta_dentro_de_hora' => true,
                'id_hora_limite' => $horaLimite->id_hora_limite,
                'hora_limite' => $horaLimite->hora_limite,
                'doc_interno' => 'PED-' . $ahora->format('Ymd-His') . '-' . strtoupper(substr(uniqid(), -5)),
                'is_deleted' => false,
                'status' => true
            ]);

            // Crear detalles del pedido
            foreach ($request->detalles as $detalle) {
                $fotoPath = null;
                if (isset($detalle['foto_referencial']) && $detalle['foto_referencial'] instanceof \Illuminate\Http\UploadedFile) {
                    $fotoPath = $detalle['foto_referencial']->store('pedidos', 'public');
                }

                $receta = isset($detalle['id_recetas']) ?
                    RecetaCabecera::find($detalle['id_recetas']) : null;

                PedidoDetalle::create([
                    'id_pedidos_cab' => $pedidoCab->id_pedidos_cab,
                    'id_areas' => $detalle['id_areas'],
                    'id_recetas' => $detalle['id_recetas'] ?? null,
                    'id_productos_api' => $receta ? $receta->id_productos_api : null,
                    'cantidad' => $detalle['cantidad'],
                    'id_u_medidas' => $detalle['id_u_medidas'],
                    'es_personalizado' => $detalle['es_personalizado'] ?? false,
                    'descripcion' => $detalle['descripcion'] ?? null,
                    'foto_referencial_url' => $detalle['foto_referencial_url'] ?? null,
                    'id_estados' => $detalle['id_estados'] ?? 2, // Estado pendiente por defecto
                    'is_deleted' => false,
                    'foto_referencial' => $fotoPath
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pedido creado correctamente',
                'redirect' => route('pedidos.index'),
                'clearStorage' => true // Señal para que el frontend limpie el localStorage
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear pedido: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al crear el pedido: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $pedido = PedidoCabecera::with([
            'pedidosDetalle.area',
            'pedidosDetalle.receta',
            'pedidosDetalle.uMedida',
            'pedidosDetalle.estado',
            'usuario.tienda',
            'horaLimite'
        ])->findOrFail($id);

        return view('pedidos.show', compact('pedido'));
    }

    public function edit($id)
    {
        $pedido = PedidoCabecera::with([
            'pedidosDetalle.area',
            'pedidosDetalle.receta',
            'pedidosDetalle.uMedida',
            'pedidosDetalle.estado',
            'usuario.tienda',
            'horaLimite'
        ])->findOrFail($id);

        // Verificar si el pedido está dentro del tiempo permitido para edición
        $horaActual = now();
        $horaLimitePedido = Carbon::parse($pedido->hora_limite);
        $horaInicioEdicion = $horaLimitePedido->copy()->subHour();

        if ($horaActual->lt($horaInicioEdicion)) {
            return redirect()->route('pedidos.index')
                ->with('error', 'Aún no es hora de editar este pedido (se habilita a las ' . $horaInicioEdicion->format('H:i') . ')');
        }

        if ($horaActual->gt($horaLimitePedido)) {
            return redirect()->route('pedidos.index')
                ->with('error', 'El tiempo para editar este pedido ha terminado');
        }

        $areas = Area::where('status', true)
            ->where('is_deleted', false)
            ->get();

        $unidades = UMedida::where('status', true)
            ->where('is_deleted', false)
            ->get();

        $pedidosData = $pedido->pedidosDetalle->map(function ($detalle) {
            return [
                'id_pedidos_det' => $detalle->id_pedidos_det,
                'id_area' => $detalle->id_areas,
                'area_nombre' => $detalle->area->nombre,
                'id_receta' => $detalle->id_recetas,
                'receta_nombre' => $detalle->receta ? $detalle->receta->nombre : null,
                'id_producto' => $detalle->id_productos_api,
                'cantidad' => $detalle->cantidad,
                'id_u_medida' => $detalle->id_u_medidas,
                'u_medida_nombre' => $detalle->uMedida->nombre,
                'es_personalizado' => $detalle->es_personalizado,
                'descripcion' => $detalle->descripcion,
                'foto_referencial_url' => $detalle->foto_referencial ? 'storage/' . $detalle->foto_referencial : null,
                'id_estado' => $detalle->id_estados,
                'foto_referencial' => null
            ];
        });

        return view('pedidos.edit', compact(
            'pedido',
            'areas',
            'unidades',
            'pedidosData'
        ));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'detalles' => 'required|array',
            'detalles.*.id_areas' => 'required|exists:areas,id_areas',
            'detalles.*.cantidad' => 'required|numeric|min:0.1',
            'detalles.*.id_u_medidas' => 'required|exists:u_medidas,id_u_medidas',
            'detalles.*.es_personalizado' => 'sometimes|boolean',
            'detalles.*.foto_referencial' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $pedido = PedidoCabecera::findOrFail($id);

            // Verificar si el pedido está dentro del tiempo permitido para edición
            $horaActual = now();
            $horaLimitePedido = Carbon::parse($pedido->hora_limite);
            $horaInicioEdicion = $horaLimitePedido->copy()->subHour();

            if ($horaActual->lt($horaInicioEdicion) || $horaActual->gt($horaLimitePedido)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede actualizar un pedido fuera del tiempo límite'
                ], 403);
            }

            // Actualizar detalles existentes o crear nuevos
            foreach ($request->detalles as $detalleData) {
                $data = [
                    'id_areas' => $detalleData['id_areas'],
                    'id_recetas' => $detalleData['id_recetas'] ?? null,
                    'id_productos_api' => $detalleData['id_productos_api'] ?? null,
                    'id_u_medidas' => $detalleData['id_u_medidas'],
                    'cantidad' => $detalleData['cantidad'],
                    'es_personalizado' => $detalleData['es_personalizado'] ?? false,
                    'descripcion' => $detalleData['descripcion'] ?? null,
                    'id_estados' => $detalleData['id_estados'] ?? 2, // Pendiente por defecto
                ];

                if (isset($detalleData['foto_referencial']) && $detalleData['foto_referencial'] instanceof \Illuminate\Http\UploadedFile) {
                    if (isset($detalleData['id_pedidos_det'])) {
                        $detalleExistente = PedidoDetalle::find($detalleData['id_pedidos_det']);
                        if ($detalleExistente && $detalleExistente->foto_referencial) {
                            Storage::delete('public/' . $detalleExistente->foto_referencial);
                        }
                    }

                    $data['foto_referencial'] = $detalleData['foto_referencial']->store('pedidos', 'public');
                } elseif (isset($detalleData['foto_referencial_url'])) {
                    $data['foto_referencial'] = $detalleData['foto_referencial_url'];
                }

                if (isset($detalleData['id_pedidos_det'])) {
                    PedidoDetalle::where('id_pedidos_det', $detalleData['id_pedidos_det'])
                        ->update($data);
                } else {
                    $data['id_pedidos_cab'] = $pedido->id_pedidos_cab;
                    PedidoDetalle::create($data);
                }
            }

            $pedido->update([
                'fecha_last_update' => Carbon::now()->toDateString(),
                'hora_last_update' => Carbon::now()->toTimeString()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pedido actualizado exitosamente',
                'redirect' => route('pedidos.show', $pedido->id_pedidos_cab)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar pedido: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el pedido: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $pedido = PedidoCabecera::findOrFail($id);

            // Verificar si está dentro del tiempo permitido para eliminación
            $horaActual = now();
            $horaLimitePedido = Carbon::parse($pedido->hora_limite);
            $horaInicioEdicion = $horaLimitePedido->copy()->subHour();

            if ($horaActual->lt($horaInicioEdicion) || $horaActual->gt($horaLimitePedido)) {
                return redirect()->back()
                    ->with('error', 'No se puede eliminar un pedido fuera del tiempo límite permitido');
            }

            // Actualizar campos manuales antes de soft delete
            $pedido->delete();
            $pedido->update([
                'is_deleted' => true,
                'status' => false,
                'fecha_last_update' => Carbon::now()->toDateString(),
                'hora_last_update' => Carbon::now()->toTimeString(),
                'deleted_at' => Carbon::now()
            ]);

            // Actualizar los detalles del pedido
            $pedido->pedidosDetalle()->update([
                'is_deleted' => true,
                
            ]);

            DB::commit();

            return redirect()->route('pedidos.index')
                ->with('success', 'Pedido eliminado correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar pedido: ' . $e->getMessage());

            return back()
                ->with('error', 'Error al eliminar el pedido: ' . $e->getMessage());
        }
    }

    public function buscarRecetas(Request $request)
    {
        try {
            $request->validate([
                'id_areas' => 'required|exists:areas,id_areas',
                'termino' => 'required|string|min:3'
            ]);

            $recetas = RecetaCabecera::with(['uMedida', 'producto'])
                ->where('id_areas', $request->id_areas)
                ->where(function ($query) use ($request) {
                    $query->where('nombre', 'ilike', '%' . $request->termino . '%')
                        ->orWhereHas('producto', function ($q) use ($request) {
                            $q->where('nombre', 'ilike', '%' . $request->termino . '%');
                        });
                })
                ->where('status', true)
                ->where('is_deleted', false)
                ->limit(10)
                ->get()
                ->map(function ($receta) {
                    return [
                        'id' => $receta->id_recetas,
                        'nombre' => $receta->nombre,
                        'id_productos_api' => $receta->id_productos_api,
                        'id_u_medidas' => $receta->id_u_medidas,
                        'u_medida_nombre' => $receta->uMedida->nombre ?? 'N/A',
                        'producto_nombre' => $receta->producto->nombre ?? 'N/A'
                    ];
                });

            return response()->json($recetas);
        } catch (\Exception $e) {
            Log::error('Error en buscarRecetas: ' . $e->getMessage());
            return response()->json(['error' => 'Error en la búsqueda'], 500);
        }
    }

    public function generatePdf($id)
    {
        $pedido = PedidoCabecera::with([
            'pedidosDetalle.area',
            'pedidosDetalle.receta',
            'pedidosDetalle.uMedida',
            'pedidosDetalle.estado',
            'usuario.rol',
            'tienda'
        ])->findOrFail($id);

        $options = [
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'defaultFont' => 'Arial'
        ];

        $pdf = Pdf::loadView('pedidos.pdf.individual', compact('pedido'))
            ->setOptions($options)
            ->setPaper('a4', 'portrait');

        // Guardar el PDF en storage
        $filename = 'pedido_' . $pedido->id_pedidos_cab . '_' . now()->format('YmdHis') . '.pdf';
        $path = 'pedidos/pdf/' . $filename;

        // Asegurarse de que el directorio existe
        if (!Storage::disk('public')->exists('pedidos/pdf')) {
            Storage::disk('public')->makeDirectory('pedidos/pdf');
        }

        Storage::disk('public')->put($path, $pdf->output());

        // Actualizar la ruta del PDF en la base de datos
        $pedido->update(['pdf_path' => $path]);

        return $pdf->stream($filename);
    }

    public function generateConsolidadoPdf(Request $request)
    {
        $usuario = Auth::user();
        $today = Carbon::today()->format('Y-m-d');

        $query = PedidoCabecera::with([
            'pedidosDetalle.area',
            'pedidosDetalle.receta',
            'pedidosDetalle.uMedida',
            'pedidosDetalle.estado',
            'usuario',
            'tienda'
        ])
            ->whereDate('fecha_created', $today)
            ->where('is_deleted', false);

        // Aplicar filtros según rol
        if ($usuario->id_roles == 4) { // Rol operador
            $query->where('id_tiendas_api', $usuario->id_tiendas_api);
        } elseif ($usuario->id_roles != 1) { // Si no es admin
            $query->where('id_usuarios', $usuario->id_usuarios);
        }

        $pedidos = $query->get();
        $fecha = Carbon::today()->format('d/m/Y');

        $pdf = Pdf::loadView('pedidos.pdf.consolidado', compact('pedidos', 'fecha'));

        // Generar nombre único para el consolidado
        $filename = 'consolidado_pedidos_' . now()->format('YmdHis') . '.pdf';

        return $pdf->stream($filename);
    }
}


