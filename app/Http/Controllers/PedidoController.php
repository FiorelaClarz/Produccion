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

class PedidoController extends Controller
{
    public function index()
    {
        $pedidos = PedidoCabecera::with(['usuario', 'tienda', 'pedidosDetalle'])
            ->where('is_deleted', false)
            ->orderBy('fecha_created', 'desc')
            ->orderBy('hora_created', 'desc')
            ->paginate(10);

        return view('pedidos.index', compact('pedidos'));
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

            // Verificar si hay hora límite configurada
            if (!$horaLimite) {
                return redirect()->route('pedidos.index')
                    ->with('error', 'No hay hora límite configurada. Contacte al administrador.');
            }

            // Calcular tiempo restante
            $ahora = Carbon::now();
            $horaLimiteCarbon = Carbon::parse($horaLimite->hora_limite);
            $tiempoRestante = $ahora->diff($horaLimiteCarbon);

            return view('pedidos.create', compact(
                'usuario',
                'areas',
                'unidades', // Pasamos las unidades a la vista
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
                'id_hora_limite' => 'required|exists:hora_limites,id_hora_limite'
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
                'doc_interno' => 'PED-' . $ahora->format('Ymd-His') . '-' . strtoupper(substr(uniqid(), -5)),
                'is_deleted' => false,
                'status' => true
            ]);

            // Crear detalles del pedido
            foreach ($request->detalles as $detalle) {
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
                    'is_deleted' => false
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

    public function show(PedidoCabecera $pedido)
    {
        $pedido->load([
            'usuario',
            'tienda',
            'horaLimite',
            'pedidosDetalle' => function ($query) {
                $query->where('is_deleted', false)
                    ->with(['area', 'receta', 'uMedida', 'estado']);
            }
        ]);

        return view('pedidos.show', compact('pedido'));
    }

    public function edit(PedidoCabecera $pedido)
    {
        // Verificar si el pedido puede ser editado
        $horaLimite = $pedido->horaLimite;
        $ahora = Carbon::now();
        $puedeEditar = $ahora->lte(Carbon::parse($horaLimite->hora_limite));

        if (!$puedeEditar) {
            return redirect()->route('pedidos.index')
                ->with('error', 'El tiempo para editar este pedido ha expirado.');
        }

        $areas = Area::where('status', true)
            ->where('is_deleted', false)
            ->get();

        $unidades = UMedida::activos()->get();

        // Cargar detalles existentes
        $detallesExistentes = $pedido->pedidosDetalle()
            ->where('is_deleted', false)
            ->get()
            ->map(function ($detalle) {
                return [
                    'id_areas' => $detalle->id_areas,
                    'area_nombre' => $detalle->area->nombre,
                    'id_recetas' => $detalle->id_recetas,
                    'receta_nombre' => $detalle->receta ? $detalle->receta->nombre : 'Personalizado',
                    'cantidad' => $detalle->cantidad,
                    'id_u_medidas' => $detalle->id_u_medidas,
                    'u_medida_nombre' => $detalle->uMedida->nombre,
                    'es_personalizado' => $detalle->es_personalizado,
                    'descripcion' => $detalle->descripcion,
                    'foto_referencial_url' => $detalle->foto_referencial_url,
                    'id_estados' => $detalle->id_estados
                ];
            });

        return view('pedidos.edit', compact(
            'pedido',
            'areas',
            'unidades',
            'detallesExistentes'
        ));
    }

    public function update(Request $request, PedidoCabecera $pedido)
    {
        // Verificar hora límite
        $horaLimite = $pedido->horaLimite;
        $ahora = Carbon::now();

        if ($ahora->gt(Carbon::parse($horaLimite->hora_limite))) {
            return redirect()->route('pedidos.index')
                ->with('error', 'No se puede actualizar el pedido, ha pasado la hora límite.');
        }

        DB::beginTransaction();

        try {
            // Validar los datos
            $validator = Validator::make($request->all(), [
                'detalles' => 'required|array|min:1',
                'detalles.*.id_areas' => 'required|exists:areas,id_areas',
                'detalles.*.id_recetas' => 'nullable|exists:recetas_cab,id_recetas',
                'detalles.*.cantidad' => 'required|numeric|min:0.1',
                'detalles.*.id_u_medidas' => 'required|exists:u_medidas,id_u_medidas',
            ]);

            if ($validator->fails()) {
                return back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Actualizar cabecera
            $pedido->update([
                'fecha_last_update' => $ahora->toDateString(),
                'hora_last_update' => $ahora->toTimeString()
            ]);

            // Eliminar detalles antiguos (soft delete)
            $pedido->pedidosDetalle()->update(['is_deleted' => true]);

            // Crear nuevos detalles
            foreach ($request->detalles as $detalle) {
                $receta = isset($detalle['id_recetas']) ?
                    RecetaCabecera::find($detalle['id_recetas']) : null;

                PedidoDetalle::create([
                    'id_pedidos_cab' => $pedido->id_pedidos_cab,
                    'id_areas' => $detalle['id_areas'],
                    'id_recetas' => $detalle['id_recetas'] ?? null,
                    'id_productos_api' => $receta ? $receta->id_productos_api : null,
                    'cantidad' => $detalle['cantidad'],
                    'id_u_medidas' => $detalle['id_u_medidas'],
                    'es_personalizado' => $detalle['es_personalizado'] ?? false,
                    'descripcion' => $detalle['descripcion'] ?? null,
                    'foto_referencial_url' => $detalle['foto_referencial_url'] ?? null,
                    'id_estados' => $detalle['id_estados'] ?? 2, // Pendiente por defecto
                    'is_deleted' => false
                ]);
            }

            DB::commit();

            return redirect()->route('pedidos.index')
                ->with('success', 'Pedido actualizado correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar pedido: ' . $e->getMessage());

            return back()
                ->with('error', 'Error al actualizar el pedido')
                ->withInput();
        }
    }

    public function destroy(PedidoCabecera $pedido)
    {
        DB::beginTransaction();

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
