 
@extends('layouts.app')

<style>
    @page {
        size: A4 landscape;
        margin: 1cm;
    }
    
    body {
        margin: 0;
        padding: 0;
    }
    
    .header {
        text-align: center;
        margin-bottom: 1.5cm;
    }
    
    .header h3 {
        margin: 0;
        font-size: 18px;
    }
    
    .header p {
        margin: 5px 0 0;
        font-size: 14px;
    }
    
    .footer {
        text-align: center;
        margin-top: 1cm;
        font-size: 12px;
    }
    
    .table {
        width: 100%;
        margin: 0;
        border-collapse: collapse;
    }
    
    .table th,
    .table td {
        border: 1px solid #000;
        padding: 5px;
        font-size: 12px;
    }
    
    .table th {
        background-color: #f8f9fa;
        font-weight: bold;
    }
    
    .text-center {
        text-align: center;
    }
    
    .badge {
        display: inline-block;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 12px;
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
        background-color: #dc3545;
        color: white;
    }
</style>

@section('content')
<div class="header">
    <h3>Reporte de Producciones</h3>
    <p>Del {{ $fechaInicio->format('d/m/Y') }} al {{ $fechaFin->format('d/m/Y') }}</p>
</div>

<div class="footer">
    Generado el: {{ now()->format('d/m/Y H:i:s') }}
</div>

<table class="table">
    <thead>
        <tr>
            <th>#</th>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Producto</th>
            <th>Receta</th>
            <th>Área</th>
            <th>Usuario Responsable</th>
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
                <td class="text-center">{{ number_format($produccion->subtotal_receta, 2) }}</td>
                <td class="text-center">{{ number_format($produccion->costo_diseño, 2) }}</td>
                <td class="text-center">{{ number_format($produccion->total_receta, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="13" class="text-center">No se encontraron producciones para el período seleccionado</td>
            </tr>
        @endforelse
    </tbody>
</table>

@endsection
 @section('content')
<style>
    @page {
        size: A4 landscape;
        margin: 1cm;
    }
    
    body {
        margin: 0;
        padding: 0;
    }
    
    .header {
        text-align: center;
        margin-bottom: 1.5cm;
    }
    
    .header h3 {
        margin: 0;
        font-size: 18px;
    }
    
    .header p {
        margin: 5px 0 0;
        font-size: 14px;
    }
    
    .footer {
        text-align: center;
        margin-top: 1cm;
        font-size: 12px;
    }
    
    .table {
        width: 100%;
        margin: 0;
        border-collapse: collapse;
    }
    
    .table th,
    .table td {
        border: 1px solid #000;
        padding: 5px;
        font-size: 12px;
    }
    
    .table th {
        background-color: #f8f9fa;
        font-weight: bold;
    }
    
    .text-center {
        text-align: center;
    }
    
    .badge {
        display: inline-block;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 12px;
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
        background-color: #dc3545;
        color: white;
    }
</style>

<htmlpageheader name="header">
    <div class="header">
        <h3>Reporte de Producciones</h3>
        <p>Del {{ $fechaInicio->format('d/m/Y') }} al {{ $fechaFin->format('d/m/Y') }}</p>
    </div>
</htmlpageheader>

<htmlpagefooter name="footer">
    <div class="footer">
        Generado el: {{ now()->format('d/m/Y H:i:s') }}
    </div>
</htmlpagefooter>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Producto</th>
                        <th>Receta</th>
                        <th>Área</th>
                        <th>Usuario Responsable</th>
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
                            <td>{{ $produccion->produccionCabecera->usuario->name ?? 'N/A' }}</td>
                            <td class="text-center">{{ number_format($produccion->cantidad_pedido, 2) }}</td>
                            <td class="text-center">{{ number_format($produccion->cantidad_producida_real, 2) }}</td>
                            <td class="text-center">
                                <span class="badge badge-{{ $produccion->es_terminado ? 'success' : ($produccion->es_cancelado ? 'danger' : 'warning') }}">
                                    {{ $produccion->es_terminado ? 'Terminado' : ($produccion->es_cancelado ? 'Cancelado' : 'Pendiente') }}
                                </span>
                            </td>
                            <td class="text-center">{{ number_format($produccion->subtotal_receta, 2) }}</td>
                            <td class="text-center">{{ number_format($produccion->costo_diseño, 2) }}</td>
                            <td class="text-center">{{ number_format($produccion->total_receta, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="text-center">No se encontraron producciones para el período seleccionado</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    @page {
        size: A4 landscape;
        margin: 2cm;
    }
    
    .badge {
        padding: 0.25em 0.6em;
        border-radius: 4px;
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
        background-color: #dc3545;
        color: white;
    }
    
    .table {
        width: 100%;
        margin-bottom: 1rem;
        background-color: transparent;
    }
    
    .table-bordered {
        border: 1px solid #dee2e6;
    }
    
    .table-bordered th,
    .table-bordered td {
        border: 1px solid #dee2e6;
        padding: 0.75rem;
    }
    
    .text-center {
        text-align: center;
    }
</style>

@endsection