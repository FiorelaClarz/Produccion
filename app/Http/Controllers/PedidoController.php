<?php

namespace App\Http\Controllers;

use App\Models\PedidoCabecera;
use App\Models\PedidoDetalle;
use App\Models\RecetaCabecera;
use App\Models\Area;
use App\Models\Estado;
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

class PedidoController extends Controller
{

    public function index(Request $request)
{
    // Obtener el usuario autenticado
    $usuario = Auth::user();
    
    $query = PedidoCabecera::with([
            'usuario', 
            'tienda', 
            'pedidosDetalle.area',
            'pedidosDetalle.estado',
            'pedidosDetalle.uMedida'
        ])
        ->where('is_deleted', false);

    // Filtro por rol de usuario
    if ($usuario->id_roles == 4) { // Rol operador
        $query->where('id_tiendas_api', $usuario->id_tiendas_api);
    } elseif ($usuario->id_roles != 1) { // Si no es admin ni operador
        $query->where('id_usuarios', $usuario->id_usuarios);
    }
    // Para admin (id_roles = 1) no aplicamos filtro adicional

    // Ordenamiento
    $query->orderBy('fecha_last_update', 'desc')
          ->orderBy('hora_last_update', 'desc');

    // Determinar el filtro a aplicar (por defecto es 'today')
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
            $query->whereBetween('fecha_last_update', [$today->copy()->subWeek(), $today]);
            break;
        case 'custom':
            if ($request->has('custom_date')) {
                $query->whereDate('fecha_last_update', $request->custom_date);
            }
            break;
    }

    $pedidos = $query->paginate(10);

    // Obtener la hora límite para el botón de nuevo pedido
    $horaLimiteActual = HoraLimite::where('status', true)
        ->where('is_deleted', false)
        ->first();

    $horaActual = now();
    $horaLimite = $horaLimiteActual ? Carbon::parse($horaLimiteActual->hora_limite) : null;
    $dentroDeHoraPermitida = $horaLimite ? $horaActual->lte($horaLimite) : false;

    return view('pedidos.index', compact('pedidos', 'dentroDeHoraPermitida', 'horaLimiteActual', 'filter', 'usuario'));
}

    public function create()
    {
        try {
            // Obtener el usuario autenticado con sus relaciones
            $usuario = Usuario::with(['tienda', 'area'])
                ->findOrFail(Auth::id());

            // Obtener áreas activas
            $areas = Area::where('status', true)
                ->where('is_deleted', false)
                ->get();

            // Obtener unidades de medida activas
            $unidades = UMedida::where('status', true)
                ->where('is_deleted', false)
                ->get();

            // Obtener la hora límite activa
            $horaLimite = HoraLimite::where('status', true)
                ->where('is_deleted', false)
                ->first();

            if (!$horaLimite) {
                return redirect()->route('pedidos.index')
                    ->with('error', 'No hay hora límite configurada. Contacte al administrador.');
            }

            // Asegurar formato correcto de la hora
            $horaLimite->hora_limite = Carbon::createFromFormat('H:i:s', $horaLimite->hora_limite)->format('H:i:s');

            // Calcular tiempo restante
            $ahora = Carbon::now();
            $horaLimiteCarbon = Carbon::parse($horaLimite->hora_limite);
            $tiempoRestante = $ahora->diff($horaLimiteCarbon);

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
                'detalles.*.foto_referencial' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
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

            // Verificar si está dentro de la hora límite
            $estaDentroDeHora = $ahora->lte(Carbon::parse($horaLimite->hora_limite));

            if (!$estaDentroDeHora) {
                return response()->json([
                    'success' => false,
                    'message' => 'El tiempo para realizar pedidos ha terminado'
                ], 400);
            }

            // Crear cabecera del pedido
            $pedidoCab = PedidoCabecera::create([
                'id_usuarios' => $usuario->id_usuarios,
                'id_tiendas_api' => $usuario->id_tiendas_api,
                'fecha_created' => $ahora->toDateString(),
                'hora_created' => $ahora->toTimeString(),
                'fecha_last_update' => $ahora->toDateString(),
                'hora_last_update' => $ahora->toTimeString(),
                'esta_dentro_de_hora' => $estaDentroDeHora,
                'id_hora_limite' => $horaLimite->id_hora_limite,
                'hora_limite' => $horaLimite->hora_limite, // Guardamos la hora límite
                'doc_interno' => 'PED-' . $ahora->format('Ymd-His') . '-' . strtoupper(substr(uniqid(), -5)),
                'is_deleted' => false,
                'status' => true
            ]);

            // Crear detalles del pedido
            foreach ($request->detalles as $detalle) {
                $fotoPath = null;
                // Procesar la imagen si existe
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
                'redirect' => route('pedidos.index')
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
            'pedidosDetalle.estado',  // Estado viene de PedidoDetalle
            'usuario.tienda',
            'horaLimite'
        ])
            ->findOrFail($id);

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
        ])
            ->findOrFail($id);

        // Validar si el pedido está dentro del tiempo permitido
        if (!$pedido->esta_dentro_de_hora) {
            return redirect()->route('pedidos.index')
                ->with('error', 'No se puede editar un pedido fuera del tiempo límite');
        }


        // Preparar los datos de los pedidos para JavaScript
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

        $areas = Area::where('status', true)
            ->where('is_deleted', false)
            ->get();

        $unidades = UMedida::where('status', true)
            ->where('is_deleted', false)
            ->get();

        $horaLimite = $pedido->horaLimite;

        return view('pedidos.edit', compact('pedido', 'areas', 'unidades', 'horaLimite', 'pedidosData'));
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

            // Validar si el pedido está dentro del tiempo permitido
            if (!$pedido->esta_dentro_de_hora) {
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

                // Manejar la imagen
                if (isset($detalleData['foto_referencial']) && $detalleData['foto_referencial'] instanceof \Illuminate\Http\UploadedFile) {
                    // Eliminar imagen anterior si existe
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
                    // Actualizar detalle existente
                    PedidoDetalle::where('id_pedidos_det', $detalleData['id_pedidos_det'])
                        ->update($data);
                } else {
                    // Crear nuevo detalle
                    $data['id_pedidos_cab'] = $pedido->id_pedidos_cab;
                    PedidoDetalle::create($data);
                }
            }

            // Actualizar fecha/hora de modificación
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

    public function destroy(PedidoCabecera $pedido)
    {
        DB::beginTransaction();

        // Validar si el pedido está dentro del tiempo permitido
        if (!$pedido->esta_dentro_de_hora) {
            return redirect()->route('pedidos.index')
                ->with('error', 'No se puede eliminar un pedido fuera del tiempo límite');
        }
        try {
            // Soft delete de la cabecera y detalles
            $pedido->update([
                'is_deleted' => true,
                'status' => false,
                'fecha_last_update' => Carbon::now()->toDateString(),
                'hora_last_update' => Carbon::now()->toTimeString()
            ]);

            $pedido->pedidosDetalle()->update(['is_deleted' => true]);

            DB::commit();

            return redirect()->route('pedidos.index')
                ->with('success', 'Pedido eliminado correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar pedido: ' . $e->getMessage());

            return back()
                ->with('error', 'Error al eliminar el pedido');
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
}
