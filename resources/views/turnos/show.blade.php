@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>Detalles del Turno</h3>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <label class="col-md-4 col-form-label text-md-end">ID:</label>
                        <div class="col-md-6">
                            <p class="form-control-plaintext">{{ $turno->id_turnos }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-md-4 col-form-label text-md-end">Nombre:</label>
                        <div class="col-md-6">
                            <p class="form-control-plaintext">{{ $turno->nombre }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-md-4 col-form-label text-md-end">Fecha de Creación:</label>
                        <div class="col-md-6">
                            <p class="form-control-plaintext">{{ $turno->create_date }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-md-4 col-form-label text-md-end">Última Actualización:</label>
                        <div class="col-md-6">
                            <p class="form-control-plaintext">{{ $turno->last_update }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-md-4 col-form-label text-md-end">Estado:</label>
                        <div class="col-md-6">
                            <span class="badge {{ $turno->status ? 'bg-success' : 'bg-secondary' }}">
                                {{ $turno->status ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                    </div>

                    <div class="row mb-0">
                        <div class="col-md-8 offset-md-4">
                            <a href="{{ route('turnos.edit', $turno->id_turnos) }}" class="btn btn-primary">
                                Editar
                            </a>
                            <a href="{{ route('turnos.index') }}" class="btn btn-secondary">
                                Volver al Listado
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection