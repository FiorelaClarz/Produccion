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
                    <p><strong>Hora de Ingreso:</strong> {{ $produccion->hora }}</p>
                    <p><strong>Hora de Salida:</strong> 
                        @if($produccion->equipo && $produccion->equipo->salida)
                            {{ \Carbon\Carbon::parse($produccion->equipo->salida)->format('d/m/Y H:i:s') }}
                        @else
                            <span class="text-warning">No registrada</span>
                        @endif
                    </p>
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
    
    @php
        // Contador de estados
        $contadorPendientes = 0;
        $contadorTerminados = 0;
        $contadorCancelados = 0;
        $contadorEnProceso = 0;
        
        foreach($produccion->produccionesDetalle as $det) {
            if($det->es_cancelado) {
                $contadorCancelados++;
            } elseif($det->es_terminado) {
                $contadorTerminados++;
            } elseif($det->es_iniciado) {
                $contadorEnProceso++;
            } else {
                $contadorPendientes++;
            }
        }
        
        $totalRegistros = count($produccion->produccionesDetalle);
    @endphp
    
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Resumen de Registros</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col">
                            <h4 class="font-weight-bold">{{ $totalRegistros }}</h4>
                            <p>Total Registros</p>
                        </div>
                        <div class="col">
                            <h4 class="font-weight-bold text-success">{{ $contadorTerminados }}</h4>
                            <p>Terminados</p>
                        </div>
                        <div class="col">
                            <h4 class="font-weight-bold text-primary">{{ $contadorEnProceso }}</h4>
                            <p>En Proceso</p>
                        </div>
                        <div class="col">
                            <h4 class="font-weight-bold text-warning">{{ $contadorPendientes }}</h4>
                            <p>Pendientes</p>
                        </div>
                        <div class="col">
                            <h4 class="font-weight-bold text-danger">{{ $contadorCancelados }}</h4>
                            <p>Cancelados</p>
                        </div>
                    </div>
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
                            <th>#</th>
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
                        @foreach($produccion->produccionesDetalle as $index => $detalle)
                        <tr class="{{ $detalle->es_cancelado ? 'table-secondary' : '' }}">
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $detalle->producto->nombre }}</td>
                            <td>{{ $detalle->recetaCabecera->nombre }}</td>
                            <td>{{ number_format($detalle->cantidad_pedido, 2) }}</td>
                            <td>{{ number_format($detalle->cantidad_esperada, 2) }}</td>
                            <td>{{ number_format($detalle->cantidad_producida_real, 2) }}</td>
                            <td>{{ $detalle->uMedidaProd->nombre }}</td>
                            <td>
                                @if($detalle->es_cancelado)
                                    <span class="badge badge-danger" style="color: black;">Cancelado</span>
                                @elseif($detalle->es_terminado)
                                    <span class="badge badge-success" style="color: black;">Terminado</span>
                                @elseif($detalle->es_iniciado)
                                    <span class="badge badge-primary" style="color: black;">En Proceso</span>
                                @else
                                    <span class="badge badge-secondary" style="color: black;">Pendiente</span>
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