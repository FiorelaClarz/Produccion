@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header bg-primary py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0 font-weight-bold text-white">
                <i class="fas fa-users mr-2"></i>Listado de Usuarios
            </h5>
            <a href="{{ route('usuarios.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus-circle mr-1"></i> Nuevo Usuario
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center">Nombre</th>
                            <th class="text-center">DNI</th>
                            <th class="text-center">Tienda</th>
                            <th class="text-center">Área</th>
                            <th class="text-center">Rol</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($usuarios as $usuario)
                        <tr class="{{ $usuario->status ? ($loop->even ? 'fila-activa-par' : 'fila-activa-impar') : ($loop->even ? 'fila-inactiva-par' : 'fila-inactiva-impar') }}">
                            <td>
                                <div class="font-weight-bold">{{ $usuario->nombre_personal }}</div>
                                <small class="text-muted">ID: {{ $usuario->id_usuarios }}</small>
                            </td>
                            <td class="text-center">{{ $usuario->dni_personal }}</td>
                            <td class="text-center">{{ $usuario->tienda->nombre ?? 'N/A' }}</td>
                            <td class="text-center">{{ $usuario->area->nombre ?? 'N/A' }}</td>
                            <td class="text-center">{{ $usuario->rol->nombre ?? 'N/A' }}</td>
                            <td class="text-center">
                                <span class="badge {{ $usuario->status ? 'badge-success' : 'badge-danger' }}" style="color: #000 !important;">
                                    <i class="fas fa-circle {{ $usuario->status ? 'text-success' : 'text-danger' }} mr-1"></i>
                                    {{ $usuario->status ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="{{ route('usuarios.show', $usuario->id_usuarios) }}" class="btn btn-info btn-sm" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('usuarios.edit', $usuario->id_usuarios) }}" class="btn btn-primary btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('usuarios.destroy', $usuario->id_usuarios) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Eliminar" onclick="return confirm('¿Estás seguro que deseas eliminar este usuario?')">
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
    </div>
</div>
@endsection

@section('styles')
<style>
    /* Colores para filas activas */
    .fila-activa-par {
        background-color: #f0fdf4;
        /* Verde muy claro */
    }

    .fila-activa-impar {
        background-color: #dcfce7;
        /* Verde claro */
    }

    /* Colores para filas inactivas */
    .fila-inactiva-par {
        background-color: #fef2f2;
        /* Rojo muy claro */
    }

    .fila-inactiva-impar {
        background-color: #fee2e2;
        /* Rojo claro */
    }

    /* Efecto hover */
    .fila-activa-par:hover,
    .fila-activa-impar:hover {
        background-color: #bbf7d0;
        /* Verde más intenso */
    }

    .fila-inactiva-par:hover,
    .fila-inactiva-impar:hover {
        background-color: #fecaca;
        /* Rojo más intenso */
    }

    /* Estilo para el último registro */
    tr:last-child {
        border-left: 3px solid #ff9800;
    }

    /* Texto negro para el estado */
    .badge-success,
    .badge-danger {
        color: #000 !important;
    }

    .btn-danger {
        background-color: #dc3545;
        border-color: #dc3545;
    }

    .btn-danger:hover {
        background-color: #c82333;
        border-color: #bd2130;
    }
</style>
@endsection