@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Detalle de Receta</h1>
    
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Información General</h4>
            <div>
                <a href="{{ route('recetas.edit', $receta->id_recetas) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i> Editar Receta
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <p><strong>Área:</strong> {{ $receta->area->nombre }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Producto:</strong> {{ $receta->producto->nombre }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Nombre Receta:</strong> {{ $receta->nombre }}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <p><strong>Rendimiento:</strong> {{ $receta->cant_rendimiento }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Unidad de Medida:</strong> {{ $receta->uMedida->nombre }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Constante Crecimiento:</strong> {{ $receta->constante_crecimiento }}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <p><strong>Constante Peso Lata:</strong> {{ $receta->constante_peso_lata }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Estado:</strong> 
                        <span class="badge badge-{{ $receta->status ? 'success' : 'secondary' }}">
                            {{ $receta->status ? 'Activo' : 'Inactivo' }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <h4>Ingredientes</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Presentación</th>
                            <th>U. Medida</th>
                            <th>Costo Unitario</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($receta->detalles as $detalle)
                        <tr>
                            <td>{{ $detalle->id_productos_api }}</td>
                            <td>{{ $detalle->producto->nombre }}</td>
                            <td>{{ $detalle->cantidad }}</td>
                            <td>{{ $detalle->cant_presentacion }}</td>
                            <td>{{ $detalle->uMedida->nombre }}</td>
                            <td>S/ {{ number_format($detalle->costo_unitario, 2) }}</td>
                            <td>S/ {{ number_format($detalle->subtotal_receta, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6" class="text-right"><strong>Subtotal:</strong></td>
                            <td colspan="1">S/ {{ number_format($receta->detalles->sum('subtotal_receta'), 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4>Instructivo</h4>
        </div>
        <div class="card-body">
        @if($receta->relationLoaded('instructivo') && $receta->instructivo)
    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="{{ route('recetas.view-instructivo', $receta->id_recetas) }}" 
           class="btn btn-info">
            <i class="fas fa-book"></i> Ver Instructivo
        </a>
        <a href="{{ route('recetas.edit-instructivo', ['receta' => $receta->id_recetas, 'instructivo' => $receta->instructivo->id_recetas_instructivos]) }}" 
           class="btn btn-warning">
            <i class="fas fa-edit"></i> Editar Instructivo
        </a>
        <span class="badge badge-primary align-self-center ml-2">
            Versión: {{ $receta->instructivo->version }}
        </span>
    </div>
@else
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i> Esta receta no tiene un instructivo asociado.
    </div>
    <a href="{{ route('recetas.create-instructivo', $receta->id_recetas) }}" 
       class="btn btn-success">
        <i class="fas fa-plus"></i> Crear Instructivo
    </a>
@endif
        </div>
    </div>
    
    <div class="mt-3">
        <a href="{{ route('recetas.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al Listado
        </a>
    </div>
</div>
@endsection

@push('styles')
<style>
    .badge {
        font-size: 0.85em;
        padding: 0.35em 0.65em;
    }
    .table th, .table td {
        vertical-align: middle;
    }
    .alert {
        margin-bottom: 0;
    }
    .gap-2 > * {
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
    }
    .gap-2 > *:last-child {
        margin-right: 0;
    }
</style>
@endpush