@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Detalle del Turno</h1>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">ID: {{ $turno->id_turnos }}</h5>
            <p class="card-text">
                <strong>Nombre:</strong> {{ $turno->nombre }}<br>
                <strong>Fecha Creación:</strong> {{ $turno->create_date }}<br>
                <strong>Última Actualización:</strong> {{ $turno->last_update }}<br>
                <strong>Estado:</strong> 
                <span class="badge {{ $turno->status ? 'bg-success' : 'bg-secondary' }}">
                    {{ $turno->status ? 'Activo' : 'Inactivo' }}
                </span>
            </p>
            <a href="{{ route('turnos.edit', $turno->id_turnos) }}" class="btn btn-warning">Editar</a>
            <a href="{{ route('turnos.index') }}" class="btn btn-secondary">Volver</a>
        </div>
    </div>
</div>
@endsection