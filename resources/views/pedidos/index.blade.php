@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Lista de Pedidos</h1>
    
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Pedidos Registrados</span>
            <a href="{{ route('pedidos.create') }}" class="btn btn-primary">Nuevo Pedido</a>
        </div>
        
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Documento</th>
                            <th>Usuario</th>
                            <th>Tienda</th>
                            <th>Fecha/Hora</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pedidos as $pedido)
                        <tr>
                            <td>{{ $pedido->id_pedidos_cab }}</td>
                            <td>{{ $pedido->doc_interno }}</td>
                            <td>{{ $pedido->usuario->name }}</td>
                            <td>{{ $pedido->tienda->nombre }}</td>
                            <td>{{ $pedido->fecha_created }} {{ $pedido->hora_created }}</td>
                            <td>
                                @if($pedido->esta_dentro_de_hora)
                                    <span class="badge bg-success">Dentro de hora</span>
                                @else
                                    <span class="badge bg-danger">Fuera de hora</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('pedidos.show', $pedido->id_pedidos_cab) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('pedidos.edit', $pedido->id_pedidos_cab) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection