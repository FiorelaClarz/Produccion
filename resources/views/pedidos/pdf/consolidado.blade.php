<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Consolidado de Pedidos - {{ $fecha }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: bold; }
        .subtitle { font-size: 14px; color: #555; }
        .info { margin-bottom: 15px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; }
        .table th { background-color: #f2f2f2; text-align: left; }
        .footer { margin-top: 30px; font-size: 12px; text-align: center; color: #777; }
        .pedido-header { background-color: #e9ecef; padding: 5px; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">CONSOLIDADO DE PEDIDOS</div>
        <div class="subtitle">Fecha: {{ $fecha }}</div>
    </div>

    @foreach($pedidos as $pedido)
    <div class="pedido-header">
        <strong>Pedido #{{ $pedido->id_pedidos_cab }}</strong> - 
        Documento: {{ $pedido->doc_interno }} - 
        Tienda: {{ $pedido->tienda->nombre ?? 'N/A' }} - 
        Usuario: {{ $pedido->usuario->nombre_personal ?? 'N/A' }}
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Área</th>
                <th>Producto/Receta</th>
                <th>Cantidad</th>
                <th>Unidad</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pedido->pedidosDetalle as $detalle)
            <tr>
                <td>{{ $detalle->area->nombre ?? 'N/A' }}</td>
                <td>
                    @if($detalle->receta)
                        {{ $detalle->receta->nombre }}
                    @else
                        {{ $detalle->descripcion ?? 'Personalizado' }}
                    @endif
                </td>
                <td>{{ $detalle->cantidad }}</td>
                <td>{{ $detalle->uMedida->nombre ?? 'N/A' }}</td>
                <td>{{ $detalle->estado->nombre ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endforeach

    <div class="footer">
        Generado el {{ now()->format('d/m/Y H:i:s') }} - Total de pedidos: {{ $pedidos->count() }}
    </div>
</body>
</html>