@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Crear Nuevo Turno</h1>

    <form action="{{ route('turnos.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre del Turno</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required maxlength="20">
            @error('nombre')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="{{ route('turnos.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection