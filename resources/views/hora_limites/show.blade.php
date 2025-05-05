@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Detalles de Hora Límite</h1>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">ID: {{ $horaLimite->id_hora_limite }}</h5>
            <p class="card-text"><strong>Hora Límite:</strong> {{ $horaLimite->hora_limite }}</p>
            <p class="card-text"><strong>Descripción:</strong> {{ $horaLimite->descripcion }}</p>
            <p class="card-text">
                <strong>Estado:</strong> 
                @if($horaLimite->status)
                    <span class="badge bg-success">Activo</span>
                @else
                    <span class="badge bg-danger">Inactivo</span>
                @endif
            </p>
            
            <div class="mt-3">
                <a href="{{ route('hora-limites.edit', $horaLimite->id_hora_limite) }}" class="btn btn-warning">Editar</a>
                <form action="{{ route('hora-limites.destroy', $horaLimite->id_hora_limite) }}" method="POST" style="display: inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('¿Estás seguro de eliminar este registro?')">Eliminar</button>
                </form>
                <a href="{{ route('hora-limites.index') }}" class="btn btn-secondary">Volver</a>
            </div>
        </div>
    </div>
</div>
@endsection