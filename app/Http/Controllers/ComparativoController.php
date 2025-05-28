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
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

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
                'areas.nombre as area_nombre',
                'recetas_cab.nombre as receta_nombre',
                'productos.nombre as producto_nombre',
                DB::raw('SUM(produccion_det.cantidad_producida_real) as cantidad_producida'),
                DB::raw('SUM(produccion_det.total_receta) as costo_produccion')
            )
            ->join('produccion_cab', 'produccion_det.id_produccion_cab', '=', 'produccion_cab.id_produccion_cab')
            ->join('recetas_cab', 'produccion_det.id_recetas_cab', '=', 'recetas_cab.id_recetas')
            ->join('areas', 'recetas_cab.id_areas', '=', 'areas.id_areas')
            ->leftJoin('productos', 'recetas_cab.id_productos_api', '=', 'productos.id_item')
            ->whereDate('produccion_cab.fecha', '>=', $fechaInicio)
            ->whereDate('produccion_cab.fecha', '<=', $fechaFin)
            ->where('produccion_det.es_terminado', true)
            ->groupBy('produccion_det.id_recetas_cab', 'areas.nombre', 'recetas_cab.nombre', 'productos.nombre')
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
            'costo_diferencia' => 0
        ];
        
        foreach ($produccionData as $produccion) {
            $idReceta = $produccion->id_recetas_cab;
            $cantidadProducida = $produccion->cantidad_producida;
            
            // Obtener datos de merma si existen
            $cantidadMerma = 0;
            $costoMerma = 0;
            
            if (isset($mermaData[$idReceta])) {
                $cantidadMerma = $mermaData[$idReceta]->cantidad_merma;
                $costoMerma = $mermaData[$idReceta]->costo_merma;
            }
            
            // Calcular vendido (producido - merma)
            $cantidadVendida = $cantidadProducida - $cantidadMerma;
            if ($cantidadVendida < 0) $cantidadVendida = 0;
            
            // Calcular diferencia
            $diferencia = $cantidadProducida - $cantidadVendida - $cantidadMerma;
            
            // Estimar costo unitario aproximado
            $costoUnitario = $cantidadProducida > 0 ? ($produccion->costo_produccion / $cantidadProducida) : 0;
            
            // Estimar ventas (cantidad vendida * costo unitario * margen aproximado)
            $margenAproximado = 2; // Margen estimado de 2x el costo (ajustar según necesidad)
            $ventasAproximadas = $cantidadVendida * $costoUnitario * $margenAproximado;
            
            // Calcular utilidad bruta (ventas - costo producción)
            $utilidadBruta = $ventasAproximadas - ($cantidadVendida * $costoUnitario);
            
            // Costo de la diferencia
            $costoDiferencia = $diferencia * $costoUnitario;
            
            $resultados[] = [
                'fecha' => $fechaInicio == $fechaFin ? Carbon::parse($fechaInicio)->format('d-m-Y') : '',
                'area' => $produccion->area_nombre,
                'receta' => $produccion->receta_nombre,
                'producto' => $produccion->producto_nombre,
                'cantidad_producida' => $cantidadProducida,
                'cantidad_vendida' => $cantidadVendida,
                'cantidad_merma' => $cantidadMerma,
                'diferencia' => $diferencia,
                'utilidad_bruta' => $utilidadBruta,
                'ventas' => $ventasAproximadas,
                'costo_merma' => $costoMerma,
                'costo_diferencia' => $costoDiferencia
            ];
            
            // Sumar a totales
            $totales['produccion'] += $cantidadProducida;
            $totales['venta'] += $cantidadVendida;
            $totales['merma'] += $cantidadMerma;
            $totales['diferencia'] += $diferencia;
            $totales['utilidad_bruta'] += $utilidadBruta;
            $totales['ventas'] += $ventasAproximadas;
            $totales['costo_merma'] += $costoMerma;
            $totales['costo_diferencia'] += $costoDiferencia;
        }
        
        // Ordenar resultados por área y nombre de receta
        usort($resultados, function($a, $b) {
            if ($a['area'] == $b['area']) {
                return $a['receta'] <=> $b['receta'];
            }
            return $a['area'] <=> $b['area'];
        });
        
        return view('produccion.comparativo', compact(
            'resultados', 
            'totales', 
            'fechaInicio', 
            'fechaFin', 
            'areas'
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
        
        // Misma lógica que en el método index para obtener los datos
        // Obtener datos de producción en el rango de fechas
        $produccionData = ProduccionDetalle::select(
                'produccion_det.id_recetas_cab',
                'areas.nombre as area_nombre',
                'recetas_cab.nombre as receta_nombre',
                'productos.nombre as producto_nombre',
                DB::raw('SUM(produccion_det.cantidad_producida_real) as cantidad_producida'),
                DB::raw('SUM(produccion_det.total_receta) as costo_produccion')
            )
            ->join('produccion_cab', 'produccion_det.id_produccion_cab', '=', 'produccion_cab.id_produccion_cab')
            ->join('recetas_cab', 'produccion_det.id_recetas_cab', '=', 'recetas_cab.id_recetas')
            ->join('areas', 'recetas_cab.id_areas', '=', 'areas.id_areas')
            ->leftJoin('productos', 'recetas_cab.id_productos_api', '=', 'productos.id_item')
            ->whereDate('produccion_cab.fecha', '>=', $fechaInicio)
            ->whereDate('produccion_cab.fecha', '<=', $fechaFin)
            ->where('produccion_det.es_terminado', true)
            ->groupBy('produccion_det.id_recetas_cab', 'areas.nombre', 'recetas_cab.nombre', 'productos.nombre')
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
            'costo_diferencia' => 0
        ];
        
        foreach ($produccionData as $produccion) {
            $idReceta = $produccion->id_recetas_cab;
            $cantidadProducida = $produccion->cantidad_producida;
            
            // Obtener datos de merma si existen
            $cantidadMerma = 0;
            $costoMerma = 0;
            
            if (isset($mermaData[$idReceta])) {
                $cantidadMerma = $mermaData[$idReceta]->cantidad_merma;
                $costoMerma = $mermaData[$idReceta]->costo_merma;
            }
            
            // Calcular vendido (producido - merma)
            $cantidadVendida = $cantidadProducida - $cantidadMerma;
            if ($cantidadVendida < 0) $cantidadVendida = 0;
            
            // Calcular diferencia
            $diferencia = $cantidadProducida - $cantidadVendida - $cantidadMerma;
            
            // Estimar costo unitario aproximado
            $costoUnitario = $cantidadProducida > 0 ? ($produccion->costo_produccion / $cantidadProducida) : 0;
            
            // Estimar ventas (cantidad vendida * costo unitario * margen aproximado)
            $margenAproximado = 2; // Margen estimado de 2x el costo (ajustar según necesidad)
            $ventasAproximadas = $cantidadVendida * $costoUnitario * $margenAproximado;
            
            // Calcular utilidad bruta (ventas - costo producción)
            $utilidadBruta = $ventasAproximadas - ($cantidadVendida * $costoUnitario);
            
            // Costo de la diferencia
            $costoDiferencia = $diferencia * $costoUnitario;
            
            $resultados[] = [
                'fecha' => $fechaInicio == $fechaFin ? Carbon::parse($fechaInicio)->format('d-m-Y') : '',
                'area' => $produccion->area_nombre,
                'receta' => $produccion->receta_nombre,
                'producto' => $produccion->producto_nombre,
                'cantidad_producida' => $cantidadProducida,
                'cantidad_vendida' => $cantidadVendida,
                'cantidad_merma' => $cantidadMerma,
                'diferencia' => $diferencia,
                'utilidad_bruta' => $utilidadBruta,
                'ventas' => $ventasAproximadas,
                'costo_merma' => $costoMerma,
                'costo_diferencia' => $costoDiferencia
            ];
            
            // Sumar a totales
            $totales['produccion'] += $cantidadProducida;
            $totales['venta'] += $cantidadVendida;
            $totales['merma'] += $cantidadMerma;
            $totales['diferencia'] += $diferencia;
            $totales['utilidad_bruta'] += $utilidadBruta;
            $totales['ventas'] += $ventasAproximadas;
            $totales['costo_merma'] += $costoMerma;
            $totales['costo_diferencia'] += $costoDiferencia;
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
            'subtitle'
        ));
        
        return $pdf->stream('comparativo_produccion_mermas_' . Carbon::now()->format('Ymd') . '.pdf');
    }
}


