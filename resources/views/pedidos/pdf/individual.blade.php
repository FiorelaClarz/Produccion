<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Pedido #{{ $pedido->id_pedidos_cab }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
        }

        .subtitle {
            font-size: 14px;
            color: #555;
        }

        .info {
            margin-bottom: 15px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .info-box {
            flex: 1;
            min-width: 200px;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
        }

        .info-box-title {
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        .table th {
            background-color: #f2f2f2;
            text-align: left;
        }

        .footer {
            margin-top: 30px;
            font-size: 10px;
            text-align: center;
            color: #777;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        .personalizado {
            background-color: #fff8e1;
        }

        .imagen-container {
            margin-top: 10px;
            text-align: center;
        }

        .imagen-pedido {
            max-width: 200px;
            max-height: 150px;
            border: 1px solid #ddd;
        }

        .texto-pequeno {
            font-size: 10px;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="title">PEDIDO #{{ $pedido->id_pedidos_cab }}</div>
        <div class="subtitle">Documento Interno: {{ $pedido->doc_interno }}</div>
    </div>

    <div class="info">
        <div class="info-box">
            <div class="info-box-title">Información General</div>
            <div><strong>Fecha:</strong> {{ $pedido->fecha_created }}</div>
            <div><strong>Hora:</strong> {{ $pedido->hora_created }}</div>
            <div><strong>Hora Límite:</strong> {{ $pedido->hora_limite }}</div>
            <div><strong>Estado:</strong>
                @if($pedido->esta_dentro_de_hora)
                <span style="color: green;">Dentro de horario</span>
                @else
                <span style="color: red;">Fuera de horario</span>
                @endif
            </div>
        </div>

        <div class="info-box">
            <div class="info-box-title">Información de Usuario</div>
            <div><strong>Tienda:</strong> {{ $pedido->tienda->nombre ?? 'N/A' }}</div>
            <div><strong>Usuario:</strong> {{ $pedido->usuario->nombre_personal ?? 'N/A' }}</div>
            <div><strong>Rol:</strong> {{ $pedido->usuario->rol->nombre ?? 'N/A' }}</div>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="15%">Área</th>
                <th width="20%">Producto/Receta</th>
                <th width="10%">Cantidad</th>
                <th width="10%">Unidad</th>
                <th width="10%">Estado</th>
                <th width="10%">Personalizado</th>
                <th width="20%">Descripción</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pedido->pedidosDetalle as $index => $detalle)
            <tr class="{{ $detalle->es_personalizado ? 'personalizado' : '' }}">
                <td>{{ $index + 1 }}</td>
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
                <td class="text-center">
                    @if($detalle->es_personalizado)
                    <strong>SÍ</strong>
                    @else
                    No
                    @endif
                </td>
                <td>
                    @if($detalle->es_personalizado && $detalle->descripcion)
                    {{ $detalle->descripcion }}
                    @else
                    -
                    @endif
                </td>
            </tr>
            @if($detalle->es_personalizado && $detalle->foto_referencial)
            <tr class="personalizado">
                <td colspan="8" class="p-2">
                    <div class="imagen-container">
                        <div><strong>Imagen de referencia:</strong></div>
                        @if(Storage::disk('public')->exists($detalle->foto_referencial))
                        <img src="data:image/png;base64,{{ base64_encode(Storage::disk('public')->get($detalle->foto_referencial)) }}"
                            class="imagen-pedido">
                        @else
                        <div class="text-danger">Imagen no encontrada</div>
                        @endif
                        <div class="texto-pequeno">Referencia: {{ basename($detalle->foto_referencial) }}</div>
                    </div>
                </td>
            </tr>
            @endif
            @endforeach
        </tbody>
    </table>



    <div class="footer">
        Generado el {{ now()->format('d/m/Y H:i:s') }} | Sistema de Pedidos
    </div>
</body>

</html>