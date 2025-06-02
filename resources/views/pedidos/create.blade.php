@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Nuevo Pedido</h4>
                        <div class="d-flex align-items-center">
                            <div id="mensaje-guardado-temporal" style="display: none;" class="me-3">
                                <span class="badge bg-success"><i class="fas fa-save me-1"></i> Guardado temporal</span>
                            </div>
                            <div id="contador-regresivo" class="badge fs-5">
                                <i class="fas fa-clock me-2"></i>
                                <span id="tiempo-restante">--:--</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Información del Pedido -->
                    <div class="mb-4">
                        <h5><i class="fas fa-info-circle me-2"></i>Información del Pedido</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><strong><i class="fas fa-user me-1"></i>Usuario:</strong></label>
                                    <p class="form-control-plaintext">{{ Auth::user()->nombre_personal }}</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><strong><i class="fas fa-store me-1"></i>Tienda:</strong></label>
                                    <p class="form-control-plaintext">{{ Auth::user()->tienda->nombre ?? 'No asignada' }}</p>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label><strong><i class="fas fa-stopwatch me-1"></i>Hora Límite:</strong></label>
                                    <p class="form-control-plaintext">{{ $horaLimite->hora_limite }}</p>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label><strong><i class="fas fa-calendar-alt me-1"></i>Ventana:</strong></label>
                                    <p class="form-control-plaintext">
                                        {{ Carbon\Carbon::parse($horaLimite->hora_limite)->subHour()->format('H:i') }} - {{ $horaLimite->hora_limite }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detalles del Pedido -->
                    <div class="mb-4">
                        <h5><i class="fas fa-clipboard-list me-2"></i>Detalles del Pedido</h5>
                        <form id="form-detalle-pedido">
                            @csrf
                            <input type="hidden" id="id_hora_limite" name="id_hora_limite" value="{{ $horaLimite->id_hora_limite }}">

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
                                            <i class="fas fa-search me-1"></i>Buscar Producto (mínimo 3 caracteres)
                                        </label>
                                        <input type="text" class="form-control" id="buscar-receta" name="buscar-receta"
                                            placeholder="Ingrese nombre de producto..." minlength="3">
                                        <div id="sugerencias-recetas" class="list-group mt-2" style="display:none; position: absolute; z-index: 1000; width: 100%;"></div>
                                        <input type="hidden" id="id_recetas" name="id_recetas">
                                        <input type="hidden" id="id_productos_api" name="id_productos_api">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="cantidad" class="form-label">
                                            <i class="fas fa-balance-scale me-1"></i>Cantidad
                                        </label>
                                        <input type="number" class="form-control" id="cantidad" name="cantidad" 
                                               min="0.1" step="0.1" value="1" required>
                                        <div class="invalid-feedback">Ingrese una cantidad válida</div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="id_u_medidas" class="form-label">
                                            <i class="fas fa-ruler me-1"></i>Unidad
                                        </label>
                                        <select class="form-select" id="id_u_medidas" name="id_u_medidas" required>
                                            <option value="">Seleccione</option>
                                            @foreach($unidades as $umedida)
                                            <option value="{{ $umedida->id_u_medidas }}">{{ $umedida->nombre }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">Seleccione una unidad</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Campos para pedido personalizado -->
                            <div id="campos-personalizado" style="display:none;" class="mt-3">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="descripcion" class="form-label">
                                                <i class="fas fa-align-left me-1"></i>Descripción
                                            </label>
                                            <textarea class="form-control" id="descripcion" name="descripcion" rows="2"></textarea>
                                            <div class="invalid-feedback">La descripción es requerida para pedidos personalizados</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="foto_referencial" class="form-label">
                                                <i class="fas fa-camera me-1"></i>Imagen referencial (opcional)
                                            </label>
                                            <input type="file" class="form-control" id="foto_referencial" name="foto_referencial" accept="image/*">
                                            <small class="form-text text-muted">Formatos: JPEG, PNG, JPG, GIF (Max 2MB)</small>
                                            <div id="preview-container" class="mt-2" style="display:none;">
                                                <img id="preview-image" src="#" alt="Previsualización" class="img-thumbnail" style="max-height: 150px;">
                                                <button type="button" class="btn btn-sm btn-danger mt-2" onclick="eliminarPreview()">
                                                    <i class="fas fa-trash me-1"></i>Eliminar imagen
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="es_personalizado" name="es_personalizado">
                                        <label class="form-check-label" for="es_personalizado">
                                            <i class="fas fa-user-edit me-1"></i>¿Es personalizado?
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <button type="button" id="btn-agregar" class="btn btn-primary">
                                        <i class="fas fa-plus me-1"></i> Agregar
                                    </button>
                                    <button type="button" id="btn-limpiar" class="btn btn-secondary">
                                        <i class="fas fa-broom me-1"></i> Limpiar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Lista de pedidos agregados -->
                    <div class="mt-4">
                        <h5><i class="fas fa-list-ol me-2"></i>Lista de Pedidos</h5>
                        <!-- Mensaje cuando no hay pedidos -->
                        <div id="info-sin-pedidos" class="alert alert-info text-center">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>No hay pedidos agregados</p>
                        </div>
                        <!-- Tabla de pedidos (inicialmente oculta) -->
                        <div id="seccion-pedidos" class="table-responsive" style="display:none;">
                            <table class="table table-bordered table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="20%">Área</th>
                                        <th width="25%">Receta</th>
                                        <th width="10%">Cantidad</th>
                                        <th width="15%">Unidad</th>
                                        <th width="10%">Estado</th>
                                        <th width="10%">Personalizado</th>
                                        <th width="10%">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="lista-pedidos"></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Botones finales -->
                    <div class="row mt-4">
                        <div class="col-md-12 text-end">
                            <button type="button" id="btn-pedir" class="btn btn-success btn-lg">
                                <i class="fas fa-paper-plane me-2"></i> Enviar Pedido
                            </button>
                            <button type="button" id="btn-cancelar" class="btn btn-danger btn-lg">
                                <i class="fas fa-times me-2"></i> Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmación -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="confirmModalLabel">Confirmar Pedido</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Está a punto de enviar el pedido con <span id="cantidad-items">0</span> items.</p>
                <p>¿Desea continuar?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <button type="button" id="btn-confirmar-pedido" class="btn btn-primary">
                    <i class="fas fa-check me-1"></i> Confirmar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Variables globales
        let pedidos = [];
        let hora_limite = '{{ $horaLimite->hora_limite }}';
        let intervaloContador = null;
        let modalConfirmacion = new bootstrap.Modal(document.getElementById('confirmModal'));
        
        // Verificar el parámetro mode en la URL
        const urlParams = new URLSearchParams(window.location.search);
        const mode = urlParams.get('mode');
        
        // Si el modo es 'new', limpiar localStorage y empezar fresco
        if (mode === 'new') {
            console.log('Modo: nuevo pedido - limpiando datos guardados');
            localStorage.removeItem('pedido_temp_data');
            localStorage.removeItem('pedido_temp_count');
        } 
        // Si el modo es 'continue' o no está especificado, intentar restaurar datos
        else {
            console.log('Modo: continuar pedido - restaurando datos guardados');
            restoreFormData();
        }

        // Inicializar el contador regresivo
        function iniciarContadorRegresivo() {
            let [hours, minutes, seconds] = hora_limite.split(':').map(Number);
            const ahora = new Date();
            const horaFin = new Date(
                ahora.getFullYear(),
                ahora.getMonth(),
                ahora.getDate(),
                hours,
                minutes,
                seconds || 0
            );

            if (horaFin < ahora) {
                horaFin.setDate(horaFin.getDate() + 1);
            }

            actualizarContador(ahora, horaFin);

            intervaloContador = setInterval(() => {
                actualizarContador(new Date(), horaFin);
            }, 1000);
        }

        function actualizarContador(ahora, horaFin) {
            const diferencia = horaFin - ahora;

            if (diferencia <= 0) {
                clearInterval(intervaloContador);
                $('#tiempo-restante').text('00:00');
                $('#contador-regresivo').removeClass('bg-primary bg-warning').addClass('bg-danger');

                Swal.fire({
                    title: '¡Tiempo agotado!',
                    text: 'El tiempo para realizar pedidos ha terminado. Será redirigido.',
                    icon: 'error',
                    confirmButtonText: 'Entendido'
                }).then(() => {
                    window.location.href = "{{ route('pedidos.index') }}";
                });
                return;
            }

            const horas = Math.floor(diferencia / (1000 * 60 * 60));
            const minutos = Math.floor((diferencia % (1000 * 60 * 60)) / (1000 * 60));
            const segundos = Math.floor((diferencia % (1000 * 60)) / 1000);

            const tiempoRestante = horas > 0 ?
                `${horas.toString().padStart(2, '0')}:${minutos.toString().padStart(2, '0')}:${segundos.toString().padStart(2, '0')}` :
                `${minutos.toString().padStart(2, '0')}:${segundos.toString().padStart(2, '0')}`;

            $('#tiempo-restante').text(tiempoRestante);

            if (diferencia < (5 * 60 * 1000)) {
                $('#contador-regresivo').removeClass('bg-primary bg-warning').addClass('bg-danger');
            } else if (diferencia < (15 * 60 * 1000)) {
                $('#contador-regresivo').removeClass('bg-primary bg-danger').addClass('bg-warning');
            }
        }

        // Mostrar/ocultar campos personalizados
        $('#es_personalizado').change(function() {
            if ($(this).is(':checked')) {
                $('#campos-personalizado').show();
                $('#descripcion').prop('required', true);
            } else {
                $('#campos-personalizado').hide();
                $('#descripcion').prop('required', false);
                $('#descripcion').val('');
                $('#foto_referencial').val('');
                $('#preview-container').hide();
            }
        });

        // Buscar recetas al escribir
        $('#buscar-receta').on('input', function() {
            const term = $(this).val();
            const id_area = $('#id_areas').val();

            if (term.length >= 3 && id_area) {
                $.get('{{ route("pedidos.buscar-recetas") }}', {
                    id_areas: id_area,
                    termino: term
                }, function(data) {
                    const $sugerencias = $('#sugerencias-recetas');
                    $sugerencias.empty();

                    if (data.length > 0) {
                        data.forEach(function(receta) {
                            $sugerencias.append(`
                                <a href="#" class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="fw-bold">${receta.nombre}</div>
                                            <small class="text-muted">${receta.producto_nombre}</small>
                                        </div>
                                        <span class="badge bg-primary">${receta.u_medida_nombre}</span>
                                        <span class="d-none" 
                                              data-id="${receta.id}" 
                                              data-id-producto="${receta.id_productos_api}"
                                              data-id-u-medida="${receta.id_u_medidas}"
                                              data-u-medida-nombre="${receta.u_medida_nombre}"></span>
                                    </div>
                                </a>
                            `);
                        });
                        $sugerencias.show();
                    } else {
                        $sugerencias.append(`
                            <div class="list-group-item text-muted">
                                No se encontraron recetas coincidentes
                            </div>
                        `);
                        $sugerencias.show();
                    }
                });
            } else {
                $('#sugerencias-recetas').hide();
            }
        });

        // Seleccionar receta de las sugerencias
        $(document).on('click', '#sugerencias-recetas a', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const id_receta = $(this).find('span.d-none').data('id');
            const id_producto = $(this).find('span.d-none').data('id-producto');
            const id_u_medida = $(this).find('span.d-none').data('id-u-medida');
            const u_medida_nombre = $(this).find('span.d-none').data('u-medida-nombre');
            const receta_nombre = $(this).find('.fw-bold').text();

            $('#id_recetas').val(id_receta);
            $('#id_productos_api').val(id_producto);
            $('#buscar-receta').val(receta_nombre);
            $('#id_u_medidas').val(id_u_medida).trigger('change');

            $('#sugerencias-recetas').hide();
        });

        // Previsualizar imagen
        $('#foto_referencial').change(function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#preview-image').attr('src', e.target.result);
                    $('#preview-container').show();
                }
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Eliminar previsualización de imagen
        window.eliminarPreview = function() {
            $('#foto_referencial').val('');
            $('#preview-image').attr('src', '#');
            $('#preview-container').hide();
        }

        // Limpiar formulario
        function limpiarFormulario() {
            $('#form-detalle-pedido')[0].reset();
            $('#campos-personalizado').hide();
            $('#es_personalizado').prop('checked', false);
            $('#sugerencias-recetas').hide();
            $('#preview-container').hide();
            $('.invalid-feedback').hide();
            $('.is-invalid').removeClass('is-invalid');
        }

        $('#btn-limpiar').click(function() {
            limpiarFormulario();
            // Guardar en localStorage después de limpiar
            saveFormData();
        });

        // Validar formulario antes de agregar
        function validarFormulario() {
            let valido = true;
            const form = $('#form-detalle-pedido')[0];
            
            // Validar área
            if (!$('#id_areas').val()) {
                $('#id_areas').addClass('is-invalid');
                valido = false;
            } else {
                $('#id_areas').removeClass('is-invalid');
            }

            // Validar receta (si no es personalizado)
            if (!$('#es_personalizado').is(':checked') && !$('#id_recetas').val()) {
                $('#buscar-receta').addClass('is-invalid');
                valido = false;
            } else {
                $('#buscar-receta').removeClass('is-invalid');
            }

            // Validar cantidad
            if (!$('#cantidad').val() || parseFloat($('#cantidad').val()) <= 0) {
                $('#cantidad').addClass('is-invalid');
                valido = false;
            } else {
                $('#cantidad').removeClass('is-invalid');
            }

            // Validar unidad
            if (!$('#id_u_medidas').val()) {
                $('#id_u_medidas').addClass('is-invalid');
                valido = false;
            } else {
                $('#id_u_medidas').removeClass('is-invalid');
            }

            // Validar descripción si es personalizado
            if ($('#es_personalizado').is(':checked') && !$('#descripcion').val()) {
                $('#descripcion').addClass('is-invalid');
                valido = false;
            } else {
                $('#descripcion').removeClass('is-invalid');
            }

            return valido;
        }

        // Agregar pedido a la lista
        $('#btn-agregar').click(function() {
            if (validarFormulario()) {
                const area = $('#id_areas option:selected').text();
                const receta = $('#buscar-receta').val();
                const cantidad = $('#cantidad').val();
                const unidad = $('#id_u_medidas option:selected').text();
                const es_personalizado = $('#es_personalizado').is(':checked');
                const descripcion = $('#descripcion').val();
                const fotoInput = $('#foto_referencial')[0];
                const fotoFile = fotoInput.files[0];

                const pedido = {
                    id_area: $('#id_areas').val(),
                    area_nombre: area,
                    id_receta: $('#id_recetas').val(),
                    receta_nombre: receta,
                    id_producto: $('#id_productos_api').val(),
                    cantidad: cantidad,
                    id_u_medida: $('#id_u_medidas').val(),
                    u_medida_nombre: unidad,
                    es_personalizado: es_personalizado,
                    descripcion: descripcion,
                    foto_referencial: fotoFile, // Guardamos el archivo directamente
                    foto_referencial_url: null,
                    id_estado: 2 // Pendiente por defecto
                };

                pedidos.push(pedido);
                actualizarTablaPedidos();
                limpiarFormulario();
                // Guardar en localStorage después de agregar
                saveFormData();
            }
        });

        function actualizarTablaPedidos() {
            console.log('Actualizando tabla con', pedidos.length, 'pedidos');
            const $lista = $('#lista-pedidos');
            $lista.empty();

            if (!pedidos || pedidos.length === 0) {
                // Mostrar mensaje de no hay pedidos
                $('#info-sin-pedidos').show();
                $('#seccion-pedidos').hide();
                return;
            }
            
            // Hay pedidos para mostrar
            $('#info-sin-pedidos').hide();
            $('#seccion-pedidos').show();

            pedidos.forEach(function(pedido, index) {
                const estadoColor = getColorEstado(pedido.id_estado);
                const personalizadoIcon = pedido.es_personalizado ?
                    '<i class="fas fa-check text-success"></i>' :
                    '<i class="fas fa-times text-danger"></i>';

                let fotoThumbnail = '<div class="text-muted">Sin imagen</div>';
                
                if (pedido.foto_referencial) {
                    // Nueva imagen subida
                    fotoThumbnail = `
                        <div class="position-relative" style="width: 50px; height: 50px;">
                            <img src="${URL.createObjectURL(pedido.foto_referencial)}" 
                                 class="img-thumbnail" 
                                 style="width: 100%; height: 100%; object-fit: cover;">
                            <button class="btn btn-sm btn-danger position-absolute top-0 end-0 p-0 eliminar-foto" 
                                    style="width: 15px; height: 15px; line-height: 15px;"
                                    data-index="${index}">
                                <i class="fas fa-times" style="font-size: 8px;"></i>
                            </button>
                        </div>`;
                }

                $lista.append(`
                    <tr data-index="${index}">
                        <td>${pedido.area_nombre}</td>
                        <td>${pedido.receta_nombre || 'Personalizado'}</td>
                        <td class="text-end">${pedido.cantidad}</td>
                        <td>${pedido.u_medida_nombre}</td>
                        <td><span class="badge ${estadoColor}">Pendiente</span></td>
                        <td class="text-center">${personalizadoIcon}</td>
                        <td class="text-center">
                            ${fotoThumbnail}
                            <button class="btn btn-sm btn-warning btn-editar" data-index="${index}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger btn-eliminar" data-index="${index}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });
        }

        // Eliminar foto de un pedido
        $(document).on('click', '.eliminar-foto', function(e) {
            e.stopPropagation();
            const index = $(this).data('index');
            pedidos[index].foto_referencial = null;
            actualizarListaPedidos();
        });

        // Obtener color según el estado
        function getColorEstado(id_estado) {
            switch (id_estado) {
                case 2: return 'bg-light text-dark'; // Pendiente
                case 3: return 'bg-info'; // Procesando
                case 4: return 'bg-success'; // Terminado
                case 5: return 'bg-danger'; // Cancelado
                default: return 'bg-secondary';
            }
        }

        // Editar pedido
        $(document).on('click', '.btn-editar', function() {
            const index = $(this).data('index');
            const pedido = pedidos[index];

            // Llenar el formulario con los datos del pedido
            $('#id_areas').val(pedido.id_area).trigger('change');
            $('#id_recetas').val(pedido.id_receta);
            $('#buscar-receta').val(pedido.receta_nombre || '');
            $('#id_productos_api').val(pedido.id_producto || '');
            $('#cantidad').val(pedido.cantidad);
            $('#id_u_medidas').val(pedido.id_u_medida).trigger('change');
            
            if (pedido.es_personalizado) {
                $('#es_personalizado').prop('checked', true).trigger('change');
                $('#descripcion').val(pedido.descripcion || '');
                
                // Mostrar previsualización si hay imagen
                if (pedido.foto_referencial) {
                    $('#preview-image').attr('src', URL.createObjectURL(pedido.foto_referencial));
                    $('#preview-container').show();
                }
            } else {
                $('#es_personalizado').prop('checked', false).trigger('change');
            }

            // Eliminar el pedido de la lista temporal
            pedidos.splice(index, 1);
            actualizarTablaPedidos();
        });

        // Eliminar pedido
        $(document).on('click', '.btn-eliminar', function() {
            const index = $(this).data('index');
            Swal.fire({
                title: '¿Eliminar pedido?',
                text: "Esta acción no se puede deshacer",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    pedidos.splice(index, 1);
                    actualizarTablaPedidos();
                    Swal.fire(
                        'Eliminado!',
                        'El pedido ha sido eliminado.',
                        'success'
                    );
                }
            });
        });

        // Enviar pedido - Mostrar confirmación
        $('#btn-pedir').click(function() {
            if (pedidos.length === 0) {
                Swal.fire({
                    title: 'Error',
                    text: 'Debe agregar al menos un pedido',
                    icon: 'error',
                    confirmButtonText: 'Entendido'
                });
                return;
            }

            $('#cantidad-items').text(pedidos.length);
            modalConfirmacion.show();
        });

        // Confirmar envío del pedido
        $('#btn-confirmar-pedido').click(function() {
            modalConfirmacion.hide();
            enviarPedido();
        });

        // Función para enviar el pedido al servidor
        function enviarPedido() {
            const formData = new FormData();
            formData.append('id_hora_limite', $('#id_hora_limite').val());

            pedidos.forEach((pedido, index) => {
                formData.append(`detalles[${index}][id_areas]`, pedido.id_area);
                formData.append(`detalles[${index}][id_recetas]`, pedido.id_receta || '');
                formData.append(`detalles[${index}][id_productos_api]`, pedido.id_producto || '');
                formData.append(`detalles[${index}][cantidad]`, pedido.cantidad);
                formData.append(`detalles[${index}][id_u_medidas]`, pedido.id_u_medida);
                formData.append(`detalles[${index}][es_personalizado]`, pedido.es_personalizado ? '1' : '0');
                formData.append(`detalles[${index}][descripcion]`, pedido.descripcion || '');
                formData.append(`detalles[${index}][id_estados]`, pedido.id_estado);

                // Agregar la imagen si existe
                if (pedido.foto_referencial) {
                    formData.append(`detalles[${index}][foto_referencial]`, pedido.foto_referencial);
                }
            });

            $.ajax({
                url: '{{ route("pedidos.store") }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    $('#btn-pedir').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Enviando...');
                },
                success: function(response) {
                    if (response.success) {
                        // Forzar la limpieza total del localStorage usando un enfoque definitivo
                        console.log('Pedido enviado exitosamente - iniciando limpieza de localStorage');
                        
                        // 1. Vaciamos primero el localStorage completo
                        for (let i = 0; i < localStorage.length; i++) {
                            const key = localStorage.key(i);
                            console.log('Limpiando clave:', key);
                        }
                        localStorage.clear();
                        
                        // 2. Eliminamos específicamente los datos del pedido temporal
                        localStorage.removeItem('pedido_temp_data');
                        localStorage.removeItem('pedido_temp_count');
                        
                        // 3. Establecemos valores vacíos y luego los eliminamos
                        localStorage.setItem('pedido_temp_data', JSON.stringify({pedidos:[]}));
                        localStorage.setItem('pedido_temp_count', '0');
                        localStorage.removeItem('pedido_temp_data');
                        localStorage.removeItem('pedido_temp_count');
                        
                        // 4. Verificamos que se haya limpiado correctamente
                        const tempData = localStorage.getItem('pedido_temp_data');
                        if (tempData) {
                            console.error('ADVERTENCIA: No se pudo limpiar localStorage. Contenido:', tempData);
                        } else {
                            console.log('Verificación exitosa: localStorage limpiado correctamente');
                        }
                        
                        Swal.fire({
                            title: '¡Éxito!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonText: 'Aceptar'
                        }).then(() => {
                            // Limpiar ABSOLUTAMENTE TODO en localStorage antes de redirigir
                            console.log('Limpiando localStorage antes de redireccionar');
                            
                            // Usar window.localStorage para asegurar acceso completo
                            window.localStorage.clear();
                            
                            // Eliminar items específicos por su nombre
                            window.localStorage.removeItem('pedido_temp_data');
                            window.localStorage.removeItem('pedido_temp_count');
                            
                            // Forzar a una página intermedia que limpia el localStorage
                            if (response.clearStorage) {
                                const cleanerUrl = '{{ route("pedidos.index") }}?clean=1&timestamp=' + new Date().getTime();
                                console.log('Redirigiendo a través de página limpiadora:', cleanerUrl);
                                window.location.href = cleanerUrl;
                            } else {
                                window.location.href = response.redirect;
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: response.message,
                            icon: 'error',
                            confirmButtonText: 'Entendido'
                        });
                        $('#btn-pedir').prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i> Enviar Pedido');
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'Error al enviar el pedido';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    
                    // Preguntar al usuario si desea mantener los datos guardados
                    Swal.fire({
                        title: 'Error',
                        text: errorMsg,
                        icon: 'error',
                        showCancelButton: true,
                        confirmButtonText: 'Intentar de nuevo',
                        cancelButtonText: 'Descartar borrador'
                    }).then((result) => {
                        if (result.dismiss === Swal.DismissReason.cancel) {
                            // Usuario eligió descartar el borrador
                            localStorage.removeItem('pedido_temp_data');
                            localStorage.removeItem('pedido_temp_count');
                            window.location.href = '{{ route("pedidos.index") }}';
                        } else {
                            // Usuario quiere intentarlo de nuevo, mantener los datos
                            $('#btn-pedir').prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i> Enviar Pedido');
                        }
                    });
                }
            });
        }

        // Cancelar pedido
        $('#btn-cancelar').click(function() {
            if (pedidos.length > 0) {
                Swal.fire({
                    title: '¿Cancelar pedido?',
                    text: "Todos los datos no guardados se perderán",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, cancelar',
                    cancelButtonText: 'Continuar editando'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Limpiar localStorage al cancelar
                        localStorage.removeItem('pedido_temp_data');
                        localStorage.removeItem('pedido_temp_count');
                        window.location.href = '{{ route("pedidos.index") }}';
                    }
                });
            } else {
                // Incluso si no hay pedidos, limpiar localStorage
                localStorage.removeItem('pedido_temp_data');
                localStorage.removeItem('pedido_temp_count');
                window.location.href = '{{ route("pedidos.index") }}';
            }
        });

        // Iniciar el contador al cargar la página
        iniciarContadorRegresivo();

        // Función para guardar datos del formulario en localStorage
        function saveFormData() {
            // Obtener los datos del formulario principal
            let formData = {
                'id_areas': $('#id_areas').val(),
                'buscar-receta': $('#buscar-receta').val(),
                'id_recetas': $('#id_recetas').val(),
                'id_productos_api': $('#id_productos_api').val(),
                'cantidad': $('#cantidad').val(),
                'id_u_medidas': $('#id_u_medidas').val(),
                'descripcion': $('#descripcion').val(),
                'es_personalizado': $('#es_personalizado').is(':checked')
            };
            
            // Crear una copia serializable de los pedidos
            // Elimina las propiedades que no se pueden serializar como File objects
            const pedidosSerializables = pedidos.map(pedido => {
                // Crear una copia del pedido sin las propiedades no serializables
                const pedidoCopiado = { ...pedido };
                
                // Eliminar las propiedades no serializables
                if (pedidoCopiado.foto_referencial) {
                    // No podemos guardar el File object directamente
                    delete pedidoCopiado.foto_referencial;
                }
                
                return pedidoCopiado;
            });
            
            // Añadir los pedidos serializables al objeto formData
            formData.pedidos = pedidosSerializables;
            
            // Guardar en localStorage - con una clave que indique que contiene pedidos
            localStorage.setItem('pedido_temp_data', JSON.stringify(formData));
            localStorage.setItem('pedido_temp_count', pedidos.length.toString());
            
            console.log('Datos guardados en localStorage:', formData);
            
            // Mostrar indicador visual de guardado
            mostrarMensajeGuardadoTemporal();
        }
        
        // Función para restaurar datos del formulario desde localStorage
        function restoreFormData() {
            const savedData = localStorage.getItem('pedido_temp_data');
            if (!savedData) {
                console.log('No se encontraron datos guardados');
                return;
            }
            
            try {
                const formData = JSON.parse(savedData);
                console.log('Restaurando datos:', formData);
                
                // Restaurar estado de pedido personalizado primero
                if (formData.es_personalizado) {
                    $('#es_personalizado').prop('checked', true).trigger('change');
                }
                
                // Restaurar valores en los campos (con comprobaciones de seguridad)
                $('#id_areas').val(formData.id_areas || '');
                $('#buscar-receta').val(formData['buscar-receta'] || '');
                $('#id_recetas').val(formData.id_recetas || '');
                $('#id_productos_api').val(formData.id_productos_api || '');
                $('#cantidad').val(formData.cantidad || '1');
                $('#id_u_medidas').val(formData.id_u_medidas || '');
                $('#descripcion').val(formData.descripcion || '');
                
                // IMPORTANTE: Restaurar la lista de pedidos
                if (formData.pedidos && Array.isArray(formData.pedidos) && formData.pedidos.length > 0) {
                    console.log('Restaurando ' + formData.pedidos.length + ' pedidos');
                    
                    // Limpiar el array de pedidos actual y copiar los pedidos guardados
                    pedidos = [];
                    
                    formData.pedidos.forEach(pedidoGuardado => {
                        // Crear una copia profunda del pedido guardado
                        const pedidoRestaurado = JSON.parse(JSON.stringify(pedidoGuardado));
                        pedidos.push(pedidoRestaurado);
                    });
                    
                    // Actualizar inmediatamente la interfaz
                    console.log('Pedidos restaurados:', pedidos);
                    actualizarTablaPedidos();
                    
                    // Verificar que se muestren los pedidos (ocultar mensaje de "no hay pedidos")
                    if (pedidos.length > 0) {
                        setTimeout(() => {
                            $('#info-sin-pedidos').hide();
                            $('#seccion-pedidos').show();
                        }, 500);
                    }
                    
                    // Mostrar notificación de restauración
                    Swal.fire({
                        title: '¡Pedidos restaurados!',
                        text: 'Se han recuperado ' + pedidos.length + ' pedidos guardados anteriormente.',
                        icon: 'success',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                } else {
                    console.log('No hay pedidos para restaurar');
                }
                
            } catch (e) {
                console.error('Error al restaurar datos:', e);
                // Si hay error, limpiar localStorage para evitar problemas futuros
                localStorage.removeItem('pedido_temp_data');
                localStorage.removeItem('pedido_temp_count');
                
                // Informar al usuario del error
                Swal.fire({
                    title: 'Error al restaurar datos',
                    text: 'Hubo un problema al recuperar tus datos guardados. Por favor, comienza de nuevo.',
                    icon: 'error',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 5000
                });
            }
        }
        
        // Función para mostrar mensaje de guardado temporal
        function mostrarMensajeGuardadoTemporal() {
            $('#mensaje-guardado-temporal').fadeIn().delay(2000).fadeOut();
        }

        // Guardar formulario al cambiar cualquier campo
        $('#form-detalle-pedido input, #form-detalle-pedido select, #form-detalle-pedido textarea').on('change', function() {
            saveFormData();
        });
        
        // Guardar al navegar a otra página
        // Guardar datos cuando el usuario sale de la página
        $(window).on('beforeunload', function() {
            saveFormData();
            // No mostrar diálogo de confirmación
            return undefined;
        });
        
        // Verificar y corregir la interfaz según el estado de los pedidos
        setInterval(function() {
            if (pedidos && pedidos.length > 0) {
                console.log('Verificación de pedidos:', pedidos.length);
                // Mostrar la sección de pedidos y actualizar la tabla
                $('#info-sin-pedidos').hide();
                $('#seccion-pedidos').show();
                
                // Asegurarse de que la tabla refleja correctamente los pedidos
                const filasPedidos = $('#lista-pedidos tr').length;
                if (filasPedidos === 0 || (filasPedidos === 1 && $('#lista-pedidos tr:first').find('td[colspan]').length > 0)) {
                    console.log('Actualizando tabla de pedidos forzadamente');
                    actualizarTablaPedidos();
                }
            }
        }, 1000);
    });
</script>

<style>
    /* Estilos para las sugerencias de recetas */
    #sugerencias-recetas {
        max-height: 300px;
        overflow-y: auto;
        box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
        border-radius: 0 0 5px 5px;
    }

    #sugerencias-recetas .list-group-item {
        border-left: none;
        border-right: none;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    #sugerencias-recetas .list-group-item:hover {
        background-color: #f8f9fa;
    }

    #sugerencias-recetas .fw-bold {
        color: #0d6efd;
    }

    #sugerencias-recetas .badge {
        font-size: 0.8em;
    }

    /* Estilo para la previsualización de imagen */
    #preview-container {
        transition: all 0.3s ease;
    }

    #preview-image {
        max-width: 100%;
        max-height: 150px;
        object-fit: contain;
    }

    /* Estilo para el contador regresivo */
    #contador-regresivo {
        transition: all 0.3s ease;
    }

    /* Estilo para los botones de acción */
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }

    /* Estilo para la tabla de pedidos */
    #lista-pedidos tr:hover {
        background-color: rgba(13, 110, 253, 0.05);
    }

    /* Estilo para los mensajes de validación */
    .invalid-feedback {
        display: none;
        font-size: 0.8em;
    }

    .is-invalid {
        border-color: #dc3545;
    }

    .is-invalid ~ .invalid-feedback {
        display: block;
    }
</style>
@endsection