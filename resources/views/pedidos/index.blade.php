@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-between mb-4">
        <div class="col-md-6">
            <h2>Listado de Pedidos</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('pedidos.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Pedido
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Documento</th>
                    <th>Usuario</th>
                    <th>Tienda</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Hora Límite</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pedidos as $pedido)
                <tr>
                    <td>{{ $pedido->id_pedidos_cab }}</td>
                    <td>{{ $pedido->doc_interno }}</td>
                    <td>{{ $pedido->usuario->name }}</td>
                    <td>{{ $pedido->tienda->nombre }}</td>
                    <td>{{ \Carbon\Carbon::parse($pedido->fecha_created)->format('d/m/Y') }}</td>
                    <td>{{ $pedido->hora_created }}</td>
                    <td>
                        {{ $pedido->horaLimite->hora_limite }}
                        @if (!$pedido->esta_dentro_de_hora)
                            <span class="badge bg-danger">Expirado</span>
                        @endif
                    </td>
                    <td>
                        @php
                            $estadoGeneral = $pedido->pedidosDetalle->pluck('id_estados')->unique()->count() == 1 
                                ? $pedido->pedidosDetalle->first()->estado->nombre 
                                : 'Mixto';
                        @endphp
                        {{ $estadoGeneral }}
                    </td>
                    <td>
                        <a href="{{ route('pedidos.show', $pedido->id_pedidos_cab) }}" class="btn btn-sm btn-info" title="Ver">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('pedidos.edit', $pedido->id_pedidos_cab) }}" class="btn btn-sm btn-warning" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('pedidos.destroy', $pedido->id_pedidos_cab) }}" method="POST" style="display: inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar este pedido?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center">
        {{ $pedidos->links() }}
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Script para manejar la expiración del tiempo
    document.addEventListener('DOMContentLoaded', function() {
        // Aquí puedes agregar lógica para actualizar el estado de los pedidos expirados
    });
</script>
@endsection