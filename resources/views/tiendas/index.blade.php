@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Listado de Tiendas</h1>

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <a href="{{ route('tiendas.create') }}" class="btn btn-primary mb-3">Crear Nueva Tienda</a>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            Tiendas Activas
        </div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Fecha Creación</th>
                        <th>Última Actualización</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tiendas->where('status', true) as $tienda)
                    <tr>
                        <td>{{ $tienda->id_tiendas }}</td>
                        <td>{{ $tienda->nombre }}</td>
                        <td>{{ $tienda->created_at_datetime->format('d/m/Y H:i') }}</td>
                        <td>{{ $tienda->updated_at_datetime->format('d/m/Y H:i') }}</td>
                        <td>
                            <a href="{{ route('tiendas.show', $tienda->id_tiendas) }}" class="btn btn-sm btn-info">Ver</a>
                            <a href="{{ route('tiendas.edit', $tienda->id_tiendas) }}" class="btn btn-sm btn-warning">Editar</a>
                            <form action="{{ route('tiendas.destroy', $tienda->id_tiendas) }}" method="POST" style="display: inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de eliminar esta tienda?')">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-secondary text-white">
            Tiendas Inactivas
        </div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Fecha Creación</th>
                        <th>Última Actualización</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tiendas->where('status', false) as $tienda)
                    <tr class="table-secondary">
                        <td>{{ $tienda->id_tiendas }}</td>
                        <td>{{ $tienda->nombre }}</td>
                        <td>{{ $tienda->created_at_datetime ? $tienda->created_at_datetime->format('d/m/Y H:i') : 'N/A' }}</td>
                        <td>{{ $tienda->updated_at_datetime ? $tienda->updated_at_datetime->format('d/m/Y H:i') : 'N/A' }}</td>
                        <td>
                            <a href="{{ route('tiendas.edit', $tienda->id_tiendas) }}" class="btn btn-sm btn-warning">Editar</a>
                            <form action="{{ route('tiendas.destroy', $tienda->id_tiendas) }}" method="POST" style="display: inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de eliminar esta tienda?')">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection