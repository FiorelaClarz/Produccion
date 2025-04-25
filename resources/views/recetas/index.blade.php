@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Listado de Recetas</h1>
    <a href="{{ route('recetas.create') }}" class="btn btn-primary mb-3">
        <i class="fas fa-plus"></i> Nueva Receta
    </a>
    
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
                    <th>Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recetas as $receta)
                <tr class="{{ $receta->status ? '' : 'table-secondary' }}">
                    <td>{{ $receta->id_recetas }}</td>
                    <td>{{ $receta->area->nombre }}</td>
                    <td>{{ $receta->producto->nombre }}</td>
                    <td>{{ $receta->nombre }}</td>
                    <td>{{ $receta->cant_rendimiento }}</td>
                    <td>{{ $receta->uMedida->nombre }}</td>
                    <td>
                        @if($receta->status)
                            <span class="badge badge-success" style="color: green;"><i class="fas fa-check-circle" style="color: green;"></i> Activo</span>
                        @else
                            <span class="badge badge-secondary" style="color: gray;"><i class="fas fa-times-circle" style="color: gray;"></i> Inactivo</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="btn-group" role="group">
                            <!-- Ver -->
                            <a href="{{ route('recetas.show', $receta->id_recetas) }}" 
                               class="btn btn-info btn-sm" 
                               title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </a>
                            
                            <!-- Editar -->
                            <a href="{{ route('recetas.edit', $receta->id_recetas) }}" 
                               class="btn btn-warning btn-sm" 
                               title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            
                            <!-- Activar/Desactivar -->
                            <form action="{{ route('recetas.toggle-status', $receta->id_recetas) }}" 
                                  method="POST" 
                                  class="d-inline"
                                  title="{{ $receta->status ? 'Desactivar' : 'Activar' }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $receta->status ? 'btn-secondary' : 'btn-success' }}">
                                    <i class="fas {{ $receta->status ? 'fa-toggle-off' : 'fa-toggle-on' }}"></i>
                                </button>
                            </form>
                            
                            <!-- Eliminar -->
                            <form action="{{ route('recetas.destroy', $receta->id_recetas) }}" 
                                  method="POST" 
                                  class="d-inline"
                                  title="Eliminar">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection