@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Crear Nueva Producción</h1>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Datos de Producción</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('produccion.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_equipos">Equipo</label>
                            <select name="id_equipos" id="id_equipos" class="form-control" required>
                                <option value="">Seleccione un equipo</option>
                                @foreach($equipos as $equipo)
                                <option value="{{ $equipo->id_equipos }}" {{ old('id_equipos') == $equipo->id_equipos ? 'selected' : '' }}>
                                    {{ $equipo->nombre }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_turnos">Turno</label>
                            <select name="id_turnos" id="id_turnos" class="form-control" required>
                                <option value="">Seleccione un turno</option>
                                @foreach($turnos as $turno)
                                <option value="{{ $turno->id_turnos }}" {{ old('id_turnos') == $turno->id_turnos ? 'selected' : '' }}>
                                    {{ $turno->nombre }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="fecha">Fecha</label>
                            <input type="date" name="fecha" id="fecha" class="form-control" 
                                   value="{{ old('fecha', date('Y-m-d')) }}" required>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="hora">Hora</label>
                            <input type="time" name="hora" id="hora" class="form-control" 
                                   value="{{ old('hora', date('H:i')) }}" required>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="doc_interno">Documento Interno</label>
                            <input type="text" name="doc_interno" id="doc_interno" class="form-control" 
                                   value="{{ old('doc_interno') }}">
                        </div>
                    </div>
                </div>
                
                <div class="form-group mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                    <a href="{{ route('produccion.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection