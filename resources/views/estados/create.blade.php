@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Crear Nuevo Estado</h1>

    <form action="{{ route('estados.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre del Estado</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required maxlength="45">
            @error('nombre')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="{{ route('estados.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection