<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comparativo de Producción y Mermas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            margin: 5px 0;
        }
        .subtitle {
            font-size: 14px;
            margin: 5px 0;
        }
        .date {
            font-size: 12px;
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            padding: 8px;
            font-size: 11px;
            text-align: center;
        }
        td {
            padding: 6px;
            font-size: 10px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            font-size: 10px;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
        .totals {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="title">{{ $title }}</div>
            <div class="subtitle">{{ $subtitle }}</div>
            <div class="date">Generado el: {{ date('d/m/Y H:i:s') }}</div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ÁREA</th>
                    <th>PRODUCTO</th>
                    <th>PROD.</th>
                    <th>VENTA</th>
                    <th>MERMA</th>
                    <th>DIFERENCIA</th>
                    <th>UTIL. BRUTA</th>
                    <th>VENTAS</th>
                    <th>COSTO MERMA</th>
                    <th>COSTO DIF.</th>
                </tr>
            </thead>
            <tbody>
                @forelse($resultados as $resultado)
                    <tr>
                        <td>{{ $resultado['area'] }}</td>
                        <td>{{ $resultado['producto'] }}</td>
                        <td class="text-right">{{ number_format($resultado['cantidad_producida'], 2) }}</td>
                        <td class="text-right">{{ number_format($resultado['cantidad_vendida'], 2) }}</td>
                        <td class="text-right">{{ number_format($resultado['cantidad_merma'], 2) }}</td>
                        <td class="text-right">{{ number_format($resultado['diferencia'], 2) }}</td>
                        <td class="text-right">S/ {{ number_format($resultado['utilidad_bruta'], 2) }}</td>
                        <td class="text-right">S/ {{ number_format($resultado['ventas'], 2) }}</td>
                        <td class="text-right">S/ {{ number_format($resultado['costo_merma'], 2) }}</td>
                        <td class="text-right">S/ {{ number_format($resultado['costo_diferencia'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center">No hay datos disponibles</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="totals">
                    <td colspan="2" class="text-right">TOTALES:</td>
                    <td class="text-right">{{ number_format($totales['produccion'], 2) }}</td>
                    <td class="text-right">{{ number_format($totales['venta'], 2) }}</td>
                    <td class="text-right">{{ number_format($totales['merma'], 2) }}</td>
                    <td class="text-right">{{ number_format($totales['diferencia'], 2) }}</td>
                    <td class="text-right">S/ {{ number_format($totales['utilidad_bruta'], 2) }}</td>
                    <td class="text-right">S/ {{ number_format($totales['ventas'], 2) }}</td>
                    <td class="text-right">S/ {{ number_format($totales['costo_merma'], 2) }}</td>
                    <td class="text-right">S/ {{ number_format($totales['costo_diferencia'], 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <div>
            <h3>Resumen de resultados:</h3>
            <ul>
                <li><strong>Producción total:</strong> {{ number_format($totales['produccion'], 2) }} KG</li>
                <li><strong>Ventas estimadas:</strong> {{ number_format($totales['venta'], 2) }} KG (S/ {{ number_format($totales['ventas'], 2) }})</li>
                <li><strong>Mermas registradas:</strong> {{ number_format($totales['merma'], 2) }} KG (S/ {{ number_format($totales['costo_merma'], 2) }})</li>
                <li><strong>Diferencia:</strong> {{ number_format($totales['diferencia'], 2) }} KG (S/ {{ number_format($totales['costo_diferencia'], 2) }})</li>
                <li><strong>Utilidad bruta estimada:</strong> S/ {{ number_format($totales['utilidad_bruta'], 2) }}</li>
            </ul>
        </div>

        <div class="footer">
            Comparativo de Producción y Mermas - Página 1
        </div>
    </div>
</body>
</html>


