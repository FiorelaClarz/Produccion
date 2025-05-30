@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Detalles del Usuario</h6>
            @if(auth()->user() && auth()->user()->id_roles === 1)
            <a href="{{ route('usuarios.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            @endif
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Información Básica</h5>
                    <hr>
                    <div class="form-group row">
                        <label class="col-md-4 col-form-label">Nombre:</label>
                        <div class="col-md-8">
                            <p class="form-control-plaintext">{{ $usuario->nombre_personal }}</p>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-4 col-form-label">DNI:</label>
                        <div class="col-md-8">
                            <p class="form-control-plaintext">{{ $usuario->dni_personal }}</p>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-4 col-form-label">Tienda:</label>
                        <div class="col-md-8">
                            <p class="form-control-plaintext">{{ $usuario->tienda->nombre ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-4 col-form-label">Área:</label>
                        <div class="col-md-8">
                            <p class="form-control-plaintext">{{ $usuario->area->nombre ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-4 col-form-label">Rol:</label>
                        <div class="col-md-8">
                            <p class="form-control-plaintext">{{ $usuario->rol->nombre ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <h5>Estado y Auditoría</h5>
                    <hr>
                    <div class="form-group row">
                        <label class="col-md-4 col-form-label">Estado:</label>
                        <div class="col-md-8">
                            <span class="badge badge-{{ $usuario->status ? 'success' : 'danger' }}" style="color: black;">
                                {{ $usuario->status ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-4 col-form-label">Fecha Creación:</label>
                        <div class="col-md-8">
                            <p class="form-control-plaintext">{{ $usuario->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-4 col-form-label">Última Actualización:</label>
                        <div class="col-md-8">
                            <p class="form-control-plaintext">{{ $usuario->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-4 col-form-label">Contraseña:</label>
                        <div class="col-md-8">
                            <p class="form-control-plaintext password-placeholder" data-length="{{ strlen($usuario->clave) }}"></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-12 text-right">
                    @if(auth()->user() && auth()->user()->id_roles === 1)
                    <a href="{{ route('usuarios.edit', $usuario->id_usuarios) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Mostrar puntos según la longitud de la contraseña
        $('.password-placeholder').each(function() {
            const length = $(this).data('length');
            $(this).text('•'.repeat(length));
        });
    });
</script>
@endsection