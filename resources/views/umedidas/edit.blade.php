@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h2>Editar Unidad: {{ $umedida->nombre }}</h2>
        </div>
        <div class="card-body">
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('umedidas.update', $umedida->id_u_medidas) }}">
                @csrf
                @method('PUT')

                <div class="form-group mb-3">
                    <label for="nombre">Nombre:</label>
                    <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                           id="nombre" name="nombre" value="{{ old('nombre', $umedida->nombre) }}" 
                           required maxlength="45">
                    @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="status" name="status" 
                               value="1" {{ $umedida->status ? 'checked' : '' }}>
                        <label class="form-check-label" for="status">
                            {{ $umedida->status ? 'Activo' : 'Inactivo' }}
                        </label>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('umedidas.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Actualizar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection