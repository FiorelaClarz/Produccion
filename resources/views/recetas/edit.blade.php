@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Receta</h1>
    
    <form action="{{ route('recetas.update', $receta->id_recetas) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="id_areas">Área</label>
                    <select class="form-control" id="id_areas" name="id_areas" required>
                        <option value="">Seleccione un área</option>
                        @foreach($areas as $area)
                        <option value="{{ $area->id_areas }}" {{ $receta->id_areas == $area->id_areas ? 'selected' : '' }}>
                            {{ $area->nombre }}
                        </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="producto_nombre">Producto</label>
                    <input type="text" class="form-control" id="producto_nombre" 
                           value="{{ $receta->producto->nombre }}" readonly>
                    <input type="hidden" id="id_productos_api" name="id_productos_api" 
                           value="{{ $receta->id_productos_api }}">
                </div>
                
                <div class="form-group">
                    <label for="nombre">Nombre Receta</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" 
                           value="{{ $receta->nombre }}" required>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group">
                    <label for="cant_rendimiento">Rendimiento</label>
                    <input type="number" step="0.01" class="form-control" id="cant_rendimiento" 
                           name="cant_rendimiento" value="{{ $receta->cant_rendimiento }}" required>
                </div>
                
                <div class="form-group">
                    <label for="id_u_medidas">Unidad de Medida</label>
                    <select class="form-control" id="id_u_medidas" name="id_u_medidas" required>
                        <option value="">Seleccione una unidad</option>
                        @foreach($unidades as $unidad)
                        <option value="{{ $unidad->id_u_medidas }}" {{ $receta->id_u_medidas == $unidad->id_u_medidas ? 'selected' : '' }}>
                            {{ $unidad->nombre }}
                        </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="constante_crecimiento">Constante Crecimiento</label>
                            <input type="number" step="0.01" class="form-control" id="constante_crecimiento" 
                                   name="constante_crecimiento" value="{{ $receta->constante_crecimiento }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="constante_peso_lata">Constante Peso Lata</label>
                            <input type="number" step="0.01" class="form-control" id="constante_peso_lata" 
                                   name="constante_peso_lata" value="{{ $receta->constante_peso_lata }}" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Actualizar Receta</button>
            <a href="{{ route('recetas.index') }}" class="btn btn-danger">Cancelar</a>
        </div>
    </form>
</div>
@endsection