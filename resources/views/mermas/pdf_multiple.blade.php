<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 15px;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #2c3e50;
        }
        .header p {
            margin: 5px 0 0;
            color: #7f8c8d;
            font-size: 11px;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-section h2 {
            font-size: 14px;
            margin: 0 0 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
            color: #3498db;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th {
            background-color: #3498db;
            color: white;
            padding: 8px;
            font-size: 12px;
            text-align: left;
        }
        td {
            padding: 6px 8px;
            font-size: 11px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #7f8c8d;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .merma-header {
            background-color: #f8f9fa;
            padding: 8px;
            margin-top: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .merma-header h3 {
            margin: 0;
            font-size: 14px;
            color: #2c3e50;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        .summary-table th {
            background-color: #34495e;
            color: white;
            padding: 8px;
            text-align: left;
        }
        .summary-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #ddd;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Documento generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="info-section">
        <h2>Resumen de Mermas</h2>
        <table class="summary-table">
            <thead>
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th style="width: 80px;">Fecha</th>
                    <th style="width: 70px;">Hora</th>
                    <th>Usuario</th>
                    <th>Tienda</th>
                    <th style="width: 50px;" class="text-center">Ítems</th>
                    <th style="width: 80px;" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalMermas = 0;
                @endphp
                @forelse($mermas as $merma)
                    @php
                        $subtotal = $merma->mermasDetalle->where('is_deleted', false)->sum('total');
                        $totalMermas += $subtotal;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $merma->id_mermas_cab }}</td>
                        <td>{{ \Carbon\Carbon::parse($merma->fecha_registro)->format('d/m/Y') }}</td>
                        <td>{{ $merma->hora_registro }}</td>
                        <td>{{ $merma->usuario->nombre_personal ?? 'N/A' }}</td>
                        <td>{{ $merma->tienda->nombre ?? 'N/A' }}</td>
                        <td class="text-center">{{ $merma->mermasDetalle->where('is_deleted', false)->count() }}</td>
                        <td class="text-right">{{ number_format($subtotal, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No hay mermas disponibles</td>
                    </tr>
                @endforelse
                @if($mermas->count() > 0)
                    <tr>
                        <td colspan="6" class="text-right"><strong>TOTAL:</strong></td>
                        <td class="text-right"><strong>{{ number_format($totalMermas, 2) }}</strong></td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    @foreach($mermas as $merma)
        <div class="merma-header">
            <h3>Merma #{{ $merma->id_mermas_cab }} - {{ \Carbon\Carbon::parse($merma->fecha_registro)->format('d/m/Y') }} {{ $merma->hora_registro }}</h3>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th style="width: 30px;">#</th>
                    <th style="width: 80px;">Área</th>
                    <th>Receta</th>
                    <th>Producto</th>
                    <th style="width: 60px;" class="text-center">Cantidad</th>
                    <th style="width: 60px;" class="text-center">Costo</th>
                    <th style="width: 60px;" class="text-center">Total</th>
                    <th style="width: 70px;">U. Medida</th>
                    <th>Observación</th>
                </tr>
            </thead>
            <tbody>
                @forelse($merma->mermasDetalle->where('is_deleted', false) as $index => $detalle)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $detalle->area->nombre ?? 'N/A' }}</td>
                        <td>{{ $detalle->receta->nombre ?? 'N/A' }}</td>
                        <td>{{ optional($detalle->receta)->producto_nombre ?? 'N/A' }}</td>
                        <td class="text-center">{{ number_format($detalle->cantidad, 2) }}</td>
                        <td class="text-center">{{ number_format($detalle->costo ?? 0, 2) }}</td>
                        <td class="text-center">{{ number_format($detalle->total ?? 0, 2) }}</td>
                        <td>{{ $detalle->uMedida->nombre ?? 'N/A' }}</td>
                        <td>{{ $detalle->obs ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">No hay detalles disponibles</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endforeach

    <div class="footer">
        <p>Este documento es un reporte oficial de mermas. Conserve este documento para sus registros.</p>
        <p>Sistema de Gestión de Producción - {{ \Carbon\Carbon::now()->format('Y') }}</p>
    </div>
</body>
</html>


