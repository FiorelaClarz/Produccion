@extends('layouts.app')

@section('content')
<div class="container">
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Fecha Creación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rols as $rol)
                <tr>
                    <td>{{ $rol->id_roles }}</td>
                    <td>{{ $rol->nombre }}</td>
                    <td>{{ \Carbon\Carbon::parse($rol->create_date)->format('d/m/Y H:i') }}</td>
                    <td>
                        <a href="{{ route('rols.edit', $rol->id_roles) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('rols.destroy', $rol->id_roles) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro?')">
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
@endsection