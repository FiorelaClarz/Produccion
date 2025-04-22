@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h3>Detalles de la Tienda</h3>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <label class="col-md-4 col-form-label text-md-end">ID:</label>
                <div class="col-md-6">
                    <p class="form-control-plaintext">{{ $tienda->id_tiendas }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-md-4 col-form-label text-md-end">Nombre:</label>
                <div class="col-md-6">
                    <p class="form-control-plaintext">{{ $tienda->nombre }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-md-4 col-form-label text-md-end">Fecha Creación:</label>
                <div class="col-md-6">
                <p class="form-control-plaintext">{{ $tienda->created_at_datetime ? $tienda->created_at_datetime->format('d/m/Y H:i') : 'N/A' }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-md-4 col-form-label text-md-end">Última Actualización:</label>
                <div class="col-md-6">
                <p class="form-control-plaintext">{{ $tienda->updated_at_datetime ? $tienda->updated_at_datetime->format('d/m/Y H:i') : 'N/A' }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-md-4 col-form-label text-md-end">Estado:</label>
                <div class="col-md-6">
                    <span class="badge {{ $tienda->status ? 'bg-success' : 'bg-secondary' }}">
                        {{ $tienda->status ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>
            </div>

            <div class="row mb-0">
                <div class="col-md-8 offset-md-4">
                    <a href="{{ route('tiendas.edit', $tienda->id_tiendas) }}" class="btn btn-warning">Editar</a>
                    <a href="{{ route('tiendas.index') }}" class="btn btn-secondary">Volver</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection