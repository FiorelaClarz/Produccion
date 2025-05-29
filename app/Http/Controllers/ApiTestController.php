<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\TokenHelper;
use App\Models\Tienda;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ApiTestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * Test de la API de ventas para verificar que la integración funciona correctamente
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function testSalesApi(Request $request)
    {
        // Obtener parámetros o usar valores predeterminados
        $tiendaCodigo = $request->input('tienda_codigo', 'T01');
        $idItem = $request->input('id_item', '250704'); // Item predeterminado
        $fechaInicio = $request->input('fecha_inicio', date('Y-m-d', strtotime('-7 days')));
        $fechaFin = $request->input('fecha_fin', date('Y-m-d'));
        $force = $request->input('force', false);
        
        // Si se solicita forzar regeneración del token
        if ($force) {
            Cache::forget(TokenHelper::TOKEN_CACHE_KEY);
            Cache::forget(TokenHelper::TOKEN_EXPIRATION_CACHE_KEY);
        }
        
        // Resultado completo del test
        $result = [
            'status' => 'success',
            'time' => now()->format('Y-m-d H:i:s'),
            'parameters' => [
                'tienda_codigo' => $tiendaCodigo,
                'id_item' => $idItem,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'force_new_token' => $force
            ],
            'token_test' => [],
            'api_test' => [],
            'db_test' => []
        ];
        
        // Test 1: Verificar obtención de token
        try {
            // Verificar el script gettoken.php directamente
            $tokenPath = resource_path('api/token/gettoken.php');
            $command = PHP_BINARY . ' ' . escapeshellarg($tokenPath);
            $scriptResponse = shell_exec($command);
            
            $result['token_script_test'] = [
                'raw_response' => $scriptResponse,
                'response_length' => strlen($scriptResponse),
                'is_json' => json_decode($scriptResponse) !== null,
                'json_last_error' => json_last_error_msg(),
            ];
            
            // Ahora intentar obtener el token con TokenHelper
            $token = TokenHelper::getValidToken();
            $result['token_test'] = [
                'status' => !empty($token) ? 'success' : 'error',
                'token' => !empty($token) ? substr($token, 0, 15) . '...' : null,
                'token_length' => strlen($token),
                'cache_status' => Cache::has(TokenHelper::TOKEN_CACHE_KEY) ? 'cached' : 'not_cached',
                'expiration' => Cache::has(TokenHelper::TOKEN_EXPIRATION_CACHE_KEY) ? 
                    Cache::get(TokenHelper::TOKEN_EXPIRATION_CACHE_KEY)->format('Y-m-d H:i:s') : null
            ];
        } catch (\Exception $e) {
            $result['token_test'] = [
                'status' => 'error',
                'message' => 'Error al obtener token: ' . $e->getMessage()
            ];
            $result['status'] = 'error';
        }
        
        // Test 2: Verificar llamada a API de ventas
        if (!empty($token)) {
            try {
                // Hacemos la llamada a la API directamente para propósitos de test
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json'
                ])->get(TokenHelper::SALES_STORE_ENDPOINT, [
                    'accion' => 'MostrarByIdDate',
                    'store_code' => $tiendaCodigo,
                    'id_item' => $idItem,
                    'fecha1' => $fechaInicio,
                    'fecha2' => $fechaFin
                ]);
                
                $data = $response->json();
                $statusCode = $response->status();
                
                $cantidadVendida = 0;
                if ($response->successful() && isset($data['data']) && is_array($data['data'])) {
                    foreach ($data['data'] as $venta) {
                        $cantidadVendida += isset($venta['cantidad']) ? floatval($venta['cantidad']) : 0;
                    }
                }
                
                $result['api_test'] = [
                    'status' => $response->successful() ? 'success' : 'error',
                    'http_code' => $statusCode,
                    'response_data_count' => isset($data['data']) ? count($data['data']) : 0,
                    'cantidad_vendida' => $cantidadVendida,
                    'raw_data' => isset($data['data']) ? $data['data'] : null
                ];
                
                if (!$response->successful()) {
                    $result['api_test']['error_details'] = $data;
                    $result['status'] = 'error';
                }
            } catch (\Exception $e) {
                $result['api_test'] = [
                    'status' => 'error',
                    'message' => 'Error al hacer llamada a API: ' . $e->getMessage()
                ];
                $result['status'] = 'error';
            }
        }
        
        // Test 3: Verificar datos de tienda en la base de datos
        try {
            $tiendas = Tienda::activos()->get();
            $tiendasCount = $tiendas->count();
            
            $tiendaEncontrada = Tienda::where('codigo_tienda', $tiendaCodigo)
                ->where('status', true)
                ->where('is_deleted', false)
                ->first();
            
            $result['db_test'] = [
                'status' => 'success',
                'tiendas_count' => $tiendasCount,
                'tienda_actual' => $tiendaEncontrada ? [
                    'id' => $tiendaEncontrada->id_tiendas,
                    'nombre' => $tiendaEncontrada->nombre,
                    'codigo' => $tiendaEncontrada->codigo_tienda,
                    'status' => $tiendaEncontrada->status
                ] : null,
                'tienda_encontrada' => $tiendaEncontrada ? true : false
            ];
            
            if (!$tiendaEncontrada) {
                $result['db_test']['otras_tiendas'] = $tiendas->take(5)->map(function($tienda) {
                    return [
                        'id' => $tienda->id_tiendas,
                        'nombre' => $tienda->nombre,
                        'codigo' => $tienda->codigo_tienda
                    ];
                });
            }
        } catch (\Exception $e) {
            $result['db_test'] = [
                'status' => 'error',
                'message' => 'Error al obtener datos de tienda: ' . $e->getMessage()
            ];
        }
        
        // Devolver resultado como JSON
        return response()->json($result);
    }
    
    /**
     * Test de consulta usando el método mejorado de TokenHelper
     */
    public function testHelperMethod(Request $request)
    {
        // Obtener parámetros o usar valores predeterminados
        $tiendaCodigo = $request->input('tienda_codigo', 'T01');
        $idItem = $request->input('id_item', '250704'); // Item predeterminado
        $fechaInicio = $request->input('fecha_inicio', date('Y-m-d', strtotime('-7 days')));
        $fechaFin = $request->input('fecha_fin', date('Y-m-d'));
        
        // Resultado de la prueba
        $result = [
            'status' => 'success',
            'time' => now()->format('Y-m-d H:i:s'),
            'parameters' => [
                'tienda_codigo' => $tiendaCodigo,
                'id_item' => $idItem,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin
            ],
            'test_result' => []
        ];
        
        try {
            // Usar el método mejorado de TokenHelper
            $startTime = microtime(true);
            
            $cantidadVendida = TokenHelper::consultarVentasPorTienda(
                $tiendaCodigo,
                $idItem,
                $fechaInicio,
                $fechaFin
            );
            
            $endTime = microtime(true);
            $executionTime = round(($endTime - $startTime) * 1000, 2); // en milisegundos
            
            $result['test_result'] = [
                'status' => 'success',
                'cantidad_vendida' => $cantidadVendida,
                'execution_time_ms' => $executionTime,
                'token_cached' => Cache::has(TokenHelper::TOKEN_CACHE_KEY)
            ];
        } catch (\Exception $e) {
            $result['test_result'] = [
                'status' => 'error',
                'message' => 'Error al consultar ventas: ' . $e->getMessage()
            ];
            $result['status'] = 'error';
        }
        
        // Devolver resultado como JSON
        return response()->json($result);
    }
}
