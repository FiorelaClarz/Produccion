@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Usuarios</h6>
            <a href="{{ route('usuarios.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Nuevo Usuario
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Tienda</th>
                            <th>Área</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($usuarios as $usuario)
                        <tr class="{{ $usuario->status ? '' : 'table-secondary' }}">
                            <td>{{ $usuario->nombre_personal }}</td>
                            <td>{{ $usuario->tienda->nombre ?? 'N/A' }}</td>
                            <td>{{ $usuario->area->nombre ?? 'N/A' }}</td>
                            <td>{{ $usuario->rol->nombre ?? 'N/A' }}</td>
                            <td>
                                <span class="badge badge-{{ $usuario->status ? 'success' : 'danger' }}">
                                    {{ $usuario->status ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('usuarios.show', $usuario->id_usuarios) }}" class="btn btn-info btn-sm" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('usuarios.edit', $usuario->id_usuarios) }}" class="btn btn-primary btn-sm" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($usuario->status)
                                <form action="{{ route('usuarios.destroy', $usuario->id_usuarios) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Desactivar">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                </form>
                                @endif
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