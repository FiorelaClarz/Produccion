<?php

namespace App\Http\Controllers;

use App\Models\MermaCabecera;
use App\Models\MermaDetalle;
use App\Models\RecetaCabecera;
use App\Models\Area;
use App\Models\UMedida;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class MermaController extends Controller
{
    /**
     * Obtiene el costo de un producto por su ID
     * 
     * @param Request $request Request con el id_productos_api
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerCosto(Request $request)
    {
        try {
            $idProducto = $request->id_productos_api;
            $costo = 0;
            
            if ($idProducto) {
                $producto = Producto::find($idProducto);
                if ($producto) {
                    $costo = $producto->costo ?? 0;
                }
            }
            
            return response()->json([
                'status' => 'success',
                'costo' => $costo
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener costo: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener el costo del producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Constructor del controlador
     * Aplica middleware de autenticación y verifica que solo
     * administradores y operadores puedan acceder
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            // Verificar si el usuario es admin (id_roles === 1) o operador (id_roles === 4)
            if ($user->id_roles !== 1 && $user->id_roles !== 4) {
                return redirect()->route('home')->with('error', 'No tiene permisos para acceder a esta sección.');
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $usuario = Auth::user();
        
        $query = MermaCabecera::with([
            'usuario',
            'tienda',
            'mermasDetalle' => function ($query) {
                $query->where('is_deleted', false);
            },
            'mermasDetalle.area',
            'mermasDetalle.receta',
            'mermasDetalle.uMedida'
        ])
        ->where('is_deleted', false);

        // Filtro por rol de usuario
        if ($usuario->id_roles == 4) { // Rol operador
            $query->where('id_tiendas_api', $usuario->id_tiendas_api);
        }

        // Ordenamiento
        $query->orderBy('fecha_registro', 'desc')
              ->orderBy('hora_registro', 'desc');

        // Determinar el filtro a aplicar
        $filter = $request->filter ?? 'today';
        $today = Carbon::today();

        switch ($filter) {
            case 'today':
                $query->whereDate('fecha_registro', $today);
                break;
            case 'yesterday':
                $query->whereDate('fecha_registro', $today->copy()->subDay());
                break;
            case 'week':
                $query->whereBetween('fecha_registro', [$today->copy()->subWeek(), $today]);
                break;
            case 'custom':
                if ($request->has('custom_date')) {
                    $query->whereDate('fecha_registro', $request->custom_date);
                }
                break;
        }

        $mermas = $query->paginate(10);

        return view('mermas.index', compact('mermas', 'filter', 'usuario'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $usuario = Auth::user();
        $areas = Area::where('status', true)
                    ->where('is_deleted', false)
                    ->orderBy('nombre')
                    ->get();
                    
        $uMedidas = UMedida::where('status', true)
                        ->where('is_deleted', false)
                        ->orderBy('nombre')
                        ->get();

        return view('mermas.create', compact('usuario', 'areas', 'uMedidas'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // Validación de datos de cabecera
            $validator = Validator::make($request->all(), [
                'detalles' => 'required|array|min:1',
                'detalles.*.id_areas' => 'required|exists:areas,id_areas',
                'detalles.*.id_recetas' => 'required|exists:recetas_cab,id_recetas',
                'detalles.*.cantidad' => 'required|numeric|min:0.01',
                'detalles.*.id_u_medidas' => 'required|exists:u_medidas,id_u_medidas',
            ], [
                'detalles.required' => 'Debe agregar al menos un detalle de merma.',
                'detalles.*.id_areas.required' => 'El área es obligatoria.',
                'detalles.*.id_recetas.required' => 'La receta es obligatoria.',
                'detalles.*.cantidad.required' => 'La cantidad es obligatoria.',
                'detalles.*.cantidad.numeric' => 'La cantidad debe ser un número.',
                'detalles.*.cantidad.min' => 'La cantidad debe ser mayor a cero.',
                'detalles.*.id_u_medidas.required' => 'La unidad de medida es obligatoria.',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
            }

            // Iniciar transacción
            DB::beginTransaction();

            $usuario = Auth::user();
            $now = Carbon::now();
            
            // Crear la cabecera de merma
            $mermaCabecera = MermaCabecera::create([
                'id_usuarios' => $usuario->id_usuarios,
                'id_tiendas_api' => $usuario->id_tiendas_api,
                'fecha_registro' => $now->format('Y-m-d'),
                'hora_registro' => $now->format('H:i:s'),
                'last_update' => $now
            ]);

            // Procesar cada detalle
            foreach ($request->detalles as $detalle) {
                // Obtener id_productos_api directamente de la receta
                $receta = RecetaCabecera::find($detalle['id_recetas']);
                
                if (!$receta) {
                    throw new \Exception('La receta seleccionada no existe');
                }
                
                // Obtener el costo del producto
                $costo = 0;
                if ($receta->id_productos_api) {
                    $producto = Producto::find($receta->id_productos_api);
                    if ($producto) {
                        $costo = $producto->costo ?? 0;
                    }
                }
                
                // Calcular el total (cantidad * costo)
                $cantidad = floatval($detalle['cantidad']);
                $total = $cantidad * $costo;
                
                MermaDetalle::create([
                    'id_mermas_cab' => $mermaCabecera->id_mermas_cab,
                    'id_areas' => $detalle['id_areas'],
                    'id_recetas' => $detalle['id_recetas'],
                    'id_productos_api' => $receta->id_productos_api, // Obtenemos el ID del producto desde la receta
                    'cantidad' => $cantidad,
                    'costo' => $costo, // Costo unitario del producto
                    'total' => $total, // Total calculado (cantidad * costo)
                    'id_u_medidas' => $detalle['id_u_medidas'],
                    'obs' => $detalle['obs'] ?? null,
                    'is_deleted' => false
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Merma registrada correctamente',
                'id' => $mermaCabecera->id_mermas_cab,
                'redirect' => route('mermas.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar merma: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error al registrar la merma: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $merma = MermaCabecera::with([
            'usuario',
            'tienda',
            'mermasDetalle' => function ($query) {
                $query->where('is_deleted', false);
            },
            'mermasDetalle.area',
            'mermasDetalle.receta',
            'mermasDetalle.uMedida'
        ])->findOrFail($id);

        return view('mermas.show', compact('merma'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $merma = MermaCabecera::with([
            'usuario',
            'tienda',
            'mermasDetalle' => function ($query) {
                $query->where('is_deleted', false);
            },
            'mermasDetalle.area',
            'mermasDetalle.receta',
            'mermasDetalle.uMedida'
        ])->findOrFail($id);

        $usuario = Auth::user();
        $areas = Area::where('status', true)
                    ->where('is_deleted', false)
                    ->orderBy('nombre')
                    ->get();
                    
        $uMedidas = UMedida::where('status', true)
                        ->where('is_deleted', false)
                        ->orderBy('nombre')
                        ->get();

        return view('mermas.edit', compact('merma', 'usuario', 'areas', 'uMedidas'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            // Validación de datos
            $validator = Validator::make($request->all(), [
                'detalles' => 'required|array|min:1',
                'detalles.*.id_areas' => 'required|exists:areas,id_areas',
                'detalles.*.id_recetas' => 'required|exists:recetas_cab,id_recetas',
                'detalles.*.cantidad' => 'required|numeric|min:0.01',
                'detalles.*.id_u_medidas' => 'required|exists:u_medidas,id_u_medidas',
            ], [
                'detalles.required' => 'Debe agregar al menos un detalle de merma.',
                'detalles.min' => 'Debe agregar al menos un detalle de merma.',
                'detalles.*.id_areas.required' => 'El área es obligatoria.',
                'detalles.*.id_recetas.required' => 'La receta es obligatoria.',
                'detalles.*.cantidad.required' => 'La cantidad es obligatoria.',
                'detalles.*.cantidad.numeric' => 'La cantidad debe ser un número.',
                'detalles.*.cantidad.min' => 'La cantidad debe ser mayor a cero.',
                'detalles.*.id_u_medidas.required' => 'La unidad de medida es obligatoria.',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
            }

            // Verificar que haya al menos un detalle
            if (empty($request->detalles)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Debe agregar al menos un detalle de merma'
                ], 422);
            }

            // Iniciar transacción
            DB::beginTransaction();

            // Obtener la cabecera de merma
            $mermaCabecera = MermaCabecera::findOrFail($id);
            $now = Carbon::now();
            
            // Actualizar la cabecera con la fecha de actualización
            $mermaCabecera->update([
                'last_update' => $now
            ]);

            // Obtenemos todos los IDs de detalles que se enviaron en la solicitud
            $detalleIds = [];
            foreach ($request->detalles as $detalle) {
                if (isset($detalle['id_mermas_det']) && $detalle['id_mermas_det']) {
                    $detalleIds[] = $detalle['id_mermas_det'];
                }
            }

            // Marcar como eliminados solo los detalles que NO están en la solicitud actual
            MermaDetalle::where('id_mermas_cab', $id)
                ->whereNotIn('id_mermas_det', $detalleIds)
                ->update(['is_deleted' => true]);

            // Procesar cada detalle
            foreach ($request->detalles as $detalle) {
                // Obtener id_productos_api directamente de la receta
                $receta = RecetaCabecera::find($detalle['id_recetas']);
                
                if (!$receta) {
                    throw new \Exception('La receta seleccionada no existe');
                }
                
                // Si el detalle tiene un ID, actualizar en lugar de crear
                if (isset($detalle['id_mermas_det']) && $detalle['id_mermas_det']) {
                    $mermaDetalle = MermaDetalle::find($detalle['id_mermas_det']);
                    if ($mermaDetalle) {
                        $mermaDetalle->update([
                            'id_areas' => $detalle['id_areas'],
                            'id_recetas' => $detalle['id_recetas'],
                            'id_productos_api' => $receta->id_productos_api, // Usar el ID del producto de la receta
                            'cantidad' => $detalle['cantidad'],
                            'id_u_medidas' => $detalle['id_u_medidas'],
                            'obs' => $detalle['obs'] ?? null,
                            'is_deleted' => false,  // Asegurarse de que no esté marcado como eliminado
                            'updated_at' => now()   // Actualizar el timestamp
                        ]);
                        continue;
                    }
                }
                
                // Crear nuevo detalle
                MermaDetalle::create([
                    'id_mermas_cab' => $id,
                    'id_areas' => $detalle['id_areas'],
                    'id_recetas' => $detalle['id_recetas'],
                    'id_productos_api' => $receta->id_productos_api, // Usar el ID del producto de la receta
                    'cantidad' => $detalle['cantidad'],
                    'id_u_medidas' => $detalle['id_u_medidas'],
                    'obs' => $detalle['obs'] ?? null,
                    'is_deleted' => false
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Merma actualizada correctamente',
                'redirect' => route('mermas.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar merma: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar la merma: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // Marcar todos los detalles como eliminados
            MermaDetalle::where('id_mermas_cab', $id)->update(['is_deleted' => true]);
            
            // Marcar la cabecera como eliminada
            $mermaCabecera = MermaCabecera::findOrFail($id);
            $mermaCabecera->update(['is_deleted' => true]);

            DB::commit();
            
            return redirect()->route('mermas.index')
                ->with('success', 'Merma eliminada correctamente');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar merma: ' . $e->getMessage());
            
            return redirect()->route('mermas.index')
                ->with('error', 'Error al eliminar la merma: ' . $e->getMessage());
        }
    }
    
    /**
     * Buscar recetas por área
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Generar PDF de la merma
     * 
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function generatePdf($id)
    {
        $merma = MermaCabecera::with([
            'mermasDetalle' => function ($query) {
                $query->where('is_deleted', false);
            },
            'mermasDetalle.area',
            'mermasDetalle.receta',
            'mermasDetalle.uMedida',
            'usuario',
            'tienda'
        ])->findOrFail($id);

        $pdf = Pdf::loadView('mermas.pdf', compact('merma'));
        return $pdf->stream('merma_' . $id . '.pdf');
    }
    
    /**
     * Generar PDF de múltiples mermas basado en los filtros aplicados
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function generateBulkPdf(Request $request)
    {
        $usuario = Auth::user();
        
        $query = MermaCabecera::with([
            'usuario',
            'tienda',
            'mermasDetalle' => function ($query) {
                $query->where('is_deleted', false);
            },
            'mermasDetalle.area',
            'mermasDetalle.receta',
            'mermasDetalle.uMedida'
        ])
        ->where('is_deleted', false);

        // Filtro por rol de usuario
        if ($usuario->id_roles == 4) { // Rol operador
            $query->where('id_tiendas_api', $usuario->id_tiendas_api);
        }

        // Ordenamiento
        $query->orderBy('fecha_registro', 'desc')
              ->orderBy('hora_registro', 'desc');

        // Determinar el filtro a aplicar
        $filter = $request->filter ?? 'today';
        $today = Carbon::today();

        switch ($filter) {
            case 'today':
                $query->whereDate('fecha_registro', $today);
                $title = 'Mermas de Hoy';
                break;
            case 'yesterday':
                $query->whereDate('fecha_registro', $today->copy()->subDay());
                $title = 'Mermas de Ayer';
                break;
            case 'week':
                $query->whereBetween('fecha_registro', [$today->copy()->subWeek(), $today]);
                $title = 'Mermas de la Última Semana';
                break;
            case 'custom':
                if ($request->has('custom_date')) {
                    $query->whereDate('fecha_registro', $request->custom_date);
                    $title = 'Mermas del ' . Carbon::parse($request->custom_date)->format('d/m/Y');
                } else {
                    $title = 'Todas las Mermas';
                }
                break;
            default:
                $title = 'Todas las Mermas';
        }

        $mermas = $query->limit(100)->get(); // Limitamos a 100 para evitar PDFs muy grandes

        $pdf = Pdf::loadView('mermas.pdf_multiple', compact('mermas', 'title'));
        return $pdf->stream('mermas_lista_' . Carbon::now()->format('Y-m-d') . '.pdf');
    }
}


