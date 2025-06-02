<?php

namespace App\Http\Controllers;

use App\Models\ProduccionCabecera;
use App\Models\ProduccionDetalle;
use App\Models\MermaCabecera;
use App\Models\MermaDetalle;
use App\Models\RecetaCabecera;
use App\Models\Area;
use App\Models\Producto;
use App\Models\UMedida;
use App\Models\Tienda;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\TokenHelper;

class ComparativoController extends Controller
{
    /**
     * Constructor del controlador
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * Obtiene un token válido para la API de ventas
     * Si el token está expirado o no existe, genera uno nuevo
     *
     * @return string Token de autorización
     */
   
    
    /**
     * Muestra la vista comparativa entre producción y mermas
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Obtener fechas de filtro
        $fechaInicio = $request->input('fecha_inicio', Carbon::today()->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', Carbon::today()->format('Y-m-d'));
        
        // Validación de fechas
        if (Carbon::parse($fechaInicio)->greaterThan(Carbon::parse($fechaFin))) {
            $fechaTemp = $fechaInicio;
            $fechaInicio = $fechaFin;
            $fechaFin = $fechaTemp;
        }
        
        $fechaInicioCarbon = Carbon::parse($fechaInicio)->startOfDay();
        $fechaFinCarbon = Carbon::parse($fechaFin)->endOfDay();
        
        // Obtener áreas para el filtro
        $areas = Area::where('status', true)
                     ->where('is_deleted', false)
                     ->orderBy('nombre')
                     ->get();
        
        // Obtener datos de producción en el rango de fechas
        $produccionData = ProduccionDetalle::select(
                'produccion_det.id_recetas_cab',
                'produccion_det.id_u_medidas_prodcc',
                'areas.nombre as area_nombre',
                'areas.id_areas',
                'recetas_cab.nombre as receta_nombre',
                'recetas_cab.id_productos_api',
                'recetas_cab.costo_receta',
                'productos.nombre as producto_nombre',
                'productos.id_item',
                DB::raw('DATE(produccion_det.updated_at) as fecha_produccion'),
                DB::raw('SUM(produccion_det.cantidad_producida_real) as cantidad_producida'),
                DB::raw('SUM(produccion_det.cantidad_esperada) as cantidad_esperada'),
                DB::raw('SUM(produccion_det.total_receta) as costo_produccion')
            )
            ->join('produccion_cab', 'produccion_det.id_produccion_cab', '=', 'produccion_cab.id_produccion_cab')
            ->join('recetas_cab', 'produccion_det.id_recetas_cab', '=', 'recetas_cab.id_recetas')
            ->join('areas', 'recetas_cab.id_areas', '=', 'areas.id_areas')
            ->leftJoin('productos', 'recetas_cab.id_productos_api', '=', 'productos.id_item')
            ->whereDate('produccion_det.updated_at', '>=', $fechaInicio)
            ->whereDate('produccion_det.updated_at', '<=', $fechaFin)
            ->where('produccion_det.es_terminado', true)
            ->groupBy('produccion_det.id_recetas_cab', 'produccion_det.id_u_medidas_prodcc', 'areas.nombre', 'areas.id_areas', 'recetas_cab.nombre', 'recetas_cab.id_productos_api', 'recetas_cab.costo_receta', 'productos.nombre', 'productos.id_item', 'fecha_produccion')
            ->get();
            
        // Obtener datos de mermas en el rango de fechas
        $mermaData = MermaDetalle::select(
                'mermas_det.id_recetas',
                DB::raw('SUM(mermas_det.cantidad) as cantidad_merma'),
                DB::raw('AVG(mermas_det.costo) as costo_unitario'),
                // Cálculo del Costo Merma:
                // Se obtiene inicialmente de la base de datos en la consulta SQL. Esto suma los valores del campo "total" de los registros de mermas.
                DB::raw('SUM(mermas_det.total) as costo_merma')
            )
            ->join('mermas_cab', 'mermas_det.id_mermas_cab', '=', 'mermas_cab.id_mermas_cab')
            ->whereDate('mermas_cab.fecha_registro', '>=', $fechaInicio)
            ->whereDate('mermas_cab.fecha_registro', '<=', $fechaFin)
            ->where('mermas_det.is_deleted', false)
            ->where('mermas_cab.is_deleted', false)
            ->groupBy('mermas_det.id_recetas')
            ->get()
            ->keyBy('id_recetas');
            
        // Obtener tiendas activas para consultar la API de ventas
        $tiendas = Tienda::activos()->get();
            
        // Combinar datos para la vista
        $resultados = [];
        $totales = [
            'produccion' => 0,
            'venta' => 0,
            'merma' => 0,
            'diferencia' => 0,
            'utilidad_bruta' => 0,
            'ventas' => 0,
            'costo_merma' => 0,
            'costo_diferencia' => 0,
            'costo_produccion' => 0  // Add this line
        ];
        
        foreach ($produccionData as $produccion) {
            $idReceta = $produccion->id_recetas_cab;
            $idItem = $produccion->id_productos_api;
            $cantidadProducida = $produccion->cantidad_producida;
            $fechaProduccion = $produccion->fecha_produccion;
            
            // Obtener datos de merma si existen
            $cantidadMerma = 0;
            $costoMerma = 0;
            $costoUnitario = 0;
            
            // Obtenemos el costo_receta directamente de la tabla recetas_cab
            $costoReceta = $produccion->costo_receta;
            
            if (isset($mermaData[$idReceta])) {
                $cantidadMerma = $mermaData[$idReceta]->cantidad_merma;
                // Cálculo del costo de merma usando costo_receta de recetas_cab
                $costoMerma = $cantidadMerma * $costoReceta;
                $costoUnitario = $mermaData[$idReceta]->costo_unitario;
            } else {
                // Si no hay registro en mermas, calculamos el costo unitario desde producción
                $costoUnitario = $cantidadProducida > 0 ? ($produccion->costo_produccion / $cantidadProducida) : 0;
            }
            
            // Consultar ventas a través de la API para cada tienda activa
            $cantidadVendida = 0;
            $ventasTotal = 0;

            if ($idItem) {
                foreach ($tiendas as $tienda) {
                    $codigoTienda = $tienda->codigo_tienda;
                    try {
                        // Usar TokenHelper para consultar ventas por tienda
$resultadoVenta = TokenHelper::consultarVentasPorTienda(
    $codigoTienda,
    $idItem,
    $fechaInicio,
    $fechaFin
);

// Sumar la cantidad vendida en esta tienda al total
$cantidadVendida += $resultadoVenta['cantidad_vendida'];

// Sumar el costo total de ventas (usando el costo de la API)
$ventasTotal += $resultadoVenta['costo_total'];

// Registrar la consulta para depuración
Log::debug("Consulta de ventas para tienda {$codigoTienda}, producto {$idItem}: ", [
    'cantidad' => $resultadoVenta['cantidad_vendida'],
    'costo' => $resultadoVenta['costo_total']
]);
                    } catch (\Exception $e) {
                        // Manejar error de API silenciosamente
                        Log::error('Error al consultar API de ventas: ' . $e->getMessage());
                    }
                }
            }
            
            // Calcular diferencia entre producción, ventas y mermas
            $diferencia = $cantidadProducida - $cantidadVendida - $cantidadMerma;
            
            // Calcular valores monetarios
            // $ventasTotal = $cantidadVendida * $costoUnitario;
            
            // Usar directamente el costo de producción calculado en la consulta SQL (suma de total_receta)
            $costoProduccion = $produccion->costo_produccion;
            
            // Actualizar el cálculo de utilidad bruta para que sea ventas - costo produccion
            $utilidadBruta = $ventasTotal - $costoProduccion;

            // $diferencia es la cantidad producida menos cantidad vendida menos cantidad de merma
            // Calculamos el costo de la diferencia usando costo_receta de recetas_cab
            $costoDiferencia = $diferencia * $costoReceta;
            
            $resultados[] = [
                'fecha' => $fechaInicio == $fechaFin ? Carbon::parse($fechaInicio)->format('d-m-Y') : '',
                'area' => $produccion->area_nombre,
                'receta' => $produccion->receta_nombre,
                'producto' => $produccion->producto_nombre,
                'cantidad_producida' => $cantidadProducida,
                'cantidad_esperada' => $produccion->cantidad_esperada,
                'cantidad_vendida' => $cantidadVendida,
                'cantidad_merma' => $cantidadMerma,
                'diferencia' => $diferencia,
                'costo_produccion' => $costoProduccion,
                'utilidad_bruta' => $utilidadBruta,
                'ventas' => $ventasTotal,
                'costo_merma' => $costoMerma,
                'costo_diferencia' => $costoDiferencia
            ];
            
            // Sumar a totales
            $totales['produccion'] += $cantidadProducida;
            $totales['venta'] += $cantidadVendida;
            $totales['merma'] += $cantidadMerma;
            $totales['diferencia'] += $diferencia;
            $totales['utilidad_bruta'] += $utilidadBruta;
            $totales['ventas'] += $ventasTotal;
            $totales['costo_merma'] += $costoMerma;
            $totales['costo_diferencia'] += $costoDiferencia;
            $totales['costo_produccion'] += $costoProduccion;
        }
        
        // Ordenar resultados por área y nombre de receta
        usort($resultados, function($a, $b) {
            if ($a['area'] == $b['area']) {
                return $a['receta'] <=> $b['receta'];
            }
            return $a['area'] <=> $b['area'];
        });
        
        // Obtener las unidades de medida para cada receta
        $unidadesProduccion = [];
        $idsRecetas = array_column($resultados, 'receta');
        
        // Obtener las unidades de medida de producción para todas las recetas encontradas
        $unidadesData = ProduccionDetalle::select('id_recetas_cab', 'id_u_medidas_prodcc')
            ->whereIn('id_recetas_cab', array_column($produccionData->toArray(), 'id_recetas_cab'))
            ->whereNotNull('id_u_medidas_prodcc')
            ->groupBy('id_recetas_cab', 'id_u_medidas_prodcc')
            ->get();
            
        // Crear un array asociativo de receta => id_u_medidas_prodcc
        foreach ($unidadesData as $unidad) {
            $unidadesProduccion[$unidad->id_recetas_cab] = $unidad->id_u_medidas_prodcc;
        }
        
        // Agregar las unidades de medida a cada resultado
        foreach ($resultados as &$resultado) {
            $idReceta = array_search($resultado['receta'], array_column($produccionData->toArray(), 'receta_nombre'));
            if ($idReceta !== false) {
                $idRecetaCab = $produccionData[$idReceta]->id_recetas_cab;
                $resultado['id_u_medidas_prodcc'] = $unidadesProduccion[$idRecetaCab] ?? null;
            }
        }
        
        // Cargar las unidades de medida directamente para asegurar consistencia
        $unidadesMedida = UMedida::pluck('nombre', 'id_u_medidas');
        
        // Para depuración
        $idKilogramo = array_search('Kilogramos', $unidadesMedida->toArray());

        return view('produccion.comparativo', compact(
            'resultados', 
            'totales', 
            'fechaInicio', 
            'fechaFin', 
            'areas',
            'unidadesMedida'
        ));
    }
    
    /**
     * Generar PDF del comparativo
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function generarPdf(Request $request)
    {
        // Obtener fechas de filtro
        $fechaInicio = $request->input('fecha_inicio', Carbon::today()->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', Carbon::today()->format('Y-m-d'));
        
        // Validación de fechas
        if (Carbon::parse($fechaInicio)->greaterThan(Carbon::parse($fechaFin))) {
            $fechaTemp = $fechaInicio;
            $fechaInicio = $fechaFin;
            $fechaFin = $fechaTemp;
        }
        
        // Obtener todas las unidades de medida para mostrarlas en el reporte
        $unidadesMedida = UMedida::pluck('nombre', 'id_u_medidas')->toArray();
        
        // Obtener tiendas activas para consultar la API de ventas
        $tiendas = Tienda::activos()->get();
        
        // Misma lógica que en el método index para obtener los datos
        // Obtener datos de producción en el rango de fechas
        $produccionData = ProduccionDetalle::select(
            'produccion_det.id_recetas_cab',
            'areas.nombre as area_nombre',
            'areas.id_areas',
            'recetas_cab.nombre as receta_nombre',
            'recetas_cab.id_productos_api',
            'recetas_cab.costo_receta',  
            'productos.nombre as producto_nombre',
            DB::raw('DATE(produccion_cab.fecha) as fecha_produccion'),
            'produccion_det.id_u_medidas_prodcc',
            DB::raw('SUM(produccion_det.cantidad_producida_real) as cantidad_producida'),
            DB::raw('SUM(produccion_det.cantidad_esperada) as cantidad_esperada'),  
            DB::raw('SUM(produccion_det.total_receta) as costo_produccion')
        )
            ->join('produccion_cab', 'produccion_det.id_produccion_cab', '=', 'produccion_cab.id_produccion_cab')
            ->join('recetas_cab', 'produccion_det.id_recetas_cab', '=', 'recetas_cab.id_recetas')
            ->join('areas', 'recetas_cab.id_areas', '=', 'areas.id_areas')
            ->leftJoin('productos', 'recetas_cab.id_productos_api', '=', 'productos.id_item')
            ->whereDate('produccion_cab.fecha', '>=', $fechaInicio)
            ->whereDate('produccion_cab.fecha', '<=', $fechaFin)
            ->where('produccion_det.es_terminado', true)
            ->groupBy('produccion_det.id_recetas_cab', 'areas.nombre', 'areas.id_areas', 'recetas_cab.nombre', 'recetas_cab.id_productos_api', 'recetas_cab.costo_receta', 'productos.nombre', 'fecha_produccion', 'produccion_det.id_u_medidas_prodcc')
            ->get();
            
        // Obtener datos de mermas en el rango de fechas
        $mermaData = MermaDetalle::select(
                'mermas_det.id_recetas',
                DB::raw('SUM(mermas_det.cantidad) as cantidad_merma'),
                DB::raw('SUM(mermas_det.total) as costo_merma')
            )
            ->join('mermas_cab', 'mermas_det.id_mermas_cab', '=', 'mermas_cab.id_mermas_cab')
            ->whereDate('mermas_cab.fecha_registro', '>=', $fechaInicio)
            ->whereDate('mermas_cab.fecha_registro', '<=', $fechaFin)
            ->where('mermas_det.is_deleted', false)
            ->where('mermas_cab.is_deleted', false)
            ->groupBy('mermas_det.id_recetas')
            ->get()
            ->keyBy('id_recetas');
        // Combinar datos para la vista
        $resultados = [];
        $totales = [
            'produccion' => 0,
            'venta' => 0,
            'merma' => 0,
            'diferencia' => 0,
            'utilidad_bruta' => 0,
            'ventas' => 0,
            'costo_merma' => 0,
            'costo_diferencia' => 0,
            'costo_produccion' => 0
        ];
        
        foreach ($produccionData as $produccion) {
            $idReceta = $produccion->id_recetas_cab;
            $idItem = $produccion->id_productos_api; // Usar el campo id_productos_api que viene de recetas_cab
            $cantidadProducida = $produccion->cantidad_producida;
            $fechaProduccion = $produccion->fecha_produccion;
            
            // Obtener datos de merma si existen
            $cantidadMerma = 0;
            $costoMerma = 0;
            $costoUnitario = 0;
            // Obtenemos el costo_receta directamente de la tabla recetas_cab
            $costoReceta = $produccion->costo_receta;
            
            if (isset($mermaData[$idReceta])) {
                $cantidadMerma = $mermaData[$idReceta]->cantidad_merma;
                $costoMerma = $cantidadMerma * $costoReceta;
                $costoUnitario = $mermaData[$idReceta]->costo_unitario;
            } else {
                // Si no hay registro en mermas, calculamos el costo unitario desde producción
                $costoUnitario = $cantidadProducida > 0 ? ($produccion->costo_produccion / $cantidadProducida) : 0;
            }
            
            // Consultar ventas a través de la API para cada tienda activa
            $cantidadVendida = 0;
            $ventasTotal = 0;

            if ($idItem) {
                foreach ($tiendas as $tienda) {
                    $codigoTienda = $tienda->codigo_tienda;
                    try {
                        // Usar TokenHelper para consultar ventas por tienda
$resultadoVenta = TokenHelper::consultarVentasPorTienda(
    $codigoTienda,
    $idItem,
    $fechaInicio,
    $fechaFin
);

// Sumar la cantidad vendida en esta tienda al total
$cantidadVendida += $resultadoVenta['cantidad_vendida'];

// Sumar el costo total de ventas (usando el costo de la API)
$ventasTotal += $resultadoVenta['costo_total'];

// Registrar la consulta para depuración
Log::debug("Consulta de ventas para tienda {$codigoTienda}, producto {$idItem}: ", [
    'cantidad' => $resultadoVenta['cantidad_vendida'],
    'costo' => $resultadoVenta['costo_total']
]);
                    } catch (\Exception $e) {
                        // Manejar error de API silenciosamente
                        Log::error('Error al consultar API de ventas: ' . $e->getMessage());
                    }
                }
            }
            
            // Calcular diferencia entre producción, ventas y mermas
            $diferencia = $cantidadProducida - $cantidadVendida - $cantidadMerma;
            
            // Calcular valores monetarios
            // $ventasTotal = $cantidadVendida * $costoUnitario;
            $costoProduccion = $produccion->costo_produccion;
$utilidadBruta = $ventasTotal - $costoProduccion;
$costoDiferencia = $diferencia * $costoReceta;
            
            $resultados[] = [
                'fecha' => Carbon::parse($fechaProduccion)->format('d-m-Y'),
                'area' => $produccion->area_nombre,
                'id_areas' => $produccion->id_areas,
                'receta' => $produccion->receta_nombre,
                'producto' => $produccion->producto_nombre,
                'id_receta' => $idReceta,
                'id_item' => $idItem,
                'cantidad_producida' => $cantidadProducida,
                'id_u_medidas_prodcc' => $produccion->id_u_medidas_prodcc,
                'cantidad_vendida' => $cantidadVendida,
                'cantidad_merma' => $cantidadMerma,
                'diferencia' => $diferencia,
                'costo_unitario' => $costoUnitario,
                'utilidad_bruta' => $utilidadBruta,
                'ventas' => $ventasTotal,
                'costo_merma' => $costoMerma,
                'costo_diferencia' => $costoDiferencia,
                'costo_produccion' => $costoProduccion
            ];
            
            // Sumar a totales
            $totales['produccion'] += $cantidadProducida;
            $totales['venta'] += $cantidadVendida;
            $totales['merma'] += $cantidadMerma;
            $totales['diferencia'] += $diferencia;
            $totales['utilidad_bruta'] += $utilidadBruta;
            $totales['ventas'] += $ventasTotal;
            $totales['costo_merma'] += $costoMerma;
            $totales['costo_diferencia'] += $costoDiferencia;
            $totales['costo_produccion'] += $costoProduccion;
        }
        
        // Ordenar resultados por área y nombre de receta
        usort($resultados, function($a, $b) {
            if ($a['area'] == $b['area']) {
                return $a['receta'] <=> $b['receta'];
            }
            return $a['area'] <=> $b['area'];
        });
        
        $title = "Comparativo de Producción y Mermas";
        $subtitle = "Del " . Carbon::parse($fechaInicio)->format('d/m/Y');
        if ($fechaInicio != $fechaFin) {
            $subtitle .= " al " . Carbon::parse($fechaFin)->format('d/m/Y');
        }
        
        $pdf = Pdf::loadView('produccion.comparativo_pdf', compact(
            'resultados', 
            'totales',
            'title',
            'subtitle',
            'unidadesMedida'
        ));
        
        return $pdf->stream('comparativo_produccion_mermas_' . Carbon::now()->format('Ymd') . '.pdf');
    }
    
    /**
     * Exportar a Excel el comparativo de producción y mermas
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportarExcel(Request $request)
    {
        // Obtener fechas de filtro
        $fechaInicio = $request->input('fecha_inicio', Carbon::today()->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', Carbon::today()->format('Y-m-d'));
        
        // Validación de fechas
        if (Carbon::parse($fechaInicio)->greaterThan(Carbon::parse($fechaFin))) {
            $fechaTemp = $fechaInicio;
            $fechaInicio = $fechaFin;
            $fechaFin = $fechaTemp;
        }
        
        // Obtener todas las unidades de medida para mostrarlas en el reporte
        $unidadesMedida = UMedida::pluck('nombre', 'id_u_medidas')->toArray();
        
        // Obtener tiendas activas para consultar la API de ventas
        $tiendas = Tienda::activos()->get();
        
        // Misma lógica que en el método generarPdf para obtener los datos
        $produccionData = ProduccionDetalle::select(
            'produccion_det.id_recetas_cab',
            'areas.nombre as area_nombre',
            'areas.id_areas',
            'recetas_cab.nombre as receta_nombre',
            'recetas_cab.id_productos_api',
            'recetas_cab.costo_receta',  
            'productos.nombre as producto_nombre',
            DB::raw('DATE(produccion_cab.fecha) as fecha_produccion'),
            'produccion_det.id_u_medidas_prodcc',
            DB::raw('SUM(produccion_det.cantidad_producida_real) as cantidad_producida'),
            DB::raw('SUM(produccion_det.cantidad_esperada) as cantidad_esperada'),  
            DB::raw('SUM(produccion_det.total_receta) as costo_produccion')
        )
            ->join('produccion_cab', 'produccion_det.id_produccion_cab', '=', 'produccion_cab.id_produccion_cab')
            ->join('recetas_cab', 'produccion_det.id_recetas_cab', '=', 'recetas_cab.id_recetas')
            ->join('areas', 'recetas_cab.id_areas', '=', 'areas.id_areas')
            ->leftJoin('productos', 'recetas_cab.id_productos_api', '=', 'productos.id_item')
            ->whereDate('produccion_cab.fecha', '>=', $fechaInicio)
            ->whereDate('produccion_cab.fecha', '<=', $fechaFin)
            ->where('produccion_det.es_terminado', true)
            ->groupBy('produccion_det.id_recetas_cab', 'areas.nombre', 'areas.id_areas', 'recetas_cab.nombre', 'recetas_cab.id_productos_api', 'recetas_cab.costo_receta', 'productos.nombre', 'fecha_produccion', 'produccion_det.id_u_medidas_prodcc')
            ->get();
            
        // Obtener datos de mermas en el rango de fechas
        $mermaData = MermaDetalle::select(
                'mermas_det.id_recetas',
                DB::raw('SUM(mermas_det.cantidad) as cantidad_merma'),
                DB::raw('SUM(mermas_det.total) as costo_merma')
            )
            ->join('mermas_cab', 'mermas_det.id_mermas_cab', '=', 'mermas_cab.id_mermas_cab')
            ->whereDate('mermas_cab.fecha_registro', '>=', $fechaInicio)
            ->whereDate('mermas_cab.fecha_registro', '<=', $fechaFin)
            ->where('mermas_det.is_deleted', false)
            ->where('mermas_cab.is_deleted', false)
            ->groupBy('mermas_det.id_recetas')
            ->get()
            ->keyBy('id_recetas');
            
        // Combinar datos para la vista
        $resultados = [];
        $totales = [
            'produccion' => 0,
            'venta' => 0,
            'merma' => 0,
            'diferencia' => 0,
            'utilidad_bruta' => 0,
            'ventas' => 0,
            'costo_merma' => 0,
            'costo_diferencia' => 0,
            'costo_produccion' => 0
        ];
        
        // Procesar los datos igual que en generarPdf
        foreach ($produccionData as $produccion) {
            $idReceta = $produccion->id_recetas_cab;
            $idItem = $produccion->id_productos_api;
            $cantidadProducida = $produccion->cantidad_producida;
            $fechaProduccion = $produccion->fecha_produccion;
            
            // Obtener datos de merma si existen
            $cantidadMerma = 0;
            $costoMerma = 0;
            $costoUnitario = 0;

            // Obtenemos el costo_receta directamente de la tabla recetas_cab
            $costoReceta = $produccion->costo_receta;
            
            if (isset($mermaData[$idReceta])) {
                $cantidadMerma = $mermaData[$idReceta]->cantidad_merma;
                $costoMerma = $cantidadMerma * $costoReceta;
                $costoUnitario = $mermaData[$idReceta]->costo_unitario;
            } else {
                // Si no hay registro en mermas, calculamos el costo unitario desde producción
                $costoUnitario = $cantidadProducida > 0 ? ($produccion->costo_produccion / $cantidadProducida) : 0;
            }
            
            // Consultar ventas a través de la API para cada tienda activa
            $cantidadVendida = 0;
            $ventasTotal = 0;

            if ($idItem) {
                foreach ($tiendas as $tienda) {
                    $codigoTienda = $tienda->codigo_tienda;
                    try {
                        // Usar TokenHelper para consultar ventas por tienda
                        $resultadoVenta = TokenHelper::consultarVentasPorTienda(
                            $codigoTienda,
                            $idItem,
                            $fechaInicio,
                            $fechaFin
                        );
                        
                        // Sumar la cantidad vendida en esta tienda al total
                        $cantidadVendida += $resultadoVenta['cantidad_vendida'];
                        
                        // Sumar el costo total de ventas (usando el costo proporcionado por la API)
                        $ventasTotal += $resultadoVenta['costo_total'];
                    } catch (\Exception $e) {
                        // En caso de error, solo registramos y continuamos con las siguientes tiendas
                        Log::error('Error al consultar ventas para la tienda ' . $codigoTienda . ': ' . $e->getMessage());
                    }
                }
            }
            
            // Calcular diferencia entre producción, ventas y mermas
            $diferencia = $cantidadProducida - $cantidadVendida - $cantidadMerma;
            
            // Calcular valores monetarios
            $costoProduccion = $produccion->costo_produccion;
            $utilidadBruta = $ventasTotal - $costoProduccion; // Utilidad bruta es ventas menos costo de producción
            $costoDiferencia = $diferencia * $costoReceta;
            
            $resultados[] = [
                'fecha' => $fechaProduccion ? Carbon::parse($fechaProduccion)->format('d-m-Y') : '',
                'area' => $produccion->area_nombre,
                'receta' => $produccion->receta_nombre,
                'producto' => $produccion->producto_nombre,
                'cantidad_producida' => $cantidadProducida,
                'id_u_medidas_prodcc' => $produccion->id_u_medidas_prodcc,
                'cantidad_vendida' => $cantidadVendida,
                'cantidad_merma' => $cantidadMerma,
                'diferencia' => $diferencia,
                'costo_unitario' => $costoUnitario,
                'utilidad_bruta' => $utilidadBruta,
                'ventas' => $ventasTotal,
                'costo_merma' => $costoMerma,
                'costo_diferencia' => $costoDiferencia,
                'costo_produccion' => $costoProduccion
            ];
            
            // Sumar a totales
            $totales['produccion'] += $cantidadProducida;
            $totales['venta'] += $cantidadVendida;
            $totales['merma'] += $cantidadMerma;
            $totales['diferencia'] += $diferencia;
            $totales['utilidad_bruta'] += $utilidadBruta;
            $totales['ventas'] += $ventasTotal;
            $totales['costo_merma'] += $costoMerma;
            $totales['costo_diferencia'] += $costoDiferencia;
            $totales['costo_produccion'] += $costoProduccion;
        }
        
        // Ordenar resultados por área y nombre de receta
        usort($resultados, function($a, $b) {
            if ($a['area'] == $b['area']) {
                return $a['receta'] <=> $b['receta'];
            }
            return $a['area'] <=> $b['area'];
        });
        
        // Crear una instancia de Spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Comparativo');
        
        // Información del reporte
        $sheet->setCellValue('A1', 'COMPARATIVO DE PRODUCCIÓN Y MERMAS');
        $periodoTexto = $fechaInicio == $fechaFin ?
            'DEL ' . Carbon::parse($fechaInicio)->format('d/m/Y') :
            'DEL ' . Carbon::parse($fechaInicio)->format('d/m/Y') . ' AL ' . Carbon::parse($fechaFin)->format('d/m/Y');
        $sheet->setCellValue('A2', $periodoTexto);
        
        // Aplicar formato al título
        $sheet->getStyle('A1:N1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2:N2')->getFont()->setBold(true);
        
        // Encabezados de la tabla
        $encabezados = [
            'FECHA', 'ÁREA', 'RECETA', 'PRODUCTO', 
            'CANT. PRODUCIDA', 'UM', 'CANT. VENDIDA', 'CANT. MERMA', 'DIFERENCIA', 
            'COSTO PRODUCCIÓN', 'UTILIDAD BRUTA', 'VENTAS', 'COSTO MERMA', 'COSTO DIF.'
        ];
        
        $columna = 'A';
        $fila = 4;
        
        // Establecer encabezados
        foreach ($encabezados as $encabezado) {
            $sheet->setCellValue($columna . $fila, $encabezado);
            $columna++;
        }
        
        // Aplicar formato a los encabezados
        $sheet->getStyle('A4:N4')->getFont()->setBold(true);
        $sheet->getStyle('A4:N4')->getFill()
              ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
              ->getStartColor()->setRGB('D9EAD3');
        
        // Establecer ancho de columnas
        $sheet->getColumnDimension('A')->setWidth(12); // Fecha
        $sheet->getColumnDimension('B')->setWidth(20); // Área
        $sheet->getColumnDimension('C')->setWidth(25); // Receta
        $sheet->getColumnDimension('D')->setWidth(25); // Producto
        $sheet->getColumnDimension('E')->setWidth(15); // Cant. Producida
        $sheet->getColumnDimension('F')->setWidth(10); // UM
        $sheet->getColumnDimension('G')->setWidth(15); // Cant. Vendida
        $sheet->getColumnDimension('H')->setWidth(15); // Cant. Merma
        $sheet->getColumnDimension('I')->setWidth(15); // Diferencia
        $sheet->getColumnDimension('J')->setWidth(15); // Costo Producción
        $sheet->getColumnDimension('K')->setWidth(15); // Utilidad Bruta
        $sheet->getColumnDimension('L')->setWidth(15); // Ventas
        $sheet->getColumnDimension('M')->setWidth(15); // Costo Merma
        $sheet->getColumnDimension('N')->setWidth(15); // Costo Diferencia
        
        // Llenar datos
        $fila = 5;
        foreach ($resultados as $resultado) {
            $sheet->setCellValue('A' . $fila, $resultado['fecha']);
            $sheet->setCellValue('B' . $fila, $resultado['area']);
            $sheet->setCellValue('C' . $fila, $resultado['receta']);
            $sheet->setCellValue('D' . $fila, $resultado['producto']);
            $sheet->setCellValue('E' . $fila, $resultado['cantidad_producida']);
            // Agregar unidad de medida
            $um = isset($resultado['id_u_medidas_prodcc']) && isset($unidadesMedida[$resultado['id_u_medidas_prodcc']]) 
                ? $unidadesMedida[$resultado['id_u_medidas_prodcc']] 
                : '-';
            $sheet->setCellValue('F' . $fila, $um);
            $sheet->setCellValue('G' . $fila, $resultado['cantidad_vendida']);
            $sheet->setCellValue('H' . $fila, $resultado['cantidad_merma']);
            $sheet->setCellValue('I' . $fila, $resultado['diferencia']);
            $sheet->setCellValue('J' . $fila, $resultado['costo_produccion']);
            $sheet->setCellValue('K' . $fila, $resultado['utilidad_bruta']);
            $sheet->setCellValue('L' . $fila, $resultado['ventas']);
            $sheet->setCellValue('M' . $fila, $resultado['costo_merma']);
            $sheet->setCellValue('N' . $fila, $resultado['costo_diferencia']);
            $fila++;
        }
        
        // Agregar fila de totales
        $sheet->setCellValue('A' . $fila, 'TOTALES');
        $sheet->getStyle('A' . $fila)->getFont()->setBold(true);
        $sheet->setCellValue('E' . $fila, number_format($totales['produccion'], 2));
        $sheet->setCellValue('F' . $fila, ''); // Unidad de medida no se suma
        $sheet->setCellValue('G' . $fila, number_format($totales['venta'], 2));
        $sheet->setCellValue('H' . $fila, number_format($totales['merma'], 2));
        $sheet->setCellValue('I' . $fila, number_format($totales['diferencia'], 2));
        $sheet->setCellValue('J' . $fila, number_format($totales['costo_produccion'], 2));
        $sheet->setCellValue('K' . $fila, number_format($totales['utilidad_bruta'], 2));
        $sheet->setCellValue('L' . $fila, number_format($totales['ventas'], 2));
        $sheet->setCellValue('M' . $fila, number_format($totales['costo_merma'], 2));
        $sheet->setCellValue('N' . $fila, number_format($totales['costo_diferencia'], 2));
        
        // Aplicar formato a la fila de totales
        $sheet->getStyle('A' . $fila . ':N' . $fila)->getFont()->setBold(true);
        $sheet->getStyle('A' . $fila . ':N' . $fila)->getFill()
              ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
              ->getStartColor()->setRGB('E6E6E6');
        
        // Aplicar formato de número a las columnas numéricas
        $rangoNumeros = 'E5:N' . ($fila);
        $sheet->getStyle($rangoNumeros)->getNumberFormat()->setFormatCode('#,##0.00');
        
        // No aplicamos formato de número a la columna F (unidad de medida)
        $sheet->getStyle('F5:F' . ($fila))->getNumberFormat()->setFormatCode('@');
        
        // Ajustes finales y creación del archivo Excel
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $nombreArchivo = 'comparativo_produccion_mermas_' . Carbon::now()->format('Ymd') . '.xlsx';
        $rutaTemporal = storage_path('app/public/' . $nombreArchivo);
        
        // Guardar el archivo
        $writer->save($rutaTemporal);
        
        // Descargar el archivo y luego eliminarlo
        return response()->download($rutaTemporal, $nombreArchivo)->deleteFileAfterSend(true);
    }
}



