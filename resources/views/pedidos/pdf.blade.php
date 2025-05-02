<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pedido #{{ $pedido->id_pedidos_cab }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; color: #333; }
        .header p { margin: 5px 0; color: #666; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 8px; border: 1px solid #ddd; }
        .info-table .label { font-weight: bold; background-color: #f5f5f5; width: 30%; }
        .products-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .products-table th { background-color: #343a40; color: white; padding: 10px; text-align: left; }
        .products-table td { padding: 8px; border: 1px solid #ddd; }
        .products-table tr:nth-child(even) { background-color: #f9f9f9; }
        .footer { margin-top: 30px; text-align: right; font-size: 12px; color: #666; }
        .status-badge { 
            padding: 5px 10px; 
            border-radius: 20px; 
            font-weight: bold; 
            display: inline-block; 
            margin-bottom: 10px;
        }
        .status-active { background-color: #28a745; color: white; }
        .status-inactive { background-color: #dc3545; color: white; }
        .signature-area { margin-top: 50px; border-top: 1px dashed #333; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Pedido #{{ $pedido->id_pedidos_cab }}</h1>
        <p>Generado el {{ now()->format('d/m/Y H:i:s') }}</p>
        <div class="status-badge {{ $pedido->esta_dentro_de_hora ? 'status-active' : 'status-inactive' }}">
            {{ $pedido->esta_dentro_de_hora ? 'DENTRO DE HORA' : 'FUERA DE HORA' }}
        </div>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">Fecha creación</td>
            <td>{{ $pedido->fecha_created }} {{ $pedido->hora_created }}</td>
        </tr>
        <tr>
            <td class="label">Última actualización</td>
            <td>{{ $pedido->fecha_last_update }} {{ $pedido->hora_last_update }}</td>
        </tr>
        <tr>
            <td class="label">Hora límite</td>
            <td>{{ $pedido->horaLimite->hora_limite }}</td>
        </tr>
        <tr>
            <td class="label">Usuario</td>
            <td>{{ $pedido->usuario->nombre ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Tienda</td>
            <td>{{ $pedido->tienda->nombre ?? 'N/A' }}</td>
        </tr>
    </table>

    <h3>Productos solicitados</h3>
    <table class="products-table">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Unidad</th>
                <th>Área</th>
                <th>Estado</th>
                <th>Personalizado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pedido->pedidosDetalle as $detalle)
            <tr>
                <td>{{ $detalle->producto->nombre ?? 'N/A' }}</td>
                <td>{{ $detalle->cantidad }}</td>
                <td>{{ $detalle->uMedida->nombre ?? 'N/A' }}</td>
                <td>{{ $detalle->area->nombre ?? 'N/A' }}</td>
                <td>{{ $detalle->estado->nombre ?? 'N/A' }}</td>
                <td>{{ $detalle->es_personalizado ? 'Sí' : 'No' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($pedido->pedidosDetalle->where('es_personalizado', true)->count() > 0)
    <h3>Productos personalizados</h3>
    <table class="products-table">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Descripción</th>
                <th>Referencia</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pedido->pedidosDetalle->where('es_personalizado', true) as $detalle)
            <tr>
                <td>{{ $detalle->producto->nombre ?? 'N/A' }}</td>
                <td>{{ $detalle->descripcion ?? 'N/A' }}</td>
                <td>
                    @if($detalle->foto_referencial)
                    <img src="{{ storage_path('app/public/storage/pedidos' . $detalle->foto_referencial) }}" width="100">
                    @elseif($detalle->foto_referencial_url)
                    [Imagen externa]
                    @else
                    N/A
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="signature-area">
        <p>Firma del responsable: _________________________________________</p>
        <p>Nombre: {{ $pedido->usuario->nombre ?? 'N/A' }}</p>
        <p>Fecha: {{ now()->format('d/m/Y') }}</p>
    </div>

    <div class="footer">
        <p>Sistema de Pedidos - {{ config('app.name') }}</p>
        <p>Documento generado automáticamente - No requiere firma física</p>
    </div>
</body>
</html>