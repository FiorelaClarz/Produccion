@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Crear Nueva Hora Límite</h1>

    <form action="{{ route('hora-limites.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="hora_limite" class="form-label">Hora Límite</label>
            <input type="time" class="form-control" id="hora_limite" name="hora_limite" required>
        </div>

        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="status" name="status" value="1" checked>
            <label class="form-check-label" for="status">Activo</label>
        </div>

        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="{{ route('hora-limites.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection