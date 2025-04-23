@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Unidades de Medida</h2>
        <a href="{{ route('umedidas.create') }}" class="btn btn-success">
            <i class="fas fa-plus"></i> Nueva Unidad
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($umedidas->count() === 0)
        <div class="alert alert-info">
            No hay unidades registradas.
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
                    @foreach($umedidas as $umedida)
                    <tr class="{{ $umedida->status ? '' : 'table-secondary' }}">
                        <td>{{ $umedida->id_u_medidas }}</td>
                        <td>{{ $umedida->nombre }}</td>
                        <td>{{ $umedida->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</td>
                        <td>{{ $umedida->updated_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</td>
                        <td>
                            @if($umedida->status)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-warning text-dark">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('umedidas.show', $umedida->id_u_medidas) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('umedidas.edit', $umedida->id_u_medidas) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('umedidas.destroy', $umedida->id_u_medidas) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar esta unidad?')">
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