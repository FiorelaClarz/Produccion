@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Nuevo Pedido</h1>

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <span>Información del Pedido</span>
            </div>
        </div>

        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-4">
                    <p><strong>Usuario:</strong> {{ $usuario->nombre_personal }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Tienda:</strong> {{ $usuario->tienda->nombre ?? 'No asignada' }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>Área:</strong> {{ $usuario->area->nombre ?? 'No asignada' }}</p>
                </div>
            </div>

            <form id="pedidoForm" method="POST" action="{{ route('pedidos.store') }}">
                @csrf

                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">Detalles del Pedido</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="id_areas" class="form-label">Área <span class="text-danger">*</span></label>
                                    <select class="form-select" id="id_areas" name="id_areas" required>
                                        <option value="">Seleccione un área</option>
                                        @foreach($areas as $area)
                                        <option value="{{ $area->id_areas }}">{{ $area->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="buscar_receta" class="form-label">Buscar Receta <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="buscar_receta" placeholder="Ingrese al menos 3 caracteres">
                                    <div id="resultados_recetas" class="mt-2 d-none list-group"></div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="cantidad" class="form-label">Cantidad <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0.1" class="form-control" id="cantidad">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="id_u_medidas" class="form-label">Unidad de Medida <span class="text-danger">*</span></label>
                                    <select class="form-select" id="id_u_medidas">
                                        <option value="">Seleccione unidad</option>
                                        @foreach($unidades as $unidad)
                                        <option value="{{ $unidad->id_u_medidas }}">{{ $unidad->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="es_personalizado">
                                    <label class="form-check-label" for="es_personalizado">¿Es personalizado?</label>
                                </div>
                            </div>
                        </div>

                        <div id="personalizado_fields" class="row g-3 d-none mt-2">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="descripcion" class="form-label">Descripción</label>
                                    <textarea class="form-control" id="descripcion" rows="2"></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="foto_referencial_url" class="form-label">URL de Imagen Referencial</label>
                                    <input type="url" class="form-control" id="foto_referencial_url">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <button type="button" id="limpiarBtn" class="btn btn-secondary">
                                <i class="fas fa-broom me-1"></i> Limpiar
                            </button>
                            <button type="button" id="agregarBtn" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> Agregar
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">Lista de Pedidos</div>
                    <div class="card-body">
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-hover" id="tablaDetalles">
                                <thead class="table-light">
                                    <tr>
                                        <th>Área</th>
                                        <th>Receta</th>
                                        <th>Cantidad</th>
                                        <th>Unidad</th>
                                        <th>Personalizado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="detallesBody">
                                    <!-- Aquí se agregarán los detalles dinámicamente -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('pedidos.index') }}" class="btn btn-danger">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-success" id="submitBtn">
                        <i class="fas fa-check me-1"></i> Confirmar Pedido
                    </button>
                </div>

                <input type="hidden" name="detalles" id="detallesInput">
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let detalles = [];
    let editIndex = null;

    // Buscar recetas
    $('#buscar_receta').on('input', function() {
        const termino = $(this).val().trim();
        const id_areas = $('#id_areas').val();
        
        if (termino.length < 3 || !id_areas) {
            $('#resultados_recetas').addClass('d-none');
            return;
        }

        $('#resultados_recetas').html('<div class="list-group-item">Buscando...</div>')
                              .removeClass('d-none');

        $.ajax({
            url: "{{ route('pedidos.buscar-recetas') }}",
            method: 'GET',
            data: { id_areas, termino },
            success: function(response) {
                const resultados = $('#resultados_recetas');
                resultados.empty();
                
                if (response.length > 0) {
                    resultados.removeClass('d-none');
                    response.forEach(receta => {
                        resultados.append(`
                            <div class="list-group-item list-group-item-action receta-item" 
                                data-id="${receta.id}" 
                                data-nombre="${receta.nombre}"
                                data-id_producto="${receta.id_productos_api}"
                                data-id_u_medida="${receta.id_u_medidas}"
                                data-u_medida_nombre="${receta.u_medida_nombre}">
                                <strong>${receta.nombre}</strong> (${receta.u_medida_nombre})
                            </div>
                        `);
                    });
                } else {
                    resultados.html('<div class="list-group-item">No se encontraron recetas</div>')
                              .removeClass('d-none');
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr.responseText);
                $('#resultados_recetas').html('<div class="list-group-item text-danger">Error en la búsqueda</div>')
                                      .removeClass('d-none');
            }
        });
    });

    // Seleccionar receta
    $(document).on('click', '.receta-item', function() {
        const id_recetas = $(this).data('id');
        const nombre = $(this).data('nombre');
        const id_u_medida = $(this).data('id_u_medida');
        
        $('#buscar_receta').val(nombre)
            .data('id_recetas', id_recetas)
            .data('id_productos_api', $(this).data('id_producto'));
            
        $('#id_u_medidas').val(id_u_medida).trigger('change');
        $('#resultados_recetas').addClass('d-none');
    });

    // Mostrar/ocultar campos personalizados
    $('#es_personalizado').change(function() {
        $('#personalizado_fields').toggleClass('d-none', !$(this).is(':checked'));
    });

    // Agregar detalle
    $('#agregarBtn').click(function() {
        // Validar campos básicos
        if (!$('#id_areas').val() || !$('#buscar_receta').val() || !$('#cantidad').val() || !$('#id_u_medidas').val()) {
            alert('Por favor complete todos los campos obligatorios');
            return;
        }

        // Crear objeto detalle
        const detalle = {
            id_areas: $('#id_areas').val(),
            area_nombre: $('#id_areas option:selected').text(),
            id_recetas: $('#buscar_receta').data('id_recetas') || null,
            receta_nombre: $('#buscar_receta').val(),
            cantidad: parseFloat($('#cantidad').val()),
            id_u_medidas: $('#id_u_medidas').val(),
            unidad_nombre: $('#id_u_medidas option:selected').text(),
            es_personalizado: $('#es_personalizado').is(':checked') ? 1 : 0,
            descripcion: $('#es_personalizado').is(':checked') ? $('#descripcion').val() : null,
            foto_referencial_url: $('#es_personalizado').is(':checked') ? $('#foto_referencial_url').val() : null,
            id_productos_api: $('#buscar_receta').data('id_productos_api') || null
        };

        // Editar o agregar
        if (editIndex !== null) {
            detalles[editIndex] = detalle;
            editIndex = null;
            $('#agregarBtn').html('<i class="fas fa-plus me-1"></i> Agregar');
        } else {
            detalles.push(detalle);
        }

        actualizarTablaDetalles();
        limpiarCampos();
    });

    // Limpiar campos
    $('#limpiarBtn').click(limpiarCampos);

    function limpiarCampos() {
        $('#id_areas').val('').trigger('change');
        $('#buscar_receta').val('').removeData('id_recetas').removeData('id_productos_api');
        $('#cantidad').val('');
        $('#id_u_medidas').val('');
        $('#es_personalizado').prop('checked', false);
        $('#personalizado_fields').addClass('d-none');
        $('#descripcion').val('');
        $('#foto_referencial_url').val('');
        $('#resultados_recetas').addClass('d-none').empty();
        
        if (editIndex !== null) {
            $('#agregarBtn').html('<i class="fas fa-plus me-1"></i> Agregar');
            editIndex = null;
        }
    }

    // Actualizar tabla de detalles
    function actualizarTablaDetalles() {
        const tbody = $('#detallesBody');
        tbody.empty();

        if (detalles.length === 0) {
            tbody.append('<tr><td colspan="6" class="text-center">No hay detalles agregados</td></tr>');
        } else {
            detalles.forEach((detalle, index) => {
                tbody.append(`
                    <tr>
                        <td>${detalle.area_nombre}</td>
                        <td>${detalle.receta_nombre}</td>
                        <td>${detalle.cantidad}</td>
                        <td>${detalle.unidad_nombre}</td>
                        <td>${detalle.es_personalizado ? 'Sí' : 'No'}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-warning editar-detalle" data-index="${index}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger eliminar-detalle" data-index="${index}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });
        }

        $('#detallesInput').val(JSON.stringify(detalles));
        $('#submitBtn').prop('disabled', detalles.length === 0);
    }

    // Editar detalle
    $(document).on('click', '.editar-detalle', function() {
        const index = $(this).data('index');
        const detalle = detalles[index];

        $('#id_areas').val(detalle.id_areas).trigger('change');
        $('#buscar_receta').val(detalle.receta_nombre)
            .data('id_recetas', detalle.id_recetas)
            .data('id_productos_api', detalle.id_productos_api);
        $('#cantidad').val(detalle.cantidad);
        $('#id_u_medidas').val(detalle.id_u_medidas);
        $('#es_personalizado').prop('checked', detalle.es_personalizado == 1);

        if (detalle.es_personalizado) {
            $('#personalizado_fields').removeClass('d-none');
            $('#descripcion').val(detalle.descripcion);
            $('#foto_referencial_url').val(detalle.foto_referencial_url);
        }

        $('#agregarBtn').html('<i class="fas fa-save me-1"></i> Actualizar');
        editIndex = index;
    });

    // Eliminar detalle
    $(document).on('click', '.eliminar-detalle', function() {
        const index = $(this).data('index');
        detalles.splice(index, 1);
        actualizarTablaDetalles();
    });

    // Validar antes de enviar
    $('#pedidoForm').submit(function() {
        if (detalles.length === 0) {
            alert('Debe agregar al menos un detalle al pedido');
            return false;
        }
        return true;
    });
});
</script>
@endpush