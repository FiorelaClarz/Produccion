@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Crear Nuevo Rol</h2>
    
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('rols.store') }}">
        @csrf
        
        <div class="form-group">
            <label for="nombre">Nombre del Rol:</label>
            <input type="text" class="form-control" id="nombre" name="nombre" 
                   value="{{ old('nombre') }}" required maxlength="45">
            @error('nombre')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">Guardar Rol</button>
    </form>
</div>
@endsection