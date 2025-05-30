@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-sign-out-alt mr-2"></i>Confirmar Registro de Salida
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        Estás a punto de registrar la hora de salida para este equipo de trabajo. Esta acción marcará el fin de la jornada laboral.
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Información del Equipo</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Responsable:</strong> {{ $equipo->usuario->nombre }} {{ $equipo->usuario->apellido }}</p>
                                    <p><strong>Área:</strong> {{ $equipo->area->nombre }}</p>
                                    <p><strong>Turno:</strong> {{ $equipo->turno->nombre }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Fecha de inicio:</strong> {{ \Carbon\Carbon::parse($equipo->created_at)->format('d/m/Y H:i') }}</p>
                                    <p><strong>Tiempo transcurrido:</strong> {{ \Carbon\Carbon::parse($equipo->created_at)->diffForHumans() }}</p>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <h6 class="mb-3">Miembros del equipo:</h6>
                            <ul class="list-group">
                                @foreach($equipo->equiposDetalle as $detalle)
                                <li class="list-group-item">
                                    <i class="fas fa-user mr-2"></i>
                                    {{ $detalle->personal->nombre }} {{ $detalle->personal->apellido }}
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <form action="{{ route('equipos.registrar-salida', $equipo->id_equipos_cab) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check mr-2"></i>Confirmar Registro de Salida
                            </button>
                            <a href="{{ route('produccion.index') }}" class="btn btn-outline-secondary ml-2">
                                <i class="fas fa-times mr-2"></i>Cancelar
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
