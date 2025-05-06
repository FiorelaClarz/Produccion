@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Detalle de Producción #{{ $produccion->id_produccion_cab }}</h1>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Información General</h6>
            <div>
                <a href="{{ route('produccion.edit', $produccion->id_produccion_cab) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i> Editar
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Fecha:</strong> {{ $produccion->fecha }}</p>
                    <p><strong>Hora:</strong> {{ $produccion->hora }}</p>
                    <p><strong>Documento Interno:</strong> {{ $produccion->doc_interno ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Usuario:</strong> {{ $produccion->usuario->nombre_personal }}</p>
                    <p><strong>Turno:</strong> {{ $produccion->turno->nombre }}</p>
                    <p><strong>Equipo:</strong> {{ $produccion->equipo->nombre }}</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Detalles de Producción</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Receta</th>
                            <th>Cant. Pedido</th>
                            <th>Cant. Esperada</th>
                            <th>Cant. Producida</th>
                            <th>Unidad Medida</th>
                            <th>Estado</th>
                            <th>Subtotal</th>
                            <th>Costo Diseño</th>
                            <th>Total</th>
                            <th>Cant. Harina</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($produccion->produccionesDetalle as $detalle)
                        <tr class="{{ $detalle->es_cancelado ? 'table-secondary' : '' }}">
                            <td>{{ $detalle->producto->nombre }}</td>
                            <td>{{ $detalle->recetaCabecera->nombre }}</td>
                            <td>{{ number_format($detalle->cantidad_pedido, 2) }}</td>
                            <td>{{ number_format($detalle->cantidad_esperada, 2) }}</td>
                            <td>{{ number_format($detalle->cantidad_producida_real, 2) }}</td>
                            <td>{{ $detalle->uMedidaProd->nombre }}</td>
                            <td>
                                @if($detalle->es_cancelado)
                                    <span class="badge badge-danger">Cancelado</span>
                                @elseif($detalle->es_terminado)
                                    <span class="badge badge-success">Terminado</span>
                                @elseif($detalle->es_iniciado)
                                    <span class="badge badge-primary">En Proceso</span>
                                @else
                                    <span class="badge badge-secondary">Pendiente</span>
                                @endif
                            </td>
                            <td>{{ number_format($detalle->subtotal_receta, 2) }}</td>
                            <td>{{ number_format($detalle->costo_diseño, 2) }}</td>
                            <td>{{ number_format($detalle->total_receta, 2) }}</td>
                            <td>{{ number_format($detalle->cant_harina, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="text-center mt-3">
        <a href="{{ route('produccion.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al listado
        </a>
    </div>
</div>
@endsection