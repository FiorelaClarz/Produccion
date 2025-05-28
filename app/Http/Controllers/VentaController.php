<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VentaController extends Controller
{
    /**
     * Constructor de la clase
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * Muestra la vista de consulta de ventas por tienda
     * 
     * @return \Illuminate\View\View
     */
    public function consulta()
    {
        // Datos de ejemplo que sabemos que funcionan con la API
        $tiendas = [
            'T01' => 'Tienda 01',
            'T02' => 'Tienda 02',
            'T03' => 'Tienda 03',
            'T04' => 'Tienda 04',
            'T05' => 'Tienda 05',
            'T06' => 'Tienda 06',
            'T07' => 'Tienda 07',
            'T08' => 'Tienda 08',
            'T09' => 'Tienda 09',
            'T10' => 'Tienda 10'
        ];
        
        $ejemploProductos = [
            '250704' => 'AJINOMOTO DELI ARROZ 12G X 5UND', // Ejemplo de la documentación
            '117152' => 'Producto de ejemplo',
            '123456' => 'Otro producto ejemplo'
        ];
        
        return view('ventas.consulta', compact('tiendas', 'ejemploProductos'));
    }
    
    /**
     * Muestra la vista de consulta avanzada de ventas (estilo Postman)
     * 
     * @return \Illuminate\View\View
     */
    public function consultaAvanzada()
    {
        return view('ventas.consulta_avanzada');
    }
    
    /**
     * Actúa como proxy para la API de ventas para evitar problemas de CORS
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function proxy(Request $request)
    {
        try {
            // Obtener parámetros de la solicitud
            $accion = $request->input('accion');
            $storeCode = $request->input('store_code');
            $idItem = $request->input('id_item');
            $fecha1 = $request->input('fecha1');
            $fecha2 = $request->input('fecha2');
            
            // Ajustar las fechas al rango que sabemos que tiene datos (en caso de que sea necesario)
            if (empty($fecha1) || empty($fecha2)) {
                $fecha1 = '2025-01-28';
                $fecha2 = '2025-03-28';
            }
            
            // Token de autorización
            $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJ2b3ZlcmtvayIsImlhdCI6MTc0ODQ1NDEwMCwiZXhwIjoxNzQ4NDk3MzAwLCJ1c2VySUQiOiJ2b3Zlcmtva19yZWQlOTk0MTQifQ.r5Nt6mUrCUIjZie7D5h8LROQ7hkrE8v6Uhdr_gjzq9M';
            
            // Parámetros para enviar a la API
            $params = [
                'accion' => $accion,
                'store_code' => $storeCode,
                'id_item' => $idItem,
                'fecha1' => $fecha1,
                'fecha2' => $fecha2
            ];
            
            // Registrar la solicitud para diagnóstico
            Log::info('Enviando solicitud a la API con parámetros: ' . json_encode($params));
            
            // Realizar la solicitud a la API externa
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json'
            ])->get('http://64.227.4.218/stargroup/middleware/awsSalesStore.php', $params);
            
            // Registrar la respuesta completa para diagnóstico
            Log::info('Respuesta recibida: ' . $response->body());
            
            // Verificar si la respuesta fue exitosa
            if ($response->successful()) {
                $jsonResponse = $response->json();
                
                // Si es un array vacío, intentar con los parámetros originales del ejemplo de Postman
                if (is_array($jsonResponse) && count($jsonResponse) === 0) {
                    Log::warning('La API devolvió un array vacío. Intentando con parámetros por defecto...');
                    
                    // Hacer una segunda solicitud con parámetros por defecto
                    $defaultParams = [
                        'accion' => 'MostrarByIdDate',
                        'store_code' => 'T03',
                        'id_item' => '250704',
                        'fecha1' => '2025-01-28',
                        'fecha2' => '2025-03-28'
                    ];
                    
                    Log::info('Enviando solicitud por defecto: ' . json_encode($defaultParams));
                    
                    $defaultResponse = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $token,
                        'Content-Type' => 'application/json'
                    ])->get('http://64.227.4.218/stargroup/middleware/awsSalesStore.php', $defaultParams);
                    
                    Log::info('Respuesta por defecto: ' . $defaultResponse->body());
                    
                    if ($defaultResponse->successful()) {
                        $defaultJsonResponse = $defaultResponse->json();
                        if (is_array($defaultJsonResponse) && count($defaultJsonResponse) > 0) {
                            // Si la solicitud por defecto devuelve datos, usamos esos en su lugar
                            // pero incluimos metadatos para informar al cliente
                            return response()->json([
                                'data' => $defaultJsonResponse,
                                'meta' => [
                                    'is_default_data' => true,
                                    'original_params' => $params,
                                    'default_params' => $defaultParams,
                                    'message' => 'No se encontraron datos con los parámetros seleccionados. Mostrando datos de ejemplo (T03/250704/enero-marzo 2025).'
                                ]
                            ]);
                        }
                    }
                }
                
                return response()->json($jsonResponse);
            } else {
                // Registrar el error
                Log::error('Error en proxy de ventas: ' . $response->status() . ' - ' . $response->body());
                return response()->json(['error' => 'Error al consultar la API: ' . $response->status()], $response->status());
            }
        } catch (\Exception $e) {
            // Registrar la excepción
            Log::error('Excepción en proxy de ventas: ' . $e->getMessage());
            return response()->json(['error' => 'Error al procesar la solicitud: ' . $e->getMessage()], 500);
        }
    }
}
