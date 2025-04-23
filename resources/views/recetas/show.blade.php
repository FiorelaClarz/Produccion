@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Detalle de Receta</h1>
    
    <div class="card mb-4">
        <div class="card-header">
            <h4>Información General</h4>
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
                    <p><strong>Estado:</strong> {{ $receta->status ? 'Activo' : 'Inactivo' }}</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
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
                            <td colspan="5" class="text-right"><strong>Subtotal:</strong></td>
                            <td colspan="2">S/ {{ number_format($receta->detalles->sum('subtotal_receta'), 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    
    <div class="mt-3">
        <a href="{{ route('recetas.index') }}" class="btn btn-secondary">Volver</a>
    </div>
</div>
@endsection