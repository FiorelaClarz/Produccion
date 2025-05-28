@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Tienda</h1>

    <form action="{{ route('tiendas.update', $tienda->id_tiendas) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre de la Tienda</label>
            <input type="text" class="form-control" id="nombre" name="nombre" 
                   value="{{ old('nombre', $tienda->nombre) }}" required maxlength="45">
            @error('nombre')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="codigo_tienda" class="form-label">Código de Tienda</label>
            <input type="text" class="form-control" id="codigo_tienda" name="codigo_tienda" 
                   value="{{ old('codigo_tienda', $tienda->codigo_tienda) }}" maxlength="10" placeholder="Ej: T01, T02, etc.">
            <small class="text-muted">Código para integración con servicios externos</small>
            @error('codigo_tienda')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Estado</label>
            <select class="form-select" id="status" name="status" required>
                <option value="1" {{ $tienda->status ? 'selected' : '' }}>Activo</option>
                <option value="0" {{ !$tienda->status ? 'selected' : '' }}>Inactivo</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="{{ route('tiendas.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection