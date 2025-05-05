@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Lista de Horas Límite</h1>
    <a href="{{ route('hora-limites.create') }}" class="btn btn-primary mb-3">Crear Nueva Hora Límite</a>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Hora Límite</th>
                <th>Descripción</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($horaLimites as $horaLimite)
                <tr>
                    <td>{{ $horaLimite->id_hora_limite }}</td>
                    <td>{{ $horaLimite->hora_limite }}</td>
                    <td>{{ $horaLimite->descripcion }}</td>
                    <td>
                        @if($horaLimite->status)
                            <span class="badge bg-success">Activo</span>
                        @else
                            <span class="badge bg-danger">Inactivo</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('hora-limites.show', $horaLimite->id_hora_limite) }}" class="btn btn-info btn-sm">Ver</a>
                        <a href="{{ route('hora-limites.edit', $horaLimite->id_hora_limite) }}" class="btn btn-warning btn-sm">Editar</a>
                        <form action="{{ route('hora-limites.destroy', $horaLimite->id_hora_limite) }}" method="POST" style="display: inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este registro?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection