@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Listado de Áreas</h1>
    
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <a href="{{ route('areas.create') }}" class="btn btn-primary mb-3">Crear Nueva Área</a>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Fecha Creación</th>
                <th>Última Actualización</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($areas as $area)
                <tr>
                    <td>{{ $area->id_areas }}</td>
                    <td>{{ $area->nombre }}</td>
                    <td>{{ Str::limit($area->descripcion, 50) }}</td>
                    <td>{{ $area->create_date }}</td>
                    <td>{{ $area->last_update }}</td>
                    <td>
                        <span class="badge {{ $area->status ? 'bg-success' : 'bg-secondary' }}">
                            {{ $area->status ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('areas.show', $area->id_areas) }}" class="btn btn-sm btn-info">Ver</a>
                        <a href="{{ route('areas.edit', $area->id_areas) }}" class="btn btn-sm btn-warning">Editar</a>
                        <form action="{{ route('areas.destroy', $area->id_areas) }}" method="POST" style="display: inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de eliminar esta área?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection