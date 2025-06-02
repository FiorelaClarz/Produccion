@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Nueva Merma</h4>
                        <div class="d-flex align-items-center">
                            <div id="mensaje-guardado-temporal" style="display: none;" class="me-3">
                                <span class="badge bg-success"><i class="fas fa-save me-1"></i> Guardado temporal</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Información de la Merma -->
                    <div class="mb-4">
                        <h5><i class="fas fa-info-circle me-2"></i>Información de la Merma</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><strong><i class="fas fa-user me-1"></i>Usuario:</strong></label>
                                    <p class="form-control-plaintext">{{ Auth::user()->nombre_personal }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><strong><i class="fas fa-store me-1"></i>Tienda:</strong></label>
                                    <p class="form-control-plaintext">{{ Auth::user()->tienda->nombre ?? 'No asignada' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detalles de la Merma -->
                    <div class="mb-4">
                        <h5><i class="fas fa-clipboard-list me-2"></i>Detalles de la Merma</h5>
                        <form id="form-detalle-merma">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="id_areas" class="form-label">
                                            <i class="fas fa-map-marker-alt me-1"></i>Área
                                        </label>
                                        <select class="form-select" id="id_areas" name="id_areas" required>
                                            <option value="">Seleccione un área</option>
                                            @foreach($areas as $area)
                                                <option value="{{ $area->id_areas }}">{{ $area->nombre }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">Por favor seleccione un área</div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="buscar-receta" class="form-label">
                                            <i class="fas fa-search me-1"></i>Buscar Producto
                                        </label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="buscar-receta" placeholder="Buscar por nombre de receta..." autocomplete="off" disabled>
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary" type="button" id="btn-buscar-receta" disabled>
                                                    <i class="fas fa-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div id="sugerencias-recetas" class="list-group position-absolute w-100 z-index-dropdown" style="display: none;"></div>
                                        <input type="hidden" id="id_recetas" name="id_recetas">
                                        <input type="hidden" id="id_productos_api" name="id_productos_api">
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="cantidad" class="form-label">
                                            <i class="fas fa-balance-scale me-1"></i>Cantidad
                                        </label>
                                        <input type="number" class="form-control" id="cantidad" name="cantidad" min="0.01" step="0.01" required disabled>
                                        <div class="invalid-feedback">Ingrese una cantidad válida</div>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="id_u_medidas" class="form-label">
                                            <i class="fas fa-ruler me-1"></i>U. Medida
                                        </label>
                                        <select class="form-select" id="id_u_medidas" name="id_u_medidas" required disabled>
                                            <option value="">Seleccionar</option>
                                            @foreach($uMedidas as $uMedida)
                                                <option value="{{ $uMedida->id_u_medidas }}">{{ $uMedida->nombre }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">Seleccione una unidad</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="obs" class="form-label">
                                            <i class="fas fa-sticky-note me-1"></i>Observación (opcional)
                                        </label>
                                        <textarea class="form-control" id="obs" name="obs" rows="2" disabled></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-12 text-end">
                                    <button type="button" id="btn-agregar-merma" class="btn btn-success" disabled>
                                        <i class="fas fa-plus"></i> Agregar a la lista
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Lista de Mermas -->
                    <div class="mt-4" id="seccion-mermas" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Lista de Mermas</h5>
                            <div>
                                <button type="button" id="btn-guardar-mermas" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Guardar Mermas
                                </button>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th>Área</th>
                                        <th>Receta</th>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Costo</th>
                                        <th>Total</th>
                                        <th>U. Medida</th>
                                        <th>Observación</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="lista-mermas">
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div id="info-sin-mermas" class="text-center py-5">
                        <div class="mb-3">
                            <i class="fas fa-clipboard-list fa-3x text-gray-300"></i>
                        </div>
                        <h5 class="text-gray-500">No hay mermas agregadas</h5>
                        <p class="text-gray-500">Seleccione un área y una receta para comenzar a registrar mermas.</p>
                    </div>
                </div>
                
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('mermas.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Volver
                        </a>
                        <button type="button" id="btn-limpiar-form" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i> Limpiar Formulario
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación de Limpieza -->
<div class="modal fade" id="confirmClearModal" tabindex="-1" role="dialog" aria-labelledby="confirmClearModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="confirmClearModalLabel">Confirmar Limpieza</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea limpiar el formulario? Se perderán todos los datos ingresados.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btn-confirmar-limpiar">Limpiar</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Variables globales
        let mermas = [];
        let recetaSeleccionada = null;
        let timeoutGuardadoTemp = null;
        let editandoIndex = -1; // Índice del elemento que se está editando, -1 si es nuevo
        
        // Cargar datos guardados temporalmente si existen
        cargarDatosTemporales();
        
        // Habilitar búsqueda de recetas cuando se selecciona un área
        $('#id_areas').change(function() {
            const areaId = $(this).val();
            if (areaId) {
                $('#buscar-receta, #btn-buscar-receta').prop('disabled', false);
            } else {
                $('#buscar-receta, #btn-buscar-receta').prop('disabled', true);
                $('#buscar-receta').val('');
                $('#sugerencias-recetas').hide();
            }
        });

        // Búsqueda de recetas
        $('#buscar-receta').on('input', function() {
            const termino = $(this).val().trim();
            const areaId = $('#id_areas').val();
            
            if (termino.length >= 3 && areaId) {
                $.ajax({
                    url: '{{ route("mermas.buscar-recetas") }}',
                    type: 'POST',
                    data: {
                        id_areas: areaId,
                        termino: termino,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(data) {
                        let html = '';
                        
                        if (data.length > 0) {
                            data.forEach(function(receta) {
                                html += `
                                    <a href="#" class="list-group-item list-group-item-action seleccionar-receta" 
                                       data-id="${receta.id}" 
                                       data-nombre="${receta.nombre}"
                                       data-producto-id="${receta.id_productos_api}"
                                       data-producto-nombre="${receta.producto_nombre}"
                                       data-u-medida-id="${receta.id_u_medidas}"
                                       data-u-medida-nombre="${receta.u_medida_nombre}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-bold">${receta.nombre}</div>
                                                <small>${receta.producto_nombre}</small>
                                            </div>
                                            <span class="badge bg-primary">${receta.u_medida_nombre}</span>
                                        </div>
                                    </a>
                                `;
                            });
                        } else {
                            html = '<div class="list-group-item">No se encontraron resultados</div>';
                        }
                        
                        $('#sugerencias-recetas').html(html).show();
                    },
                    error: function() {
                        $('#sugerencias-recetas').html('<div class="list-group-item">Error al buscar recetas</div>').show();
                    }
                });
            } else {
                $('#sugerencias-recetas').hide();
            }
        });

        // Seleccionar receta
        $(document).on('click', '.seleccionar-receta', function(e) {
            e.preventDefault();
            
            const id = $(this).data('id');
            const nombre = $(this).data('nombre');
            const productoId = $(this).data('producto-id');
            const productoNombre = $(this).data('producto-nombre');
            const uMedidaId = $(this).data('u-medida-id');
            
            // Guardar datos de la receta seleccionada
            recetaSeleccionada = {
                id: id,
                nombre: nombre,
                producto_id: productoId,
                producto_nombre: productoNombre,
                u_medida_id: uMedidaId
            };
            
            // Mostrar receta seleccionada
            $('#buscar-receta').val(nombre);
            $('#id_recetas').val(id);
            $('#id_productos_api').val(productoId);
            $('#id_u_medidas').val(uMedidaId);
            
            // Habilitar campos restantes
            $('#cantidad, #id_u_medidas, #obs, #btn-agregar-merma').prop('disabled', false);
            
            // Ocultar sugerencias
            $('#sugerencias-recetas').hide();
        });

        // Click fuera de las sugerencias para ocultarlas
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#buscar-receta, #sugerencias-recetas').length) {
                $('#sugerencias-recetas').hide();
            }
        });

        // Agregar o actualizar merma en la lista
        $('#btn-agregar-merma').click(function() {
            // Validar formulario
            if (!validarFormularioMerma()) {
                return;
            }
            
            // Obtener datos del formulario
            const areaId = $('#id_areas').val();
            const areaNombre = $('#id_areas option:selected').text();
            const recetaId = $('#id_recetas').val();
            const recetaNombre = recetaSeleccionada.nombre;
            const productoId = $('#id_productos_api').val();
            const productoNombre = recetaSeleccionada.producto_nombre;
            const cantidad = $('#cantidad').val();
            const uMedidaId = $('#id_u_medidas').val();
            const uMedidaNombre = $('#id_u_medidas option:selected').text();
            const obs = $('#obs').val();
            
            // Obtener el costo del producto usando AJAX
            $.ajax({
                url: '{{ route("mermas.obtener-costo") }}',
                type: 'POST',
                async: false,
                data: {
                    id_recetas: recetaId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    // Crear objeto merma con costo y total

                    const costo = response.costo || 0;
                      // Aquí se obtiene el costo
                    const total = parseFloat(cantidad) * parseFloat(costo);
                    
                    const merma = {
                        id_areas: areaId,
                        area_nombre: areaNombre,
                        id_recetas: recetaId,
                        receta_nombre: recetaNombre,
                        id_productos_api: productoId,
                        producto_nombre: productoNombre,
                        cantidad: cantidad,
                        costo: costo,
                        total: total,
                        id_u_medidas: uMedidaId,
                        u_medida_nombre: uMedidaNombre,
                        obs: obs
                    };
                    
                    // Si estamos editando, actualizamos el elemento en lugar de añadir uno nuevo
                    if (editandoIndex >= 0) {
                        mermas[editandoIndex] = merma;
                        // Cambiar el botón de vuelta a "Agregar"
                        $('#btn-agregar-merma').html('<i class="fas fa-plus"></i> Agregar');
                        editandoIndex = -1; // Resetear el índice de edición
                    } else {
                        // Agregar a la lista si es un nuevo elemento
                        mermas.push(merma);
                    }
                    
                    // Actualizar tabla
                    actualizarTablaMermas();
                    
                    // Limpiar formulario de detalle
                    limpiarFormularioDetalle();
                    
                    // Guardar en localStorage automáticamente
                    guardarDatosTemporales();
                    mostrarIndicadorGuardado();
                },
                error: function() {
                    // En caso de error, usar valor por defecto
                    const costo = 0;
                    const total = 0;
                    
                    const merma = {
                        id_areas: areaId,
                        area_nombre: areaNombre,
                        id_recetas: recetaId,
                        receta_nombre: recetaNombre,
                        id_productos_api: productoId,
                        producto_nombre: productoNombre,
                        cantidad: cantidad,
                        costo: costo,
                        total: total,
                        id_u_medidas: uMedidaId,
                        u_medida_nombre: uMedidaNombre,
                        obs: obs
                    };
                    
                    // Continuar con el proceso aunque haya error
                    if (editandoIndex >= 0) {
                        mermas[editandoIndex] = merma;
                        $('#btn-agregar-merma').html('<i class="fas fa-plus"></i> Agregar');
                        editandoIndex = -1;
                    } else {
                        mermas.push(merma);
                    }
                    
                    actualizarTablaMermas();
                    limpiarFormularioDetalle();
                    guardarDatosTemporales();
                    mostrarIndicadorGuardado();
                    
                    // Mostrar mensaje de error
                    Swal.fire({
                        title: 'Advertencia',
                        text: 'No se pudo obtener el costo del producto',
                        icon: 'warning',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            });
        });

        // Validar formulario de merma
        function validarFormularioMerma() {
            let esValido = true;
            
            // Validar área
            if (!$('#id_areas').val()) {
                $('#id_areas').addClass('is-invalid');
                esValido = false;
            } else {
                $('#id_areas').removeClass('is-invalid');
            }
            
            // Validar receta
            if (!$('#id_recetas').val()) {
                $('#buscar-receta').addClass('is-invalid');
                esValido = false;
            } else {
                $('#buscar-receta').removeClass('is-invalid');
            }
            
            // Validar cantidad
            if (!$('#cantidad').val() || parseFloat($('#cantidad').val()) <= 0) {
                $('#cantidad').addClass('is-invalid');
                esValido = false;
            } else {
                $('#cantidad').removeClass('is-invalid');
            }
            
            // Validar unidad de medida
            if (!$('#id_u_medidas').val()) {
                $('#id_u_medidas').addClass('is-invalid');
                esValido = false;
            } else {
                $('#id_u_medidas').removeClass('is-invalid');
            }
            
            return esValido;
        }

        // Actualizar tabla de mermas
        function actualizarTablaMermas() {
            let html = '';
            
            mermas.forEach(function(merma, index) {
                // Asegurar que costo y total tengan valores por defecto si no existen
                const costo = merma.costo || 0;
                const total = merma.total || (parseFloat(merma.cantidad) * parseFloat(costo));
                
                html += `
                    <tr>
                        <td>${merma.area_nombre}</td>
                        <td>${merma.receta_nombre}</td>
                        <td>${merma.producto_nombre}</td>
                        <td class="text-right">${parseFloat(merma.cantidad).toFixed(2)}</td>
                        <td class="text-right">${parseFloat(costo).toFixed(2)}</td>
                        <td class="text-right">${parseFloat(total).toFixed(2)}</td>
                        <td>${merma.u_medida_nombre}</td>
                        <td>${merma.obs || '-'}</td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-warning editar-merma" data-index="${index}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger eliminar-merma" data-index="${index}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });
            
            $('#lista-mermas').html(html);
            
            // Mostrar/ocultar secciones según si hay mermas o no
            if (mermas.length > 0) {
                $('#seccion-mermas').show();
                $('#info-sin-mermas').hide();
            } else {
                $('#seccion-mermas').hide();
                $('#info-sin-mermas').show();
            }
        }

        // Eliminar merma de la lista
        $(document).on('click', '.eliminar-merma', function() {
            const index = $(this).data('index');
            mermas.splice(index, 1);
            actualizarTablaMermas();
            guardarDatosTemporales();
            mostrarIndicadorGuardado();
            
            // Si estábamos editando este elemento, volver al modo de agregar
            if (editandoIndex === index) {
                limpiarFormularioDetalle();
                $('#btn-agregar-merma').html('<i class="fas fa-plus"></i> Agregar');
                editandoIndex = -1;
            }
        });
        
        // Editar merma de la lista
        $(document).on('click', '.editar-merma', function() {
            const index = $(this).data('index');
            const merma = mermas[index];
            
            // Llenar el formulario con los datos de la merma seleccionada
            $('#id_areas').val(merma.id_areas);
            
            // Activamos la búsqueda de recetas
            $('#buscar-receta, #btn-buscar-receta').prop('disabled', false);
            
            // Establecer los valores de la receta
            $('#buscar-receta').val(merma.receta_nombre);
            $('#id_recetas').val(merma.id_recetas);
            $('#id_productos_api').val(merma.id_productos_api);
            
            // Guardar datos de la receta seleccionada para acceder después
            recetaSeleccionada = {
                id: merma.id_recetas,
                nombre: merma.receta_nombre,
                producto_id: merma.id_productos_api,
                producto_nombre: merma.producto_nombre,
                u_medida_id: merma.id_u_medidas
            };
            
            // Establecer los demás valores
            $('#cantidad').val(merma.cantidad);
            $('#id_u_medidas').val(merma.id_u_medidas);
            $('#obs').val(merma.obs || '');
            
            // Habilitar todos los campos necesarios
            $('#cantidad, #id_u_medidas, #obs, #btn-agregar-merma').prop('disabled', false);
            
            // Cambiar el texto del botón de Agregar a Actualizar
            $('#btn-agregar-merma').html('<i class="fas fa-save"></i> Actualizar');
            
            // Guardar el índice del elemento que estamos editando
            editandoIndex = index;
            
            // Hacer scroll hasta el formulario
            $('html, body').animate({
                scrollTop: $('#form-detalle-merma').offset().top - 100
            }, 500);
        });

        // Limpiar formulario de detalle
        function limpiarFormularioDetalle() {
            $('#buscar-receta').val('');
            $('#id_recetas').val('');
            $('#id_productos_api').val('');
            $('#cantidad').val('');
            $('#obs').val('');
            
            // Deshabilitar campos
            $('#cantidad, #obs, #btn-agregar-merma').prop('disabled', true);
            
            // Resetear variables globales
            recetaSeleccionada = null;
            editandoIndex = -1;
            
            // Asegurarse de que el botón dice "Agregar"
            $('#btn-agregar-merma').html('<i class="fas fa-plus"></i> Agregar');
        }
        
        // Función para guardar datos temporalmente en localStorage
        function guardarDatosTemporales() {
            const datosTemp = {
                mermas: mermas,
                timestamp: new Date().getTime()
            };
            localStorage.setItem('merma_temp_data', JSON.stringify(datosTemp));
        }
        
        // Función para cargar datos temporales desde localStorage
        function cargarDatosTemporales() {
            const datosGuardados = localStorage.getItem('merma_temp_data');
            if (datosGuardados) {
                try {
                    const datos = JSON.parse(datosGuardados);
                    mermas = datos.mermas || [];
                    actualizarTablaMermas();
                    
                    if (mermas.length > 0) {
                        Swal.fire({
                            title: 'Datos recuperados',
                            text: 'Se han cargado datos guardados anteriormente',
                            icon: 'info',
                            confirmButtonText: 'Continuar'
                        });
                    }
                } catch (e) {
                    console.error('Error al cargar datos temporales:', e);
                }
            }
        }
        
        // Función para mostrar indicador de guardado temporal
        function mostrarIndicadorGuardado() {
            $('#mensaje-guardado-temporal').fadeIn();
            
            if (timeoutGuardadoTemp) {
                clearTimeout(timeoutGuardadoTemp);
            }
            
            timeoutGuardadoTemp = setTimeout(function() {
                $('#mensaje-guardado-temporal').fadeOut();
            }, 2000);
        }

        // Guardar mermas en el servidor
        $('#btn-guardar-mermas').click(function() {
            if (mermas.length === 0) {
                Swal.fire({
                    title: 'Error',
                    text: 'Debe agregar al menos una merma',
                    icon: 'error'
                });
                return;
            }
            
            // Mostrar confirmación
            Swal.fire({
                title: '¿Está seguro?',
                text: 'Se registrarán todas las mermas agregadas.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, guardar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Enviar datos al servidor
                    $.ajax({
                        url: '{{ route("mermas.store") }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            detalles: mermas
                        },
                        beforeSend: function() {
                            Swal.fire({
                                title: 'Guardando...',
                                text: 'Por favor espere',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                        },
                        success: function(response) {
                            if (response.status === 'success') {
                                // Limpiar formulario y localStorage
                                mermas = [];
                                limpiarFormularioDetalle();
                                localStorage.removeItem('merma_temp_data');
                                $('#mensaje-guardado-temporal').hide();
                                
                                Swal.fire({
                                    title: '¡Éxito!',
                                    text: response.message,
                                    icon: 'success'
                                }).then(() => {
                                    window.location.href = response.redirect;
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error',
                                    text: response.message,
                                    icon: 'error'
                                });
                            }
                        },
                        error: function(xhr) {
                            let errorMsg = 'Error al guardar las mermas';
                            
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                const errors = xhr.responseJSON.errors;
                                errorMsg = Object.values(errors).flat().join('<br>');
                            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            
                            Swal.fire({
                                title: 'Error',
                                html: errorMsg,
                                icon: 'error'
                            });
                        }
                    });
                }
            });
        });

        // Guardar datos temporalmente
        function guardarDatosTemporales() {
            localStorage.setItem('merma_temp_data', JSON.stringify(mermas));
            mostrarMensajeGuardadoTemporal();
        }

        // Restaurar datos temporales
        function restaurarDatosTemporales() {
            try {
                const datosGuardados = localStorage.getItem('merma_temp_data');
                
                if (datosGuardados) {
                    mermas = JSON.parse(datosGuardados);
                    actualizarTablaMermas();
                    
                    Swal.fire({
                        title: 'Datos restaurados',
                        text: 'Se han recuperado los datos del formulario guardados previamente.',
                        icon: 'info',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
                
            } catch (e) {
                console.error('Error al restaurar datos:', e);
                localStorage.removeItem('merma_temp_data');
            }
        }

        // Función para mostrar mensaje de guardado temporal
        function mostrarMensajeGuardadoTemporal() {
            $('#mensaje-guardado-temporal').fadeIn().delay(2000).fadeOut();
        }

        // Limpiar formulario completo
        $('#btn-limpiar-form').click(function() {
            $('#confirmClearModal').modal('show');
        });

        // Confirmar limpieza
        $('#btn-confirmar-limpiar').click(function() {
            $('#id_areas').val('').trigger('change');
            limpiarFormularioDetalle();
            mermas = [];
            actualizarTablaMermas();
            localStorage.removeItem('merma_temp_data');
            
            $('#confirmClearModal').modal('hide');
            
            Swal.fire({
                title: 'Formulario limpiado',
                text: 'Se han eliminado todos los datos del formulario.',
                icon: 'success',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        });

        // Restaurar datos al cargar la página
        restaurarDatosTemporales();
    });
</script>
@endpush



