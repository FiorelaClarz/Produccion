@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Crear Nueva Tienda</h1>

    <form action="{{ route('tiendas.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre de la Tienda</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required maxlength="45">
            @error('nombre')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="codigo_tienda" class="form-label">Código de Tienda</label>
            <input type="text" class="form-control" id="codigo_tienda" name="codigo_tienda" maxlength="10">
            <small class="form-text text-muted">Código de identificación único para la tienda (máximo 10 caracteres)</small>
            @error('codigo_tienda')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="{{ route('tiendas.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection