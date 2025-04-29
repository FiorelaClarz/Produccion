@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Detalles del Pedido #{{ $pedido->doc_interno }}</h1>
    
    <div class="card mb-4">
        <div class="card-header">
            Información General
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Usuario:</strong> {{ $pedido->usuario->name }}</p>
                    <p><strong>Tienda:</strong> {{ $pedido->tienda->nombre }}</p>
                    <p><strong>Hora Límite:</strong> {{ $pedido->horaLimite->hora_limite }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Fecha/Hora creación:</strong> {{ $pedido->fecha_created }} {{ $pedido->hora_created }}</p>
                    <p><strong>Última actualización:</strong> {{ $pedido->fecha_last_update }} {{ $pedido->hora_last_update }}</p>
                    <p>
                        <strong>Estado:</strong> 
                        @if($pedido->esta_dentro_de_hora)
                            <span class="badge bg-success">Dentro de hora</span>
                        @else
                            <span class="badge bg-danger">Fuera de hora</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            Detalles del Pedido
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Área</th>
                            <th>Receta/Producto</th>
                            <th>Cantidad</th>
                            <th>Unidad</th>
                            <th>Estado</th>
                            <th>Personalizado</th>
                            <th>Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pedido->pedidosDetalle as $detalle)
                        <tr>
                            <td>{{ $detalle->area->nombre }}</td>
                            <td>
                                @if($detalle->receta)
                                    {{ $detalle->receta->nombre }}
                                @elseif($detalle->producto)
                                    {{ $detalle->producto->nombre }}
                                @else
                                    Producto no especificado
                                @endif
                            </td>
                            <td>{{ $detalle->cantidad }}</td>
                            <td>{{ $detalle->uMedida->nombre }}</td>
                            <td>
                                <span class="badge {{ getEstadoBadgeClass($detalle->estado->id_estados) }}">
                                    {{ $detalle->estado->nombre }}
                                </span>
                            </td>
                            <td>{{ $detalle->es_personalizado ? 'Sí' : 'No' }}</td>
                            <td>{{ $detalle->descripcion }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('pedidos.index') }}" class="btn btn-secondary">Volver</a>
        </div>
    </div>
</div>
@endsection

@php
function getEstadoBadgeClass($id_estado) {
    switch($id_estado) {
        case 2: return 'bg-light text-dark'; // Pendiente
        case 3: return 'bg-info'; // Procesando
        case 4: return 'bg-success'; // Terminado
        case 5: return 'bg-danger'; // Cancelado
        default: return 'bg-secondary';
    }
}
@endphp