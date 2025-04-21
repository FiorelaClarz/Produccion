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
                        <input type="text" class="form-control" id="nombre_personal" name="nombre_personal" required
                            placeholder="Buscar personal (escribe al menos 2 caracteres)..." autocomplete="off">
                        <div id="personalResults" class="list-group mt-1" style="display:none; position:absolute; z-index:1000; width:100%; max-height:300px; overflow-y:auto;"></div>
                        <input type="hidden" id="id_personal_api" name="id_personal_api">
                        <small class="form-text text-muted">Escribe al menos 2 caracteres para buscar</small>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-3 col-form-label">Tienda</label>
                    <div class="col-md-9">
                        <div class="input-group">
                            <input type="text" class="form-control" id="tienda_nombre" readonly>
                            <input type="hidden" id="id_tiendas_api" name="id_tiendas_api">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" id="cambiarTienda">Cambiar</button>
                            </div>
                        </div>
                        <select class="form-control mt-2" id="tienda_select" name="tienda_select" style="display:none;">
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
                            <input type="text" class="form-control" id="area_nombre" readonly>
                            <input type="hidden" id="id_areas" name="id_areas">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" id="cambiarArea">Cambiar</button>
                            </div>
                        </div>
                        <select class="form-control mt-2" id="area_select" name="area_select" style="display:none;">
                            @foreach($areas as $area)
                            <option value="{{ $area->id_areas }}">{{ $area->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="id_roles" class="col-md-3 col-form-label">Rol</label>
                    <div class="col-md-9">
                        <select class="form-control" id="id_roles" name="id_roles" required>
                            <option value="">Seleccione un rol</option>
                            @foreach($roles as $rol)
                            <option value="{{ $rol->id_roles }}">{{ $rol->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="clave" class="col-md-3 col-form-label">Contraseña</label>
                    <div class="col-md-9">
                        <input type="password" class="form-control" id="clave" name="clave" required minlength="8">
                        <small class="form-text text-muted">Mínimo 8 caracteres</small>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="clave_confirmation" class="col-md-3 col-form-label">Confirmar Contraseña</label>
                    <div class="col-md-9">
                        <input type="password" class="form-control" id="clave_confirmation" name="clave_confirmation" required minlength="8">
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
        // Configuración de búsqueda
        let searchXHR = null;

        // Función para mostrar resultados
        function showResults(data) {
            const container = $('#personalResults').empty();

            if (data.length > 0) {
                data.forEach(item => {
                    container.append(`
                    <a href="#" class="list-group-item list-group-item-action personal-item"
                       data-id="${item.id}"
                       data-nombre="${item.nombre || item.text}"
                       data-tienda-id="${item.tienda_id}"
                       data-tienda-nombre="${item.tienda}"
                       data-area-id="${item.area_id}"
                       data-area-nombre="${item.area}">
                        <strong>${item.nombre || item.text}</strong><br>
                        <small>${item.tienda} - ${item.area}</small>
                    </a>
                `);
                });
                container.show();
            } else {
                container.append('<div class="list-group-item">No se encontraron coincidencias</div>').show();
            }
        }

        // Función para buscar personal
        function searchPersonal(term) {
            if (term.length < 2) {
                $('#personalResults').hide().empty();
                return;
            }

            console.log("Buscando:", term);
            $('#personalResults').html('<div class="list-group-item">Buscando...</div>').show();

            // Cancelar petición anterior si existe
            if (searchXHR) searchXHR.abort();

            searchXHR = $.ajax({
                url: '{{ route("usuarios.buscarPersonal") }}',
                type: 'GET',
                data: {
                    term: term
                },
                dataType: 'json',
                success: function(data) {
                    console.log("Resultados:", data);
                    showResults(data);
                },
                error: function(xhr, status, error) {
                    if (status !== 'abort') {
                        console.error("Error:", error);
                        $('#personalResults').html(
                            '<div class="list-group-item text-danger">Error en la búsqueda</div>'
                        ).show();
                    }
                }
            });
        }

        // Evento de búsqueda con debounce
        let searchTimeout = null;
        $('#nombre_personal').on('input', function() {
            clearTimeout(searchTimeout);
            const term = $(this).val().trim();

            searchTimeout = setTimeout(() => {
                searchPersonal(term);
            }, 300);
        });

        // Seleccionar resultado
        $(document).on('click', '.personal-item', function(e) {
            e.preventDefault();
            const $item = $(this);

            $('#nombre_personal').val($item.data('nombre'));
            $('#id_personal_api').val($item.data('id'));
            $('#tienda_nombre').val($item.data('tienda-nombre'));
            $('#id_tiendas_api').val($item.data('tienda-id'));
            $('#area_nombre').val($item.data('area-nombre'));
            $('#id_areas').val($item.data('area-id'));

            $('#personalResults').hide();
        });

        // Ocultar resultados al hacer clic fuera
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#nombre_personal, #personalResults').length) {
                $('#personalResults').hide();
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
    });
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
</style>
@endsection