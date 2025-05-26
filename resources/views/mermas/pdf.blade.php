<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Merma #{{ $merma->id_mermas_cab }}</title>
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
        .info-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }
        .info-row {
            display: table-row;
        }
        .info-cell {
            display: table-cell;
            padding: 5px 10px;
            border: 1px solid #ddd;
        }
        .info-label {
            font-weight: bold;
            background-color: #f9f9f9;
            width: 130px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORTE DE MERMA #{{ $merma->id_mermas_cab }}</h1>
        <p>Documento generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="info-section">
        <h2>Información General</h2>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-cell info-label">Fecha:</div>
                <div class="info-cell">{{ \Carbon\Carbon::parse($merma->fecha_registro)->format('d/m/Y') }}</div>
                <div class="info-cell info-label">Hora:</div>
                <div class="info-cell">{{ $merma->hora_registro }}</div>
            </div>
            <div class="info-row">
                <div class="info-cell info-label">Usuario:</div>
                <div class="info-cell">{{ $merma->usuario->nombre_personal ?? 'N/A' }}</div>
                <div class="info-cell info-label">Tienda:</div>
                <div class="info-cell">{{ $merma->tienda->nombre ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-cell info-label">Última actualización:</div>
                <div class="info-cell">{{ \Carbon\Carbon::parse($merma->last_update)->format('d/m/Y H:i:s') }}</div>
                <div class="info-cell info-label">Total de ítems:</div>
                <div class="info-cell">{{ $merma->mermasDetalle->where('is_deleted', false)->count() }}</div>
            </div>
        </div>
    </div>

    <div class="info-section">
        <h2>Detalle de Productos</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 30px;">#</th>
                    <th style="width: 80px;">Área</th>
                    <th>Receta</th>
                    <th>Producto</th>
                    <th style="width: 60px;" class="text-center">Cantidad</th>
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
                        <td>{{ $detalle->uMedida->nombre ?? 'N/A' }}</td>
                        <td>{{ $detalle->obs ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No hay detalles disponibles</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>Este documento es un reporte oficial de mermas. Conserve este documento para sus registros.</p>
        <p>Sistema de Gestión de Producción - {{ \Carbon\Carbon::now()->format('Y') }}</p>
    </div>
</body>
</html>
