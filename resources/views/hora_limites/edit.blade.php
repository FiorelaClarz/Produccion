@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Hora Límite</h1>

    <form action="{{ route('hora-limites.update', $horaLimite->id_hora_limite) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="hora_limite" class="form-label">Hora Límite</label>
            <input type="time" class="form-control" id="hora_limite" name="hora_limite" value="{{ $horaLimite->hora_limite }}" required>
        </div>

        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea class="form-control" id="descripcion" name="descripcion" rows="3">{{ $horaLimite->descripcion }}</textarea>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="status" name="status" value="1" {{ $horaLimite->status ? 'checked' : '' }}>
            <label class="form-check-label" for="status">Activo</label>
        </div>

        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="{{ route('hora-limites.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection