@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Nuevo Pedido</h4>
                        <div id="contador-regresivo" class="badge fs-5">
                            <i class="fas fa-clock me-2"></i>
                            <span id="tiempo-restante">--:--</span>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Información del Pedido -->
                    <div class="mb-4">
                        <h5>Información del Pedido</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><strong>Usuario:</strong></label>
                                    <p class="form-control-plaintext">{{ Auth::user()->nombre_personal }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><strong>Tienda:</strong></label>
                                    <p class="form-control-plaintext">{{ Auth::user()->tienda->nombre ?? 'No asignada' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detalles del Pedido -->
                    <div class="mb-4">
                        <h5>Detalles del Pedido</h5>
                        <form id="form-detalle-pedido">
                            @csrf
                            <input type="hidden" id="id_hora_limite" name="id_hora_limite" value="{{ $horaLimite->id_hora_limite }}">

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="id_areas">Área</label>
                                        <select class="form-control" id="id_areas" name="id_areas" required>
                                            <option value="">Seleccione un área</option>
                                            @foreach($areas as $area)
                                            <option value="{{ $area->id_areas }}">{{ $area->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="buscar-receta">Buscar Receta (mínimo 3 caracteres)</label>
                                        <input type="text" class="form-control" id="buscar-receta" name="buscar-receta"
                                            placeholder="Ingrese nombre de receta..." minlength="3">
                                        <div id="sugerencias-recetas" class="list-group mt-2" style="display:none;"></div>
                                        <input type="hidden" id="id_recetas" name="id_recetas">
                                        <input type="hidden" id="id_productos_api" name="id_productos_api">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="cantidad">Cantidad</label>
                                        <input type="number" class="form-control" id="cantidad" name="cantidad" min="1" value="1" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="id_u_medidas">Unidad de Medida</label>
                                        <select class="form-control" id="id_u_medidas" name="id_u_medidas" required>
                                            <option value="">Seleccione</opstion>
                                            @foreach($unidades as $umedida)
                                            <option value="{{ $umedida->id_u_medidas }}">{{ $umedida->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Campos para pedido personalizado -->
                            <div id="campos-personalizado" style="display:none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="descripcion">Descripción</label>
                                            <textarea class="form-control" id="descripcion" name="descripcion" rows="2"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="foto_referencial_url">URL de imagen referencial (opcional)</label>
                                            <input type="url" class="form-control" id="foto_referencial_url" name="foto_referencial_url">
                                            <small class="form-text text-muted">Puede subir una imagen más adelante</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="es_personalizado" name="es_personalizado">
                                        <label class="form-check-label" for="es_personalizado">¿Es personalizado?</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <button type="button" id="btn-agregar" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Agregar
                                    </button>
                                    <button type="button" id="btn-limpiar" class="btn btn-secondary">
                                        <i class="fas fa-broom"></i> Limpiar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Lista de pedidos agregados -->
                    <div class="mt-4">
                        <h5>Lista de Pedidos</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Área</th>
                                        <th>Receta</th>
                                        <th>Cantidad</th>
                                        <th>Unidad</th>
                                        <th>Estado</th>
                                        <th>Personalizado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="lista-pedidos">
                                    <!-- Aquí se agregarán dinámicamente los pedidos -->
                                    <tr>
                                        <td colspan="7" class="text-center">No hay pedidos agregados</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Botones finales -->
                    <div class="row mt-4">
                        <div class="col-md-12 text-right">
                            <button type="button" id="btn-pedir" class="btn btn-success btn-lg">
                                <i class="fas fa-paper-plane"></i> Pedir
                            </button>
                            <button type="button" id="btn-cancelar" class="btn btn-danger btn-lg">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                        </div>
                    </div>
                </div>
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

        // Inicializar el contador regresivo
        function iniciarContadorRegresivo() {
            const ahora = new Date();
            const horaFin = new Date(`${ahora.toDateString()} ${hora_limite}`);

            actualizarContador();

            // Actualizar cada segundo
            intervaloContador = setInterval(actualizarContador, 1000);
        }

        function actualizarContador() {
            const ahora = new Date();
            const horaFin = new Date(`${ahora.toDateString()} ${hora_limite}`);
            const diferencia = horaFin - ahora;

            if (diferencia <= 0) {
                clearInterval(intervaloContador);
                $('#tiempo-restante').text('00:00');
                $('#contador-regresivo').removeClass('bg-primary bg-warning').addClass('bg-danger');

                // Mostrar alerta y redireccionar
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

            const minutos = Math.floor((diferencia % (1000 * 60 * 60)) / (1000 * 60));
            const segundos = Math.floor((diferencia % (1000 * 60)) / 1000);

            const minutosStr = minutos < 10 ? '0' + minutos : minutos;
            const segundosStr = segundos < 10 ? '0' + segundos : segundos;

            $('#tiempo-restante').text(`${minutosStr}:${segundosStr}`);

            // Cambiar color según el tiempo restante
            if (diferencia < (5 * 60 * 1000)) { // Menos de 5 minutos
                $('#contador-regresivo').removeClass('bg-primary bg-warning').addClass('bg-danger');

                // Mostrar alerta solo una vez cuando quedan 5 minutos
                if (minutos === 4 && segundos === 59) {
                    Swal.fire({
                        title: '¡Atención!',
                        text: 'Quedan menos de 5 minutos para realizar pedidos',
                        icon: 'warning',
                        confirmButtonText: 'Entendido'
                    });
                }
            } else if (diferencia < (15 * 60 * 1000)) { // Menos de 15 minutos
                $('#contador-regresivo').removeClass('bg-primary bg-danger').addClass('bg-warning');

                // Mostrar alerta solo una vez cuando quedan 15 minutos
                if (minutos === 14 && segundos === 59) {
                    Swal.fire({
                        title: '¡Atención!',
                        text: 'Quedan menos de 15 minutos para realizar pedidos',
                        icon: 'warning',
                        confirmButtonText: 'Entendido'
                    });
                }
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
                            <a href="#" class="list-group-item list-group-item-action" 
                               data-id="${receta.id}" 
                               data-id-producto="${receta.id_productos_api}"
                               data-id-u-medida="${receta.id_u_medidas}"
                               data-u-medida="${receta.u_medida_nombre}">
                                ${receta.nombre}
                                <small class="text-muted d-block">${receta.producto_nombre}</small>
                            </a>
                        `);
                        });
                        $sugerencias.show();
                    } else {
                        $sugerencias.hide();
                    }
                });
            } else {
                $('#sugerencias-recetas').hide();
            }
        });

        // Seleccionar receta de las sugerencias
        $(document).on('click', '#sugerencias-recetas a', function(e) {
            e.preventDefault();

            const id_receta = $(this).data('id');
            const id_producto = $(this).data('id-producto');
            const id_u_medida = $(this).data('id-u-medida');
            const u_medida = $(this).data('u-medida');

            $('#id_recetas').val(id_receta);
            $('#id_productos_api').val(id_producto);
            $('#buscar-receta').val($(this).text().trim());
            $('#id_u_medidas').val(id_u_medida).trigger('change');

            $('#sugerencias-recetas').hide();
        });

        // Limpiar formulario
        $('#btn-limpiar').click(function() {
            $('#form-detalle-pedido')[0].reset();
            $('#campos-personalizado').hide();
            $('#es_personalizado').prop('checked', false);
            $('#sugerencias-recetas').hide();
        });

        // Agregar pedido a la lista
        $('#btn-agregar').click(function() {
            if ($('#form-detalle-pedido')[0].checkValidity()) {
                const area = $('#id_areas option:selected').text();
                const receta = $('#buscar-receta').val();
                const cantidad = $('#cantidad').val();
                const unidad = $('#id_u_medidas option:selected').text();
                const es_personalizado = $('#es_personalizado').is(':checked');
                const descripcion = $('#descripcion').val();
                const foto_url = $('#foto_referencial_url').val();

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
                    foto_url: foto_url,
                    id_estado: 2 // Pendiente por defecto
                };

                pedidos.push(pedido);
                actualizarListaPedidos();
                $('#btn-limpiar').click(); // Limpiar el formulario
            } else {
                $('#form-detalle-pedido')[0].reportValidity();
            }
        });

        // Actualizar la tabla de pedidos
        function actualizarListaPedidos() {
            const $lista = $('#lista-pedidos');
            $lista.empty();

            if (pedidos.length === 0) {
                $lista.append('<tr><td colspan="7" class="text-center">No hay pedidos agregados</td></tr>');
                return;
            }

            pedidos.forEach(function(pedido, index) {
                const estadoColor = getColorEstado(pedido.id_estado);
                const personalizadoIcon = pedido.es_personalizado ?
                    '<i class="fas fa-check text-success"></i>' :
                    '<i class="fas fa-times text-danger"></i>';

                $lista.append(`
                <tr>
                    <td>${pedido.area_nombre}</td>
                    <td>${pedido.receta_nombre || 'Personalizado'}</td>
                    <td>${pedido.cantidad}</td>
                    <td>${pedido.u_medida_nombre}</td>
                    <td><span class="badge ${estadoColor}">Pendiente</span></td>
                    <td class="text-center">${personalizadoIcon}</td>
                    <td class="text-center">
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

        // Obtener color según el estado
        function getColorEstado(id_estado) {
            switch (id_estado) {
                case 2:
                    return 'bg-light text-dark'; // Pendiente
                case 3:
                    return 'bg-info'; // Procesando
                case 4:
                    return 'bg-success'; // Terminado
                case 5:
                    return 'bg-danger'; // Cancelado
                default:
                    return 'bg-secondary';
            }
        }

        // Editar pedido
        $(document).on('click', '.btn-editar', function() {
            const index = $(this).data('index');
            const pedido = pedidos[index];

            // Llenar el formulario con los datos del pedido
            $('#id_areas').val(pedido.id_area);
            $('#id_recetas').val(pedido.id_receta);
            $('#buscar-receta').val(pedido.receta_nombre);
            $('#id_productos_api').val(pedido.id_producto);
            $('#cantidad').val(pedido.cantidad);
            $('#id_u_medidas').val(pedido.id_u_medida);

            if (pedido.es_personalizado) {
                $('#es_personalizado').prop('checked', true);
                $('#campos-personalizado').show();
                $('#descripcion').val(pedido.descripcion);
                $('#foto_referencial_url').val(pedido.foto_url);
            } else {
                $('#es_personalizado').prop('checked', false);
                $('#campos-personalizado').hide();
            }

            // Cambiar el botón a "Actualizar"
            $('#btn-agregar').html('<i class="fas fa-sync-alt"></i> Actualizar');
            $('#btn-agregar').off('click').on('click', function() {
                // Actualizar el pedido
                pedidos[index] = {
                    id_area: $('#id_areas').val(),
                    area_nombre: $('#id_areas option:selected').text(),
                    id_receta: $('#id_recetas').val(),
                    receta_nombre: $('#buscar-receta').val(),
                    id_producto: $('#id_productos_api').val(),
                    cantidad: $('#cantidad').val(),
                    id_u_medida: $('#id_u_medidas').val(),
                    u_medida_nombre: $('#id_u_medidas option:selected').text(),
                    es_personalizado: $('#es_personalizado').is(':checked'),
                    descripcion: $('#descripcion').val(),
                    foto_url: $('#foto_referencial_url').val(),
                    id_estado: 2 // Pendiente por defecto
                };

                actualizarListaPedidos();
                $('#btn-limpiar').click();

                // Restaurar el botón a "Agregar"
                $('#btn-agregar').html('<i class="fas fa-plus"></i> Agregar');
                $('#btn-agregar').off('click').on('click', agregarPedido);
            });
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
                    actualizarListaPedidos();
                    Swal.fire(
                        'Eliminado!',
                        'El pedido ha sido eliminado.',
                        'success'
                    );
                }
            });
        });

        // Enviar pedido
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

            Swal.fire({
                title: '¿Confirmar pedido?',
                text: "Está a punto de enviar el pedido con " + pedidos.length + " items",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, enviar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    enviarPedido();
                }
            });
        });
        // Función para enviar el pedido al servidor
        function enviarPedido() {
            const data = {
                id_hora_limite: $('#id_hora_limite').val(),
                detalles: pedidos.map(pedido => ({
                    id_areas: pedido.id_area,
                    id_recetas: pedido.id_receta,
                    id_productos_api: pedido.id_producto,
                    cantidad: pedido.cantidad,
                    id_u_medidas: pedido.id_u_medida,
                    es_personalizado: pedido.es_personalizado,
                    descripcion: pedido.descripcion,
                    foto_referencial_url: pedido.foto_url,
                    id_estados: pedido.id_estado
                }))
            };

            $.ajax({
                url: '{{ route("pedidos.store") }}',
                method: 'POST',
                data: JSON.stringify(data),
                contentType: 'application/json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: '¡Éxito!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonText: 'Aceptar'
                        }).then(() => {
                            window.location.href = response.redirect;
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: response.message,
                            icon: 'error',
                            confirmButtonText: 'Entendido'
                        });
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'Error al enviar el pedido';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        title: 'Error',
                        text: errorMsg,
                        icon: 'error',
                        confirmButtonText: 'Entendido'
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
                        window.location.href = '{{ route("pedidos.index") }}';
                    }
                });
            } else {
                window.location.href = '{{ route("pedidos.index") }}';
            }
        });

        // Iniciar el contador al cargar la página
        iniciarContadorRegresivo();
    });
</script>
@endsection