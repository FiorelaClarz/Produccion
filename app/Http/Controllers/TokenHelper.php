<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class TokenHelper
{
    /**
     * Clave de caché para el token de API
     */
    const TOKEN_CACHE_KEY = 'sales_api_token';
    
    /**
     * Clave de caché para la expiración del token
     */
    const TOKEN_EXPIRATION_CACHE_KEY = 'sales_api_token_expiration';
    
    /**
     * Endpoint del token para la API de ventas
     */
    const TOKEN_ENDPOINT = 'http://64.227.4.218/stargroup/middleware/awsToken.php';
    
    /**
     * Endpoint de las ventas por tienda
     */
    const SALES_STORE_ENDPOINT = 'http://64.227.4.218/stargroup/middleware/awsSalesStore.php';
    
    /**
     * Obtiene un token válido para la API de ventas
     * Si el token está expirado o no existe, genera uno nuevo
     *
     * @return string Token de autorización
     */
    public static function getValidToken()
    {
        // Intentar obtener el token de caché primero
        $apiToken = Cache::get(self::TOKEN_CACHE_KEY);
        $tokenExpiration = Cache::get(self::TOKEN_EXPIRATION_CACHE_KEY);
        
        // Verificar si el token existe y no ha expirado
        $now = now();
        if (!$apiToken || !$tokenExpiration || $now->greaterThan($tokenExpiration)) {
            // Token expirado o no existe, generar uno nuevo
            $tokenPath = resource_path('api/token/gettoken.php');
            
            try {
                // En lugar de ejecutar el script, haremos la llamada directamente a la API
                // ya que vimos que funciona correctamente en la prueba
                $curl = curl_init();
                
                curl_setopt_array($curl, array(
                    CURLOPT_URL => self::TOKEN_ENDPOINT,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 10,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => array('userID' => 'voverkok_red%99414'),
                ));
                
                $response = curl_exec($curl);
                $error = curl_error($curl);
                $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                
                curl_close($curl);
                
                // Registrar la respuesta original para depuración
                Log::debug('Respuesta de la API de token: ' . $response);
                
                if ($error) {
                    throw new \Exception('Error en la petición cURL: ' . $error);
                }
                
                if ($httpCode !== 200) {
                    throw new \Exception('Error HTTP: ' . $httpCode);
                }
                
                if (empty($response)) {
                    throw new \Exception('No se obtuvo respuesta de la API de token');
                }
                
                // Intentar decodificar la respuesta JSON
                $responseData = json_decode($response, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Error al decodificar respuesta JSON: ' . json_last_error_msg() . '. Respuesta original: ' . $response);
                }
                
                // Registrar los datos decodificados para depuración
                Log::debug('Datos de respuesta decodificados: ' . json_encode($responseData));
                
                if (!isset($responseData['token']) || empty($responseData['token'])) {
                    throw new \Exception('Token no encontrado en la respuesta. Datos recibidos: ' . json_encode($responseData));
                }
                
                $apiToken = $responseData['token'];
                
                // Establecer la expiración del token (normalmente 12 horas)
                $tokenExpiration = now()->addHours(11); // Un poco menos que la duración real para estar seguros
                
                // Guardar en caché
                Cache::put(self::TOKEN_CACHE_KEY, $apiToken, $tokenExpiration);
                Cache::put(self::TOKEN_EXPIRATION_CACHE_KEY, $tokenExpiration, $tokenExpiration);
                
                Log::info('Nuevo token de API generado correctamente');
            } catch (\Exception $e) {
                // Registrar el error
                Log::error('Error al generar token: ' . $e->getMessage());
                return '';
            }
        }
        
        return $apiToken;
    }
    
    /**
     * Realiza una petición a la API de ventas usando un token válido
     *
     * @param string $codigoTienda Código de tienda para consultar
     * @param string $idItem ID del producto a consultar
     * @param string $fechaInicio Fecha de inicio en formato Y-m-d
     * @param string $fechaFin Fecha de fin en formato Y-m-d
     * @return array Arreglo con la cantidad vendida y el costo total
     */
    public static function consultarVentasPorTienda($codigoTienda, $idItem, $fechaInicio, $fechaFin)
    {
        $result = [
            'cantidad_vendida' => 0,
            'costo_total' => 0
        ];
        $token = self::getValidToken();
        
        if (empty($token)) {
            Log::warning('No se pudo obtener un token válido para consultar ventas');
            return $result;
        }
        
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json'
            ])->get(self::SALES_STORE_ENDPOINT, [
                'accion' => 'MostrarByIdDate',
                'store_code' => $codigoTienda,
                'id_item' => $idItem,
                'fecha1' => $fechaInicio,
                'fecha2' => $fechaFin
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                // La respuesta puede ser un array directo de ventas o estar en data[]
                $ventas = [];
                
                if (is_array($data)) {
                    // Verificar si la respuesta es un array directo de ventas
                    if (isset($data[0]) && is_array($data[0])) {
                        $ventas = $data;
                    } 
                    // O si tiene una estructura con data[]
                    else if (isset($data['data']) && is_array($data['data'])) {
                        $ventas = $data['data'];
                    }
                }
                
                // Procesar las ventas encontradas
                foreach ($ventas as $venta) {
                    // Obtener la cantidad vendida
                    $cantidad = 0;
                    if (isset($venta['sales_quantity'])) {
                        $cantidad = floatval(str_replace(',', '.', $venta['sales_quantity']));
                    } else if (isset($venta['cantidad'])) {
                        $cantidad = floatval(str_replace(',', '.', $venta['cantidad']));
                    }
                    
                    // Sumar la cantidad vendida
                    $result['cantidad_vendida'] += $cantidad;
                    
                    // CAMBIO AQUÍ: Verificar si existe total_sales y usarlo directamente
                    if (isset($venta['total_sales'])) {
                        // Sumar el total_sales directamente al costo_total
                        $result['costo_total'] += floatval(str_replace(',', '.', $venta['total_sales']));
                    } else {
                        // Comportamiento anterior: calcular costo_total como cantidad * costo unitario
                        $costoUnitario = 0;
                        if (isset($venta['cost'])) {
                            $costoUnitario = floatval(str_replace(',', '.', $venta['cost']));
                        }
                        $result['costo_total'] += $cantidad * $costoUnitario;
                    }
                }
                
                // Registrar los resultados para debugging
                Log::debug('Ventas encontradas: ' . count($ventas) . 
                          ', Cantidad total: ' . $result['cantidad_vendida'] . 
                          ', Costo total: ' . $result['costo_total']);
            } else if ($response->status() === 401) {
                // Token expirado, eliminarlo de la caché para forzar regeneración
                Cache::forget(self::TOKEN_CACHE_KEY);
                Cache::forget(self::TOKEN_EXPIRATION_CACHE_KEY);
                Log::warning('Token expirado, se forzará regeneración en la próxima solicitud');
            }
        } catch (\Exception $e) {
            Log::error('Error al consultar API de ventas: ' . $e->getMessage());
        }
        
        return $result;
    }
}
