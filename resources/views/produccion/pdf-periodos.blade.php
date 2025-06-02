<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte de Producciones</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1cm;
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }
        
        .mini-header {
            text-align: center;
            margin-bottom: 8px;
            font-size: 14px;
            color: #033988;
        }
        
        .mini-footer {
            text-align: right;
            font-size: 9px;
            color: #666;
            margin-top: 8px;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 5px;
            font-size: 10px;
            text-align: left;
        }
        
        .table th {
            background-color: #033988;
            color: white;
            font-weight: bold;
        }
        
        .text-center {
            text-align: center;
        }
        
        .badge {
            display: inline-block;
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 9px;
        }
        
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        
        .badge-warning {
            background-color: #ffc107;
            color: black;
        }
        
        .badge-danger {
            background-color: #f01917;
            color: white;
        }
        
        /* Estilos para la sección de métricas */
        .metricas-container {
            display: flex;
            flex-wrap: nowrap;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .metrica-item {
            width: 24%;
            margin-right: 8px;
            padding: 10px;
            border-radius: 5px;
            color: white;
            box-sizing: border-box;
        }
        
        .metrica-title {
            font-size: 10px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .metrica-value {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .metrica-subtitle {
            font-size: 8px;
            margin-bottom: 5px;
            opacity: 0.8;
        }
        
        .progress-container {
            height: 5px;
            background-color: rgba(255, 255, 255, 0.3);
            border-radius: 2px;
            margin-bottom: 3px;
        }
        
        .progress-bar {
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 2px;
        }
        
        .metrica-percent {
            font-size: 9px;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="mini-header">
        Reporte de Producciones - {{ $fechaInicio->format('d/m/Y') }} al {{ $fechaFin->format('d/m/Y') }}
    </div>
    
    <!-- Sección de Métricas -->
    <table class="metricas-table">
        <tr>
            <td width="25%" style="background-color: #033988; color: white; padding: 10px; border-radius: 5px;">
                <div class="metrica-title">CANTIDAD PRODUCIDA</div>
                <div class="metrica-value">{{ $metricas['cantidadProducida'] }}</div>
                <div class="metrica-subtitle">de {{ $metricas['cantidadPedida'] }} unidades de pedidos</div>
                <div style="height: 5px; background-color: rgba(255, 255, 255, 0.3); border-radius: 2px; margin: 5px 0;">
                    <div style="height: 5px; background-color: rgba(255, 255, 255, 0.8); border-radius: 2px; width: {{ $metricas['porcentajeCantidad'] }}%;"></div>
                </div>
                <div style="font-size: 9px; text-align: right;">{{ $metricas['porcentajeCantidad'] }}%</div>
            </td>
            
            <td width="25%" style="background-color: #033988; color: white; padding: 10px; border-radius: 5px;">
                <div class="metrica-title">TOTAL</div>
                <div class="metrica-value">S/ {{ $metricas['valorTotal'] }}</div>
                <div class="metrica-subtitle">{{ $metricas['totalItems'] }} producciones</div>
            </td>
            
            <td width="25%" style="background-color: #28a745; color: white; padding: 10px; border-radius: 5px;">
                <div class="metrica-title">TERMINADOS</div>
                <div class="metrica-value">{{ $metricas['totalTerminados'] }}</div>
                <div class="metrica-subtitle">de {{ $metricas['totalItems'] }}</div>
                <div style="height: 5px; background-color: rgba(255, 255, 255, 0.3); border-radius: 2px; margin: 5px 0;">
                    <div style="height: 5px; background-color: rgba(255, 255, 255, 0.8); border-radius: 2px; width: {{ $metricas['porcentajeTerminados'] }}%;"></div>
                </div>
                <div style="font-size: 9px; text-align: right;">{{ $metricas['porcentajeTerminados'] }}%</div>
            </td>
            
            <td width="25%" style="background-color: #f01917; color: white; padding: 10px; border-radius: 5px;">
                <div class="metrica-title">CANCELADOS</div>
                <div class="metrica-value">{{ $metricas['totalCancelados'] }}</div>
                <div class="metrica-subtitle">de {{ $metricas['totalItems'] }}</div>
                <div style="height: 5px; background-color: rgba(255, 255, 255, 0.3); border-radius: 2px; margin: 5px 0;">
                    <div style="height: 5px; background-color: rgba(255, 255, 255, 0.8); border-radius: 2px; width: {{ $metricas['porcentajeCancelados'] }}%;"></div>
                </div>
                <div style="font-size: 9px; text-align: right;">{{ $metricas['porcentajeCancelados'] }}%</div>
            </td>
        </tr>
    </table>
    
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Producto</th>
                <th>Receta</th>
                <th>Área</th>
                <th>Usuario</th>
                <th class="text-center">Cant. Pedido</th>
                <th class="text-center">Cant. Producida</th>
                <th class="text-center">Estado</th>
                <th class="text-center">Subtotal</th>
                <th class="text-center">Costo Diseño</th>
                <th class="text-center">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($producciones as $index => $produccion)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($produccion->produccionCabecera->fecha)->format('d/m/Y') }}</td>
                    <td>{{ $produccion->produccionCabecera->hora }}</td>
                    <td>{{ $produccion->recetaCabecera->producto->nombre ?? 'N/A' }}</td>
                    <td>{{ $produccion->recetaCabecera->nombre ?? 'N/A' }}</td>
                    <td>{{ $produccion->area->nombre ?? 'N/A' }}</td>
                    <td>{{ $produccion->produccionCabecera->usuario->nombre_personal ?? 'N/A' }}</td>
                    <td class="text-center">{{ number_format($produccion->cantidad_pedido, 2) }}</td>
                    <td class="text-center">{{ number_format($produccion->cantidad_producida_real, 2) }}</td>
                    <td class="text-center">
                        <span class="badge badge-{{ $produccion->es_terminado ? 'success' : ($produccion->es_cancelado ? 'danger' : 'warning') }}">
                            {{ $produccion->es_terminado ? 'Terminado' : ($produccion->es_cancelado ? 'Cancelado' : 'Pendiente') }}
                        </span>
                    </td>
                    <td class="text-center">S/ {{ number_format($produccion->subtotal_receta, 2) }}</td>
                    <td class="text-center">S/ {{ number_format($produccion->costo_diseño, 2) }}</td>
                    <td class="text-center">S/ {{ number_format($produccion->total_receta, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" class="text-center">No se encontraron producciones para el período seleccionado</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="mini-footer">
        Generado: {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>

