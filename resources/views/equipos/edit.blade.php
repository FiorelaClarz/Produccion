@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>Editar Equipo #{{ $equipo->id_equipos_cab }}</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('equipos.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <!-- Errores -->
    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('equipos.update', $equipo->id_equipos_cab) }}" method="POST">
        @csrf
        @method('PUT')
        
        <!-- Información del Equipo -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Información del Equipo</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="id_turnos" class="form-label">Turno *</label>
                    <select class="form-select" id="id_turnos" name="id_turnos" required>
                        <option value="">Seleccione un turno</option>
                        @foreach($turnos as $turno)
                            <option value="{{ $turno->id_turnos }}" 
                                {{ $equipo->id_turnos == $turno->id_turnos ? 'selected' : '' }}>
                                {{ $turno->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Selección de Personal -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Selección de Personal</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Seleccione los miembros del equipo (mínimo 1)</p>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="50px">Seleccionar</th>
                                <th>Código</th>
                                <th>DNI</th>
                                <th>Nombre</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                // Separar personal en seleccionados y no seleccionados
                                $seleccionados = [];
                                $noSeleccionados = [];
                                
                                foreach ($personal as $persona) {
                                    $estaEnEquipo = in_array($persona->id_personal_api, $personalEnEquipo);
                                    $esUsuarioLogueado = $persona->id_personal_api == Auth::user()->id_personal_api;
                                    
                                    if ($estaEnEquipo || $esUsuarioLogueado) {
                                        $seleccionados[] = $persona;
                                    } else {
                                        $noSeleccionados[] = $persona;
                                    }
                                }
                                
                                // Combinar primero los seleccionados
                                $personalOrdenado = array_merge($seleccionados, $noSeleccionados);
                            @endphp
                            
                            @foreach($personalOrdenado as $persona)
                                @php
                                    $estaEnEquipo = in_array($persona->id_personal_api, $personalEnEquipo);
                                    $esUsuarioLogueado = $persona->id_personal_api == Auth::user()->id_personal_api;
                                @endphp
                                <tr>
                                    <td>
                                        <input type="checkbox" name="personal_seleccionado[]" 
                                               value="{{ $persona->id_personal_api }}" 
                                               class="form-check-input"
                                               @if($estaEnEquipo || $esUsuarioLogueado) checked @endif
                                               @if($esUsuarioLogueado) disabled @endif>
                                        @if($esUsuarioLogueado)
                                            <input type="hidden" name="personal_seleccionado[]" value="{{ $persona->id_personal_api }}">
                                        @endif
                                    </td>
                                    <td>{{ $persona->codigo_personal }}</td>
                                    <td>{{ $persona->dni_personal }}</td>
                                    <td>{{ $persona->nombre }}</td>
                                    <td>
                                        @if($esUsuarioLogueado)
                                            <span class="badge bg-primary">Responsable</span>
                                        @elseif($estaEnEquipo)
                                            <span class="badge bg-success">En equipo</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-4 text-end">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Actualizar Equipo
            </button>
        </div>
    </form>
</div>
@endsection