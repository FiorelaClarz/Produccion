@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Listado de Recetas</h1>
    <a href="{{ route('recetas.create') }}" class="btn btn-primary mb-3">Nueva Receta</a>
    
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Área</th>
                    <th>Producto</th>
                    <th>Nombre Receta</th>
                    <th>Rendimiento</th>
                    <th>U. Medida</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recetas as $receta)
                <tr>
                    <td>{{ $receta->id_recetas }}</td>
                    <td>{{ $receta->area->nombre }}</td>
                    <td>{{ $receta->producto->nombre }}</td>
                    <td>{{ $receta->nombre }}</td>
                    <td>{{ $receta->cant_rendimiento }}</td>
                    <td>{{ $receta->uMedida->nombre }}</td>
                    <td>
                        <a href="{{ route('recetas.show', $receta->id_recetas) }}" class="btn btn-info btn-sm">Ver</a>
                        <a href="{{ route('recetas.edit', $receta->id_recetas) }}" class="btn btn-warning btn-sm">Editar</a>
                        <form action="{{ route('recetas.destroy', $receta->id_recetas) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection