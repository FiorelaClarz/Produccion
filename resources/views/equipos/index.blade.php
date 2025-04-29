@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-between mb-4">
        <div class="col-md-6">
            <h2>Equipos de mi área</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('equipos.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> Nuevo Equipo
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Responsable</th>
                    <th>Turno</th>
                    <th>Miembros</th>
                    <th>Estado</th>
                    <th>Creado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @php
                // Separar equipos activos e inactivos
                $equiposActivos = $equipos->where('status', true);
                $equiposInactivos = $equipos->where('status', false);
                @endphp

                <!-- Mostrar primero los activos -->
                @foreach($equiposActivos as $equipo)
                <tr>
                    <td>{{ $equipo->id_equipos_cab }}</td>
                    <td>{{ $equipo->usuario->nombre_personal }}</td>
                    <td>{{ $equipo->turno->nombre }}</td>
                    <td>{{ $equipo->equiposDetalle->count() }}</td>
                    <td>
                        <span class="badge bg-success">Activo</span>
                    </td>
                    <td>{{ $equipo->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <div class="d-flex">
                            <a href="{{ route('equipos.show', $equipo->id_equipos_cab) }}"
                                class="btn btn-sm btn-info me-1" title="Ver">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('equipos.edit', $equipo->id_equipos_cab) }}"
                                class="btn btn-sm btn-primary me-1" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if(!$equipo->salida)
                            <form action="{{ route('equipos.registrar-salida', $equipo->id_equipos_cab) }}"
                                method="POST" class="me-1">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-warning" title="Registrar salida">
                                    <i class="fas fa-sign-out-alt"></i>
                                </button>
                            </form>
                            @endif
                            <!-- Para el botón de Desactivar (cuando está activo) -->
                            @if($equipo->status)
                            <form action="{{ route('equipos.toggle-status', $equipo->id_equipos_cab) }}"
                                method="POST" class="me-1">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-warning" title="Desactivar">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                            @else
                            <!-- Para el botón de Activar (cuando está inactivo) -->
                            <form action="{{ route('equipos.toggle-status', $equipo->id_equipos_cab) }}"
                                method="POST" class="me-1">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-success" title="Activar">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            @endif
                            <form action="{{ route('equipos.destroy', $equipo->id_equipos_cab) }}"
                                method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este equipo?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach

                <!-- Mostrar después los inactivos -->
                @foreach($equiposInactivos as $equipo)
                <tr class="table-secondary">
                    <td>{{ $equipo->id_equipos_cab }}</td>
                    <td>{{ $equipo->usuario->nombre_personal }}</td>
                    <td>{{ $equipo->turno->nombre }}</td>
                    <td>{{ $equipo->equiposDetalle->count() }}</td>
                    <td>
                        <span class="badge bg-danger">Inactivo</span>
                    </td>
                    <td>{{ $equipo->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <div class="d-flex">
                            <a href="{{ route('equipos.show', $equipo->id_equipos_cab) }}"
                                class="btn btn-sm btn-info me-1" title="Ver">
                                <i class="fas fa-eye"></i>
                            </a>
                            <form action="{{ route('equipos.toggle-status', $equipo->id_equipos_cab) }}"
                                method="POST" class="me-1">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success" title="Activar">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            <form action="{{ route('equipos.destroy', $equipo->id_equipos_cab) }}"
                                method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este equipo?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                    <i class="fas fa-trash"></i>
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