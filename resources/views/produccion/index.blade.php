@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Listado de Producciones</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Producciones Registradas</h6>
            @php
            // Verificar si el usuario tiene un equipo activo con salida registrada
            $usuario = Auth::user();
            $fechaActual = \Carbon\Carbon::now()->toDateString();
            $equipoActivo = \App\Models\EquipoCabecera::where('id_usuarios', $usuario->id_usuarios)
            ->where('status', true)
            ->where('is_deleted', false)
            ->whereDate('created_at', $fechaActual)
            ->first();
            $salidaRegistrada = $equipoActivo && $equipoActivo->salida !== null;
            @endphp

            @if($salidaRegistrada)
            <div>
                <button disabled class="btn btn-secondary btn-sm">
                    <i class="fas fa-plus"></i> Nueva Producción
                </button>
                <span class="text-danger ml-2"><i class="fas fa-info-circle"></i> Ya registró su salida</span>
            </div>
            @else
            <a href="{{ route('produccion.index-personal')}}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Nueva Producción
            </a>
            @endif
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Usuario</th>
                            <th>Turno</th>
                            <th>Equipo</th>
                            <th>Documento</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($producciones as $produccion)
                        <tr>
                            <td>{{ $produccion->id_produccion_cab }}</td>
                            <td>{{ $produccion->fecha }}</td>
                            <td>{{ $produccion->hora }}</td>
                            <td>{{ $produccion->usuario->nombre_personal }}</td>
                            <td>{{ $produccion->turno->nombre }}</td>
                            <td>{{ $produccion->equipo->id_equipos_cab }}</td>
                            <td>{{ $produccion->doc_interno }}</td>
                            <td>
                                <a href="{{ route('produccion.show', $produccion->id_produccion_cab) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('produccion.edit', $produccion->id_produccion_cab) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('produccion.destroy', $produccion->id_produccion_cab) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar esta producción?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
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