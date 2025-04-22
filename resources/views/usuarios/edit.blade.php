@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Editar Usuario</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('usuarios.update', $usuario->id_usuarios) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="form-group row">
                    <label class="col-md-3 col-form-label">Nombre del Personal</label>
                    <div class="col-md-9">
                        <input type="text" class="form-control" value="{{ $usuario->nombre_personal }}" readonly>
                    </div>
                </div>
                
                <div class="form-group row">
                    <label for="id_tiendas_api" class="col-md-3 col-form-label">Tienda</label>
                    <div class="col-md-9">
                        <select class="form-control" id="id_tiendas_api" name="id_tiendas_api" required>
                            @foreach($tiendas as $tienda)
                            <option value="{{ $tienda->id_tiendas }}" {{ $usuario->id_tiendas_api == $tienda->id_tiendas ? 'selected' : '' }}>
                                {{ $tienda->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="form-group row">
                    <label for="id_areas" class="col-md-3 col-form-label">Área</label>
                    <div class="col-md-9">
                        <select class="form-control" id="id_areas" name="id_areas" required>
                            @foreach($areas as $area)
                            <option value="{{ $area->id_areas }}" {{ $usuario->id_areas == $area->id_areas ? 'selected' : '' }}>
                                {{ $area->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="form-group row">
                    <label for="id_roles" class="col-md-3 col-form-label">Rol</label>
                    <div class="col-md-9">
                        <select class="form-control" id="id_roles" name="id_roles" required>
                            @foreach($roles as $rol)
                            <option value="{{ $rol->id_roles }}" {{ $usuario->id_roles == $rol->id_roles ? 'selected' : '' }}>
                                {{ $rol->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                

                
                <div class="form-group row">
                    <label for="status" class="col-md-3 col-form-label">Estado</label>
                    <div class="col-md-9">
                        <select class="form-control" id="status" name="status" required>
                            <option value="1" {{ $usuario->status ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ !$usuario->status ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group row mb-0">
                    <div class="col-md-9 offset-md-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Actualizar
                        </button>
                        <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection