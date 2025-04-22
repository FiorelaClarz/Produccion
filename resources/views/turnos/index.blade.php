@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Listado de Turnos</h1>
    
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <a href="{{ route('turnos.create') }}" class="btn btn-primary mb-3">Crear Nuevo Turno</a>

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
            @foreach($turnos as $turno)
                <tr>
                    <td>{{ $turno->id_turnos }}</td>
                    <td>{{ $turno->nombre }}</td>
                    <td>{{ $turno->create_date }}</td>
                    <td>{{ $turno->last_update }}</td>
                    <td>
                        <span class="badge {{ $turno->status ? 'bg-success' : 'bg-secondary' }}">
                            {{ $turno->status ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('turnos.edit', $turno->id_turnos) }}" class="btn btn-sm btn-warning">Editar</a>
                        <form action="{{ route('turnos.destroy', $turno->id_turnos) }}" method="POST" style="display: inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de eliminar este turno?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection