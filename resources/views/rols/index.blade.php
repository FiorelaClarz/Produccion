@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Listado de Roles</h2>
        <a href="{{ route('rols.create') }}" class="btn btn-success">
            <i class="fas fa-plus"></i> Crear Nuevo Rol
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($rols->count() === 0)  
        <div class="alert alert-info">
            No hay roles registrados.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Fecha Creación</th>
                        <th>Última Actualización</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rols as $rol)
                    <tr class="{{ $rol->status ? '' : 'table-secondary' }}">
                        <td>{{ $rol->id_roles }}</td>
                        <td>{{ $rol->nombre }}</td>
                        <td>
                            @if($rol->created_at)
                                {{ $rol->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                                <small class="text-muted">({{ config('app.timezone') }})</small>
                            @else
                                Fecha no disponible
                            @endif
                        </td>
                        <td>
                            @if($rol->updated_at)
                                {{ $rol->updated_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                            @else
                                Nunca actualizado
                            @endif
                        </td>
                        <td>
                            @if($rol->status)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-warning text-dark">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('rols.edit', $rol->id_roles) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('rols.destroy', $rol->id_roles) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" 
                                        onclick="return confirm('¿Estás seguro de eliminar este rol?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        
    @endif
</div>
@endsection