@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Detalles del Pedido #{{ $pedido->id_pedidos_cab }}</h4>
                </div>

                <div class="card-body">
                    <!-- Información del Pedido -->
                    <div class="mb-4">
                        <h5>Información General</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><strong>Usuario:</strong></label>
                                    <p class="form-control-plaintext">{{ $pedido->usuario->nombre_personal }}</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><strong>Tienda:</strong></label>
                                    <p class="form-control-plaintext">{{ $pedido->usuario->tienda->nombre ?? 'No asignada' }}</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><strong>Fecha/Hora:</strong></label>
                                    <p class="form-control-plaintext">
                                        {{ $pedido->fecha_created }} - {{ $pedido->hora_created }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><strong>Documento Interno:</strong></label>
                                    <p class="form-control-plaintext">{{ $pedido->doc_interno }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><strong>Hora Límite:</strong></label>
                                    <p class="form-control-plaintext">{{ $pedido->hora_limite }}</p> 
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detalles del Pedido -->
                    <div class="mt-4">
                        <h5>Ítems del Pedido</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Área</th>
                                        <th>Receta/Producto</th>
                                        <th>Cantidad</th>
                                        <th>Unidad</th>
                                        <th>Estado</th>
                                        <th>Personalizado</th>
                                        <th>Imagen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pedido->pedidosDetalle as $detalle)
                                    <tr>
                                        <td>{{ $detalle->area->nombre }}</td>
                                        <td>
                                            @if($detalle->receta)
                                                {{ $detalle->receta->nombre }}
                                                @if($detalle->receta->producto)
                                                    <small class="text-muted">({{ $detalle->receta->producto->nombre }})</small>
                                                @endif
                                            @else
                                                Personalizado: {{ $detalle->descripcion }}
                                            @endif
                                        </td>
                                        <td>{{ $detalle->cantidad }}</td>
                                        <td>{{ $detalle->uMedida->nombre ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge {{ $detalle->estado->badge_class ?? 'bg-secondary' }}">
                                                {{ $detalle->estado->nombre ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <i class="fas {{ $detalle->es_personalizado ? 'fa-check text-success' : 'fa-times text-danger' }}"></i>
                                        </td>
                                        <td class="text-center">
                                            @if($detalle->foto_referencial)
                                                <img src="{{ asset('storage/' . $detalle->foto_referencial) }}" 
                                                     class="img-thumbnail" 
                                                     style="width: 50px; height: 50px; object-fit: cover;">
                                            @else
                                                <div class="text-muted">Sin imagen</div>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Botones finales -->
                    <div class="row mt-4">
                        <div class="col-md-12 text-right">
                            <a href="{{ route('pedidos.index') }}" class="btn btn-primary">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .card-header {
        padding: 1rem 1.25rem;
    }
    .form-control-plaintext {
        padding-top: calc(.375rem + 1px);
        padding-bottom: calc(.375rem + 1px);
        margin-bottom: 0;
        line-height: 1.5;
        background-color: #f8f9fa;
        border-radius: .25rem;
        padding-left: .75rem;
    }
    .table th {
        white-space: nowrap;
    }
</style>
@endsection