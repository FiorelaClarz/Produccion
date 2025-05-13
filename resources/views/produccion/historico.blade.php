@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Producciones del {{ $request->fecha_inicio }} al {{ $request->fecha_fin }}</h1>

    @foreach($producciones as $fecha => $items)
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">{{ Carbon::parse($fecha)->format('d/m/Y') }}</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Estado</th>
                            <th>Responsable</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $produccion)
                        <tr class="{{ $produccion->es_terminado ? 'terminado-row' : ($produccion->es_cancelado ? 'cancelado-row' : '') }}">
                            <td>{{ $produccion->receta->producto->nombre ?? 'N/A' }}</td>
                            <td>{{ $produccion->cantidad_producida_real }}</td>
                            <td>
                                @if($produccion->es_terminado)
                                    <span class="badge badge-success">Terminado</span>
                                @elseif($produccion->es_cancelado)
                                    <span class="badge badge-danger">Cancelado</span>
                                @else
                                    <span class="badge badge-info">En Proceso</span>
                                @endif
                            </td>
                            <td>{{ $produccion->produccionCabecera->usuario->nombre_personal }}</td>
                            <td>
                                <a href="#" class="btn btn-sm btn-outline-info">Ver Detalles</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection