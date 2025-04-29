@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>Detalles del Equipo #{{ $equipo->id_equipos_cab }}</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('equipos.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Información General</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <p><strong>Responsable:</strong> {{ $equipo->usuario->nombre_personal }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Área:</strong> {{ $equipo->area->nombre }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Turno:</strong> {{ $equipo->turno->nombre }}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <p><strong>Estado:</strong> 
                        @if($equipo->status)
                            <span class="badge bg-success">Activo</span>
                        @else
                            <span class="badge bg-danger">Inactivo</span>
                        @endif
                    </p>
                </div>
                <div class="col-md-4">
                    <p><strong>Creado:</strong> {{ $equipo->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Salida registrada:</strong> 
                        @if($equipo->salida)
                            {{ \Carbon\Carbon::parse($equipo->salida)->format('d/m/Y H:i') }}
                        @else
                            <span class="text-muted">No registrada</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Miembros del Equipo</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>DNI</th>
                            <th>Nombre</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($equipo->equiposDetalle as $detalle)
                            <tr>
                                <td>{{ $detalle->personal->codigo_personal }}</td>
                                <td>{{ $detalle->personal->dni_personal }}</td>
                                <td>{{ $detalle->personal->nombre }}</td>
                                <td>
                                    @if($detalle->status && !$detalle->is_deleted)
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-danger">Inactivo</span>
                                    @endif
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