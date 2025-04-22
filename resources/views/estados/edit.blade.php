@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Estado</h1>

    <form action="{{ route('estados.update', $estado->id_estados) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre del Estado</label>
            <input type="text" class="form-control" id="nombre" name="nombre" 
                   value="{{ old('nombre', $estado->nombre) }}" required maxlength="45">
            @error('nombre')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Estado</label>
            <select class="form-select" id="status" name="status" required>
                <option value="1" {{ $estado->status ? 'selected' : '' }}>Activo</option>
                <option value="0" {{ !$estado->status ? 'selected' : '' }}>Inactivo</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="{{ route('estados.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection