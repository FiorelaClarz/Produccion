<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Producción #{{ $produccion->id_produccion_cab }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: bold; }
        .subtitle { font-size: 14px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; }
        .table th { background-color: #f2f2f2; text-align: left; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 5px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { margin-top: 30px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Reporte de Producción</div>
        <div class="subtitle">N° {{ $produccion->doc_interno }}</div>
    </div>
    
    <table class="info-table">
        <tr>
            <td><strong>Fecha:</strong> {{ $produccion->fecha }}</td>
            <td><strong>Hora:</strong> {{ $produccion->hora }}</td>
        </tr>
        <tr>
            <td><strong>Usuario:</strong> {{ $produccion->usuario->nombre_personal }}</td>
            <td><strong>Turno:</strong> {{ $produccion->turno->nombre }}</td>
        </tr>
        <tr>
            <td><strong>Equipo:</strong> {{ $produccion->equipo->nombre }}</td>
            <td><strong>Área:</strong> {{ $produccion->produccionesDetalle->first()->area->nombre ?? 'N/A' }}</td>
        </tr>
    </table>
    
    <table class="table">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Receta</th>
                <th class="text-right">Cant. Pedido</th>
                <th class="text-right">Cant. Esperada</th>
                <th class="text-right">Cant. Producida</th>
                <th>Unidad</th>
                <th class="text-right">Subtotal</th>
                <th class="text-right">Costo Diseño</th>
                <th class="text-right">Total</th>
                <th class="text-right">Cant. Harina</th>
            </tr>
        </thead>
        <tbody>
            @foreach($produccion->produccionesDetalle as $detalle)
            <tr>
                <td>{{ $detalle->producto->nombre }}</td>
                <td>{{ $detalle->recetaCabecera->nombre }}</td>
                <td class="text-right">{{ number_format($detalle->cantidad_pedido, 2) }}</td>
                <td class="text-right">{{ number_format($detalle->cantidad_esperada, 2) }}</td>
                <td class="text-right">{{ number_format($detalle->cantidad_producida_real, 2) }}</td>
                <td>{{ $detalle->uMedidaProd->nombre }}</td>
                <td class="text-right">{{ number_format($detalle->subtotal_receta, 2) }}</td>
                <td class="text-right">{{ number_format($detalle->costo_diseño, 2) }}</td>
                <td class="text-right">{{ number_format($detalle->total_receta, 2) }}</td>
                <td class="text-right">{{ number_format($detalle->cant_harina, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="6" class="text-right">Totales:</th>
                <th class="text-right">{{ number_format($produccion->produccionesDetalle->sum('subtotal_receta'), 2) }}</th>
                <th class="text-right">{{ number_format($produccion->produccionesDetalle->sum('costo_diseño'), 2) }}</th>
                <th class="text-right">{{ number_format($produccion->produccionesDetalle->sum('total_receta'), 2) }}</th>
                <th class="text-right">{{ number_format($produccion->produccionesDetalle->sum('cant_harina'), 2) }}</th>
            </tr>
        </tfoot>
    </table>
    
    <div class="footer">
        <p>Generado el: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>