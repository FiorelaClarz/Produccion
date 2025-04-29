<?php

namespace App\Http\Controllers;

use App\Models\PedidoCabecera;
use App\Models\PedidoDetalle;
use App\Models\RecetaCabecera;
use App\Models\Usuario;
use App\Models\Area;
use App\Models\UMedida;
use App\Models\Estado;
use App\Models\HoraLimite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PedidoController extends Controller
{
    public function index()
    {
        $pedidos = PedidoCabecera::with(['usuario', 'tienda', 'pedidosDetalle'])
            ->where('is_deleted', false)
            ->orderBy('fecha_created', 'desc')
            ->get();

        return view('pedidos.index', compact('pedidos'));
    }

    public function create()
    {
        try {
            $areas = Area::where('status', true)
                ->where('is_deleted', false)
                ->get();
                
            $unidades = UMedida::activos()->get();
            $horaLimite = HoraLimite::where('status', true)->first();
            
            $usuario = Usuario::with(['tienda', 'area'])
                ->findOrFail(Auth::id());
                
            return view('pedidos.create', compact(
                'areas', 
                'unidades', 
                'horaLimite', 
                'usuario'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error en PedidoController@create: ' . $e->getMessage());
            return redirect()->route('pedidos.index')
                ->with('error', 'Error al cargar el formulario de pedido');
        }
    }

    public function store(Request $request)
    {
        // Validación inicial
        $request->validate([
            'detalles' => 'required|json',
        ]);

        DB::beginTransaction();
        
        try {
            $detalles = json_decode($request->detalles, true);
            
            // Validar el JSON
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Formato de datos inválido');
            }
            
            // Validar que haya al menos un detalle
            if (count($detalles) === 0) {
                throw new \Exception('Debe agregar al menos un detalle al pedido');
            }

            // Validar cada detalle
            foreach ($detalles as $detalle) {
                if (!isset($detalle['id_areas'])) {
                    throw new \Exception('Todos los detalles deben tener un área asignada');
                }
                // Agregar más validaciones según necesites
            }

            // Verificar hora límite
            $horaLimite = HoraLimite::where('status', true)->first();
            $ahora = Carbon::now();
            
            if ($horaLimite && $ahora->greaterThan(Carbon::parse($horaLimite->hora_limite))) {
                throw new \Exception('No se puede realizar el pedido, ha pasado la hora límite.');
            }
            
            // Crear cabecera del pedido
            $pedidoCab = PedidoCabecera::create([
                'id_usuarios' => Auth::id(),
                'id_tiendas_api' => Auth::user()->id_tiendas_api,
                'fecha_created' => $ahora->toDateString(),
                'hora_created' => $ahora->toTimeString(),
                'fecha_last_update' => $ahora->toDateString(),
                'hora_last_update' => $ahora->toTimeString(),
                'esta_dentro_de_hora' => true,
                'id_hora_limite' => $horaLimite->id_hora_limite,
                'doc_interno' => 'PED-' . strtoupper(uniqid()),
                'is_deleted' => false,
                'status' => true
            ]);
            
            // Crear detalles del pedido
            foreach ($detalles as $detalle) {
                PedidoDetalle::create([
                    'id_pedidos_cab' => $pedidoCab->id_pedidos_cab,
                    'id_areas' => $detalle['id_areas'],
                    'id_recetas' => $detalle['id_recetas'] ?? null,
                    'id_productos_api' => $detalle['id_productos_api'] ?? null,
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
                ->with('success', 'Pedido creado correctamente.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear pedido: ' . $e->getMessage());
            Log::error('Datos recibidos: ' . print_r($request->all(), true));
            
            return back()
                ->with('error', 'Error al crear el pedido: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Mostrar un pedido específico
    public function show(PedidoCabecera $pedido)
    {
        $pedido->load([
            'usuario',
            'tienda',
            'pedidosDetalle',
            'pedidosDetalle.area',
            'pedidosDetalle.receta',
            'pedidosDetalle.uMedida',
            'pedidosDetalle.estado'
        ]);

        return view('pedidos.show', compact('pedido'));
    }

    // Mostrar formulario de edición
    public function edit(PedidoCabecera $pedido)
    {
        // Verificar si se puede editar (dentro de hora límite)
        $horaLimite = HoraLimite::find($pedido->id_hora_limite);
        $ahora = Carbon::now();

        if (!$horaLimite || $ahora->greaterThan(Carbon::parse($horaLimite->hora_limite))) {
            return redirect()->route('pedidos.index')->with('error', 'No se puede editar el pedido, ha pasado la hora límite.');
        }

        $areas = Area::where('status', true)->where('is_deleted', false)->get();
        $unidades = UMedida::activos()->get();
        $usuario = Auth::user();

        // Cargar detalles existentes
        $detallesExistentes = $pedido->pedidosDetalle->map(function ($detalle) {
            return [
                'id_areas' => $detalle->id_areas,
                'area_nombre' => $detalle->area->nombre,
                'id_recetas' => $detalle->id_recetas,
                'receta_nombre' => $detalle->receta ? $detalle->receta->nombre : 'Personalizado',
                'cantidad' => $detalle->cantidad,
                'id_u_medidas' => $detalle->id_u_medidas,
                'unidad_nombre' => $detalle->uMedida->nombre,
                'es_personalizado' => $detalle->es_personalizado,
                'descripcion' => $detalle->descripcion,
                'foto_referencial_url' => $detalle->foto_referencial_url,
                'id_estados' => $detalle->id_estados
            ];
        });

        return view('pedidos.edit', compact('pedido', 'areas', 'unidades', 'horaLimite', 'usuario', 'detallesExistentes'));
    }

    // Actualizar pedido
    public function update(Request $request, PedidoCabecera $pedido)
    {
        // Verificar hora límite
        $horaLimite = HoraLimite::find($pedido->id_hora_limite);
        $ahora = Carbon::now();

        if (!$horaLimite || $ahora->greaterThan(Carbon::parse($horaLimite->hora_limite))) {
            return redirect()->route('pedidos.index')->with('error', 'No se puede actualizar el pedido, ha pasado la hora límite.');
        }

        // Validación
        $request->validate([
            'detalles' => 'required|array|min:1',
            'detalles.*.id_areas' => 'required|exists:areas,id_areas',
            'detalles.*.id_recetas' => 'nullable|exists:recetas_cab,id_recetas',
            'detalles.*.cantidad' => 'required|numeric|min:0.1',
            'detalles.*.id_u_medidas' => 'required|exists:u_medidas,id_u_medidas',
        ]);

        // Actualizar cabecera
        $pedido->update([
            'fecha_last_update' => $ahora->toDateString(),
            'hora_last_update' => $ahora->toTimeString()
        ]);

        // Eliminar detalles antiguos (soft delete)
        $pedido->pedidosDetalle()->update(['is_deleted' => true]);

        // Crear nuevos detalles
        foreach ($request->detalles as $detalle) {
            $receta = RecetaCabecera::find($detalle['id_recetas']);

            PedidoDetalle::create([
                'id_pedidos_cab' => $pedido->id_pedidos_cab,
                'id_productos_api' => $receta ? $receta->id_productos_api : null,
                'id_areas' => $detalle['id_areas'],
                'id_recetas' => $detalle['id_recetas'],
                'cantidad' => $detalle['cantidad'],
                'id_u_medidas' => $detalle['id_u_medidas'],
                'es_personalizado' => $detalle['es_personalizado'] ?? false,
                'descripcion' => $detalle['descripcion'] ?? null,
                'foto_referencial_url' => $detalle['foto_referencial_url'] ?? null,
                'id_estados' => $detalle['id_estados'] ?? 2, // Mantener estado o poner pendiente
                'is_deleted' => false
            ]);
        }

        return redirect()->route('pedidos.index')->with('success', 'Pedido actualizado correctamente.');
    }

    // Eliminar pedido (soft delete)
    public function destroy(PedidoCabecera $pedido)
    {
        $pedido->update(['is_deleted' => true]);
        $pedido->pedidosDetalle()->update(['is_deleted' => true]);

        return redirect()->route('pedidos.index')->with('success', 'Pedido eliminado correctamente.');
    }

    // Método para buscar recetas por área y término
    public function buscarRecetas(Request $request)
    {
        try {
            $request->validate([
                'id_areas' => 'required|exists:areas,id_areas',
                'termino' => 'required|string|min:3'
            ]);
            
            $recetas = RecetaCabecera::with(['uMedida', 'producto'])
                ->where('id_areas', $request->id_areas)
                ->where(function($query) use ($request) {
                    $query->where('nombre', 'ilike', '%'.$request->termino.'%')
                          ->orWhereHas('producto', function($q) use ($request) {
                              $q->where('nombre', 'ilike', '%'.$request->termino.'%');
                          });
                })
                ->where('status', true)
                ->where('is_deleted', false)
                ->limit(10)
                ->get()
                ->map(function($receta) {
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
