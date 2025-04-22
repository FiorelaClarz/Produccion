@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Listado de Estados</h1>
    
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <a href="{{ route('estados.create') }}" class="btn btn-primary mb-3">Crear Nuevo Estado</a>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Fecha Creación</th>
                <th>Última Actualización</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($estados as $estado)
                <tr>
                    <td>{{ $estado->id_estados }}</td>
                    <td>{{ $estado->nombre }}</td>
                    <td>{{ $estado->create_date }}</td>
                    <td>{{ $estado->last_update }}</td>
                    <td>
                        <span class="badge {{ $estado->status ? 'bg-success' : 'bg-secondary' }}">
                            {{ $estado->status ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('estados.show', $estado->id_estados) }}" class="btn btn-sm btn-info">Ver</a>
                        <a href="{{ route('estados.edit', $estado->id_estados) }}" class="btn btn-sm btn-warning">Editar</a>
                        <form action="{{ route('estados.destroy', $estado->id_estados) }}" method="POST" style="display: inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de eliminar este estado?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection