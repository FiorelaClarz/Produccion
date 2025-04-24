@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Crear Nuevo Usuario</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('usuarios.store') }}" method="POST" autocomplete="off">
                @csrf

                <div class="form-group row">
                    <label for="nombre_personal" class="col-md-3 col-form-label">Nombre del Personal</label>
                    <div class="col-md-9">
                        <input type="text" class="form-control @error('nombre_personal') is-invalid @enderror"
                            id="nombre_personal" name="nombre_personal" required
                            placeholder="Buscar personal (escribe al menos 2 caracteres)..." autocomplete="off">
                        @error('nombre_personal')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div id="personalResults" class="list-group mt-1" style="display:none; position:absolute; z-index:1000; width:100%; max-height:300px; overflow-y:auto;"></div>
                        <input type="hidden" id="id_personal_api" name="id_personal_api">
                        <small class="form-text text-muted">Escribe al menos 2 caracteres para buscar</small>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="dni_personal" class="col-md-3 col-form-label">DNI</label>
                    <div class="col-md-9">
                        <input type="text" class="form-control @error('dni_personal') is-invalid @enderror"
                            id="dni_personal" name="dni_personal" required readonly autocomplete="off">
                        @error('dni_personal')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-3 col-form-label">Tienda</label>
                    <div class="col-md-9">
                        <div class="input-group">
                            <input type="text" class="form-control @error('id_tiendas_api') is-invalid @enderror"
                                id="tienda_nombre" readonly>
                            <input type="hidden" id="id_tiendas_api" name="id_tiendas_api">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" id="cambiarTienda">Cambiar</button>
                            </div>
                        </div>
                        @error('id_tiendas_api')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <select class="form-control mt-2 @error('id_tiendas_api') is-invalid @enderror"
                            id="tienda_select" name="tienda_select" style="display:none;">
                            <option value="">Seleccione una tienda</option>
                            @foreach($tiendas as $tienda)
                            <option value="{{ $tienda->id_tiendas }}">{{ $tienda->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-3 col-form-label">Área</label>
                    <div class="col-md-9">
                        <div class="input-group">
                            <input type="text" class="form-control @error('id_areas') is-invalid @enderror"
                                id="area_nombre" readonly>
                            <input type="hidden" id="id_areas" name="id_areas">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" id="cambiarArea">Cambiar</button>
                            </div>
                        </div>
                        @error('id_areas')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <select class="form-control mt-2 @error('id_areas') is-invalid @enderror"
                            id="area_select" name="area_select" style="display:none;">
                            <option value="">Seleccione un área</option>
                            @foreach($areas as $area)
                            <option value="{{ $area->id_areas }}">{{ $area->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="id_roles" class="col-md-3 col-form-label">Rol</label>
                    <div class="col-md-9">
                        <select class="form-control @error('id_roles') is-invalid @enderror"
                            id="id_roles" name="id_roles" required>
                            <option value="">Seleccione un rol</option>
                            @foreach($roles as $rol)
                            <option value="{{ $rol->id_roles }}" {{ old('id_roles') == $rol->id_roles ? 'selected' : '' }}>
                                {{ $rol->nombre }}
                            </option>
                            @endforeach
                        </select>
                        @error('id_roles')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group row">
                    <label for="clave" class="col-md-3 col-form-label">Contraseña</label>
                    <div class="col-md-9">
                        <div class="input-group">
                            <input type="password" class="form-control" id="clave" name="clave" required minlength="8">
                            <div class="input-group-append">
                                <span class="input-group-text toggle-password" data-target="#clave">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                        </div>
                        <small class="form-text text-muted">Mínimo 8 caracteres</small>
                        @error('clave')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-group row">
                    <label for="clave_confirmation" class="col-md-3 col-form-label">Confirmar Contraseña</label>
                    <div class="col-md-9">
                        <div class="input-group">
                            <input type="password" class="form-control" id="clave_confirmation" name="clave_confirmation" required minlength="8">
                            <div class="input-group-append">
                                <span class="input-group-text toggle-password" data-target="#clave_confirmation">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                        </div>
                        <div id="password-match-error" class="text-danger" style="display:none;">
                            Las contraseñas no coinciden
                        </div>
                        @error('clave_confirmation')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-group row mb-0">
                    <div class="col-md-9 offset-md-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar
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

@section('scripts')
<script>
    $(document).ready(function() {
        // Buscador de personal
        new BuscadorAjax({
            inputSelector: '#nombre_personal',
            resultsContainerSelector: '#personalResults',
            endpoint: '{{ route("usuarios.buscarPersonal") }}',
            minChars: 2,
            template: (personal) => `
                <strong>${personal.nombre || personal.text}</strong><br>
                <small>${personal.tienda} - ${personal.area}</small>
            `,
            onSelect: (personal) => {
                // Verificar si ya tiene usuario
                $.get('{{ route("usuarios.verificarPersonal") }}', {
                    id: personal.id
                }).done(response => {
                    if (response.tiene_usuario) {
                        alert('Este personal ya tiene un usuario asociado.');
                        $('#nombre_personal').val('').focus();
                    } else {
                        $('#nombre_personal').val(personal.nombre || personal.text);
                        $('#dni_personal').val(personal.dni_personal);
                        $('#id_personal_api').val(personal.id);
                        $('#tienda_nombre').val(personal.tienda);
                        $('#id_tiendas_api').val(personal.tienda_id);
                        $('#area_nombre').val(personal.area);
                        $('#id_areas').val(personal.area_id);
                    }
                });
            }
        });

        // Cambiar tienda
        $('#cambiarTienda').click(function() {
            $('#tienda_select').toggle();
            $(this).text($('#tienda_select').is(':visible') ? 'Cancelar' : 'Cambiar');
        });

        $('#tienda_select').change(function() {
            const selected = $(this).find('option:selected');
            $('#tienda_nombre').val(selected.text());
            $('#id_tiendas_api').val(selected.val());
        });

        // Cambiar área
        $('#cambiarArea').click(function() {
            $('#area_select').toggle();
            $(this).text($('#area_select').is(':visible') ? 'Cancelar' : 'Cambiar');
        });

        $('#area_select').change(function() {
            const selected = $(this).find('option:selected');
            $('#area_nombre').val(selected.text());
            $('#id_areas').val(selected.val());
        });

        // Función para mostrar/ocultar contraseña
        $(document).on('click', '.toggle-password', function() {
            const target = $(this).data('target');
            const input = $(target);
            const icon = $(this).find('i');

            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                input.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        // Validación en tiempo real de coincidencia de contraseñas
        $('#clave, #clave_confirmation').on('keyup', function() {
            const password = $('#clave').val();
            const confirmPassword = $('#clave_confirmation').val();
            const errorDiv = $('#password-match-error');

            if (password && confirmPassword && password !== confirmPassword) {
                errorDiv.show();
            } else {
                errorDiv.hide();
            }
        });

        // Validación antes de enviar el formulario
        $('form').on('submit', function(e) {
            const password = $('#clave').val();
            const confirmPassword = $('#clave_confirmation').val();

            if (password !== confirmPassword) {
                e.preventDefault();
                $('#password-match-error').show();
                $('html, body').animate({
                    scrollTop: $('#password-match-error').offset().top - 100
                }, 500);
            }
        });

        // Validar al enviar el formulario
        $('form').on('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                // Desplazarse al primer error
                $('html, body').animate({
                    scrollTop: $('.is-invalid').first().offset().top - 100
                }, 500);
            }
        });

        // Validar al cambiar campos
        $('#nombre_personal, #dni_personal, #id_tiendas_api, #id_areas, #id_roles').on('change input', function() {
            validateForm();
        });
    });

    // Validación en tiempo real
    function validateForm() {
        let isValid = true;

        // Validar nombre
        if ($('#nombre_personal').val().trim() === '') {
            $('#nombre_personal').addClass('is-invalid');
            isValid = false;
        } else {
            $('#nombre_personal').removeClass('is-invalid');
        }

        // Validar DNI
        if ($('#dni_personal').val().trim() === '') {
            $('#dni_personal').addClass('is-invalid');
            isValid = false;
        } else {
            $('#dni_personal').removeClass('is-invalid');
        }

        // Validar tienda
        if ($('#id_tiendas_api').val() === '' || $('#id_tiendas_api').val() === null) {
            $('#tienda_nombre').addClass('is-invalid');
            isValid = false;
        } else {
            $('#tienda_nombre').removeClass('is-invalid');
        }

        // Validar área
        if ($('#id_areas').val() === '' || $('#id_areas').val() === null) {
            $('#area_nombre').addClass('is-invalid');
            isValid = false;
        } else {
            $('#area_nombre').removeClass('is-invalid');
        }

        // Validar rol
        if ($('#id_roles').val() === '') {
            $('#id_roles').addClass('is-invalid');
            isValid = false;
        } else {
            $('#id_roles').removeClass('is-invalid');
        }

        return isValid;
    }
</script>

<style>
    #personalResults {
        position: absolute;
        z-index: 1000;
        width: calc(100% - 30px);
        max-height: 300px;
        overflow-y: auto;
        background: white;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    #personalResults .list-group-item {
        border-radius: 0;
        border-left: none;
        border-right: none;
        cursor: pointer;
    }

    #personalResults .list-group-item:hover {
        background-color: #f8f9fa;
    }

    #personalResults .list-group-item:first-child {
        border-top: none;
    }

    #personalResults .list-group-item:last-child {
        border-bottom: none;
    }

    .toggle-password {
        cursor: pointer;
        background-color: #f8f9fa;
        border: 1px solid #ced4da;
        border-left: none;
    }

    .toggle-password:hover {
        background-color: #e9ecef;
    }

    .input-group-text {
        transition: all 0.3s;
    }
</style>
@endsection