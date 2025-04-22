@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>Detalles del Estado</h3>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <label class="col-md-4 col-form-label text-md-end">ID:</label>
                        <div class="col-md-6">
                            <p class="form-control-plaintext">{{ $estado->id_estados }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-md-4 col-form-label text-md-end">Nombre:</label>
                        <div class="col-md-6">
                            <p class="form-control-plaintext">{{ $estado->nombre }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-md-4 col-form-label text-md-end">Fecha de Creación:</label>
                        <div class="col-md-6">
                            <p class="form-control-plaintext">{{ $estado->create_date }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-md-4 col-form-label text-md-end">Última Actualización:</label>
                        <div class="col-md-6">
                            <p class="form-control-plaintext">{{ $estado->last_update }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-md-4 col-form-label text-md-end">Estado:</label>
                        <div class="col-md-6">
                            <span class="badge {{ $estado->status ? 'bg-success' : 'bg-secondary' }}">
                                {{ $estado->status ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                    </div>

                    <div class="row mb-0">
                        <div class="col-md-8 offset-md-4">
                            <a href="{{ route('estados.edit', $estado->id_estados) }}" class="btn btn-primary">
                                Editar
                            </a>
                            <a href="{{ route('estados.index') }}" class="btn btn-secondary">
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