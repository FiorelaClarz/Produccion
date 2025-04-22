@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h3>Detalles del Área</h3>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <label class="col-md-4 col-form-label text-md-end">ID:</label>
                <div class="col-md-6">
                    <p class="form-control-plaintext">{{ $area->id_areas }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-md-4 col-form-label text-md-end">Nombre:</label>
                <div class="col-md-6">
                    <p class="form-control-plaintext">{{ $area->nombre }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-md-4 col-form-label text-md-end">Descripción:</label>
                <div class="col-md-6">
                    <p class="form-control-plaintext">{{ $area->descripcion }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-md-4 col-form-label text-md-end">Fecha Creación:</label>
                <div class="col-md-6">
                    <p class="form-control-plaintext">{{ $area->create_date }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-md-4 col-form-label text-md-end">Última Actualización:</label>
                <div class="col-md-6">
                    <p class="form-control-plaintext">{{ $area->last_update }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-md-4 col-form-label text-md-end">Estado:</label>
                <div class="col-md-6">
                    <span class="badge {{ $area->status ? 'bg-success' : 'bg-secondary' }}">
                        {{ $area->status ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>
            </div>

            <div class="row mb-0">
                <div class="col-md-8 offset-md-4">
                    <a href="{{ route('areas.edit', $area->id_areas) }}" class="btn btn-warning">Editar</a>
                    <a href="{{ route('areas.index') }}" class="btn btn-secondary">Volver</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection