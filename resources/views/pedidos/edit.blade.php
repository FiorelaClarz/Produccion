@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Editar Pedido #{{ $pedido->id_pedidos_cab }}</h4>
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
                                    <p class="form-control-plaintext">{{ $pedido->usuario->nombre_personal }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><strong>Tienda:</strong></label>
                                    <p class="form-control-plaintext">{{ $pedido->usuario->tienda->nombre ?? 'No asignada' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detalles del Pedido -->
                    <div class="mb-4">
                        <h5>Detalles del Pedido</h5>
                        <form id="form-detalle-pedido">
                            @csrf
                            @method('PUT')
                            <input type="hidden" id="id_hora_limite" name="id_hora_limite" value="{{ $pedido->id_hora_limite }}">
                            <input type="hidden" id="editing-index" value="">

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
                                        <input type="number" class="form-control" id="cantidad" name="cantidad" min="0.1" step="0.1" value="1" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="id_u_medidas">Unidad de Medida</label>
                                        <select class="form-control" id="id_u_medidas" name="id_u_medidas" required>
                                            <option value="">Seleccione</option>
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
                                            <label for="foto_referencial">Imagen referencial (opcional)</label>
                                            <input type="file" class="form-control" id="foto_referencial" name="foto_referencial" accept="image/*">
                                            <small class="form-text text-muted">Formatos: JPEG, PNG, JPG, GIF (Max 2MB)</small>
                                            <div id="preview-container" class="mt-2" style="display:none;">
                                                <img id="preview-image" src="#" alt="Previsualización" class="img-thumbnail" style="max-height: 150px;">
                                            </div>
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
                                    <button type="button" id="btn-actualizar-item" class="btn btn-success" style="display:none;">
                                        <i class="fas fa-sync-alt"></i> Actualizar
                                    </button>
                                    <button type="button" id="btn-cancelar-edicion" class="btn btn-danger" style="display:none;">
                                        <i class="fas fa-times"></i> Cancelar
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
                                    @foreach($pedido->pedidosDetalle->where('is_deleted', false) as $detalle)
                                    <tr data-id="{{ $detalle->id_pedidos_det }}">
                                        <td>{{ $detalle->area->nombre }}</td>
                                        <td>{{ $detalle->receta ? $detalle->receta->nombre : 'Personalizado' }}</td>
                                        <td>{{ $detalle->cantidad }}</td>
                                        <td>{{ $detalle->uMedida->nombre }}</td>
                                        <td><span class="badge {{ $detalle->estado->badge_class }}">{{ $detalle->estado->nombre }}</span></td>
                                        <td class="text-center">
                                            <i class="fas {{ $detalle->es_personalizado ? 'fa-check text-success' : 'fa-times text-danger' }}"></i>
                                        </td>
                                        <td class="text-center">
                                            @if($detalle->foto_referencial)
                                            <div class="position-relative" style="width: 50px; height: 50px;">
                                                <img src="{{ asset('storage/' . $detalle->foto_referencial) }}" 
                                                     class="img-thumbnail" 
                                                     style="width: 100%; height: 100%; object-fit: cover;">
                                                <button class="btn btn-sm btn-danger position-absolute top-0 end-0 p-0 eliminar-foto" 
                                                        style="width: 15px; height: 15px; line-height: 15px;"
                                                        data-id="{{ $detalle->id_pedidos_det }}">
                                                    <i class="fas fa-times" style="font-size: 8px;"></i>
                                                </button>
                                            </div>
                                            @else
                                            <div class="text-muted">Sin imagen</div>
                                            @endif
                                            <button class="btn btn-sm btn-warning btn-editar" data-id="{{ $detalle->id_pedidos_det }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger btn-eliminar" data-id="{{ $detalle->id_pedidos_det }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Botones finales -->
                    <div class="row mt-4">
                        <div class="col-md-12 text-right">
                            <button type="button" id="btn-actualizar" class="btn btn-success btn-lg">
                                <i class="fas fa-save"></i> Actualizar
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
        let pedidos = @json($pedidosData).map(pedido => {
            return {
                id_pedidos_det: pedido.id_pedidos_det,
                id_area: pedido.id_area,
                area_nombre: pedido.area_nombre,
                id_receta: pedido.id_receta,
                receta_nombre: pedido.receta_nombre,
                id_producto: pedido.id_producto,
                cantidad: pedido.cantidad,
                id_u_medida: pedido.id_u_medida,
                u_medida_nombre: pedido.u_medida_nombre,
                es_personalizado: pedido.es_personalizado,
                descripcion: pedido.descripcion,
                foto_referencial: null,
                foto_referencial_url: pedido.foto_referencial_url ? 
                    '{{ asset('') }}' + pedido.foto_referencial_url : null,
                id_estado: pedido.id_estado || 2,
                is_deleted: false // Añadido para manejo de eliminación
            };
        });

        let hora_limite = '{{ $pedido->horaLimite->hora_limite }}';
        let intervaloContador = null;
        let editandoIndex = null;

        // Función para cambiar a modo edición
        function cambiarAModoEdicion(mostrar) {
            if (mostrar) {
                $('#btn-agregar').hide();
                $('#btn-limpiar').hide();
                $('#btn-actualizar-item').show();
                $('#btn-cancelar-edicion').show();
            } else {
                $('#btn-agregar').show();
                $('#btn-limpiar').show();
                $('#btn-actualizar-item').hide();
                $('#btn-cancelar-edicion').hide();
                editandoIndex = null;
                $('#editing-index').val('');
            }
        }

        // Inicializar el contador regresivo
        function iniciarContadorRegresivo() {
            const [hours, minutes, seconds] = hora_limite.split(':').map(Number);
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

            const horasStr = horas.toString().padStart(2, '0');
            const minutosStr = minutos.toString().padStart(2, '0');
            const segundosStr = segundos.toString().padStart(2, '0');

            const tiempoRestante = horas > 0 
                ? `${horasStr}:${minutosStr}:${segundosStr}`
                : `${minutosStr}:${segundosStr}`;

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
                                <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-bold">${receta.nombre}</div>
                                        <small class="text-muted">${receta.producto_nombre}</small>
                                    </div>
                                    <span class="badge bg-primary rounded-pill">${receta.u_medida_nombre}</span>
                                    <span class="d-none" 
                                          data-id="${receta.id}" 
                                          data-id-producto="${receta.id_productos_api}"
                                          data-id-u-medida="${receta.id_u_medidas}"
                                          data-u-medida-nombre="${receta.u_medida_nombre}"></span>
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

        // Limpiar formulario
        $('#btn-limpiar').click(function() {
            $('#form-detalle-pedido')[0].reset();
            $('#campos-personalizado').hide();
            $('#es_personalizado').prop('checked', false);
            $('#sugerencias-recetas').hide();
            $('#preview-container').hide();
        });

        // Cancelar edición
        $('#btn-cancelar-edicion').click(function() {
            $('#btn-limpiar').click();
            cambiarAModoEdicion(false);
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
                const fotoInput = $('#foto_referencial')[0];
                const fotoFile = fotoInput.files[0];

                const pedido = {
                    id_pedidos_det: null, // Para nuevos items
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
                    foto_referencial: fotoFile,
                    foto_referencial_url: null,
                    id_estado: 2,
                    is_deleted: false
                };

                pedidos.push(pedido);
                actualizarListaPedidos();
                $('#btn-limpiar').click();
            } else {
                $('#form-detalle-pedido')[0].reportValidity();
            }
        });

        // Actualizar item existente
        $('#btn-actualizar-item').click(function() {
            if ($('#form-detalle-pedido')[0].checkValidity()) {
                const index = $('#editing-index').val();
                const area = $('#id_areas option:selected').text();
                const receta = $('#buscar-receta').val();
                const cantidad = $('#cantidad').val();
                const unidad = $('#id_u_medidas option:selected').text();
                const es_personalizado = $('#es_personalizado').is(':checked');
                const descripcion = $('#descripcion').val();
                const fotoInput = $('#foto_referencial')[0];
                const fotoFile = fotoInput.files[0];

                // Mantener la URL de la imagen original si no se sube una nueva
                const fotoOriginalUrl = pedidos[index].foto_referencial_url;

                pedidos[index] = {
                    id_pedidos_det: pedidos[index].id_pedidos_det,
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
                    foto_referencial: fotoFile,
                    foto_referencial_url: fotoOriginalUrl,
                    id_estado: pedidos[index].id_estado,
                    is_deleted: false
                };

                actualizarListaPedidos();
                $('#btn-limpiar').click();
                cambiarAModoEdicion(false);
            } else {
                $('#form-detalle-pedido')[0].reportValidity();
            }
        });

        function actualizarListaPedidos() {
            const $lista = $('#lista-pedidos');
            $lista.empty();

            // Filtrar solo pedidos no eliminados
            const pedidosActivos = pedidos.filter(p => !p.is_deleted);

            if (pedidosActivos.length === 0) {
                $lista.append('<tr><td colspan="7" class="text-center">No hay pedidos agregados</td></tr>');
                return;
            }

            // Ordenar los pedidos por área y luego por receta
            const pedidosOrdenados = [...pedidosActivos].sort((a, b) => {
                if (a.area_nombre < b.area_nombre) return -1;
                if (a.area_nombre > b.area_nombre) return 1;
                
                const recetaA = a.receta_nombre || 'Personalizado';
                const recetaB = b.receta_nombre || 'Personalizado';
                return recetaA.localeCompare(recetaB);
            });

            pedidosOrdenados.forEach(function(pedido, index) {
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
                                    data-index="${pedidos.findIndex(p => p.id_pedidos_det === pedido.id_pedidos_det)}">
                                <i class="fas fa-times" style="font-size: 8px;"></i>
                            </button>
                        </div>`;
                } else if (pedido.foto_referencial_url) {
                    // Imagen existente
                    fotoThumbnail = `
                        <div class="position-relative" style="width: 50px; height: 50px;">
                            <img src="${pedido.foto_referencial_url}" 
                                 class="img-thumbnail" 
                                 style="width: 100%; height: 100%; object-fit: cover;">
                            <button class="btn btn-sm btn-danger position-absolute top-0 end-0 p-0 eliminar-foto" 
                                    style="width: 15px; height: 15px; line-height: 15px;"
                                    data-index="${pedidos.findIndex(p => p.id_pedidos_det === pedido.id_pedidos_det)}">
                                <i class="fas fa-times" style="font-size: 8px;"></i>
                            </button>
                        </div>`;
                }

                $lista.append(`
                    <tr data-index="${pedidos.findIndex(p => p.id_pedidos_det === pedido.id_pedidos_det)}" 
                        data-id="${pedido.id_pedidos_det}">
                        <td>${pedido.area_nombre}</td>
                        <td>${pedido.receta_nombre || 'Personalizado'}</td>
                        <td>${pedido.cantidad}</td>
                        <td>${pedido.u_medida_nombre}</td>
                        <td><span class="badge ${estadoColor}">${getEstadoNombre(pedido.id_estado)}</span></td>
                        <td class="text-center">${personalizadoIcon}</td>
                        <td class="text-center">
                            ${fotoThumbnail}
                            <button class="btn btn-sm btn-warning btn-editar" data-id="${pedido.id_pedidos_det}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger btn-eliminar" data-id="${pedido.id_pedidos_det}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });
        }

        // Función auxiliar para obtener el nombre del estado
        function getEstadoNombre(id_estado) {
            switch(id_estado) {
                case 2: return 'Pendiente';
                case 3: return 'Procesando';
                case 4: return 'Terminado';
                case 5: return 'Cancelado';
                default: return 'Desconocido';
            }
        }

        // Eliminar foto de un pedido
        $(document).on('click', '.eliminar-foto', function(e) {
            e.stopPropagation();
            const index = $(this).data('index');
            pedidos[index].foto_referencial = null;
            pedidos[index].foto_referencial_url = null;
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
            const idPedidoDet = $(this).data('id');
            const index = pedidos.findIndex(p => p.id_pedidos_det == idPedidoDet);
            
            if (index === -1) {
                console.error('No se encontró el pedido con ID:', idPedidoDet);
                return;
            }

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
                if (pedido.foto_referencial_url) {
                    $('#preview-image').attr('src', pedido.foto_referencial_url);
                    $('#preview-container').show();
                }
            } else {
                $('#es_personalizado').prop('checked', false).trigger('change');
            }

            // Guardar el índice del pedido que estamos editando
            editandoIndex = index;
            $('#editing-index').val(index);
            
            // Cambiar a modo edición
            cambiarAModoEdicion(true);
        });

        // Eliminar pedido
        $(document).on('click', '.btn-eliminar', function() {
            const idPedidoDet = $(this).data('id');
            const index = pedidos.findIndex(p => p.id_pedidos_det == idPedidoDet);
            
            Swal.fire({
                title: '¿Eliminar pedido?',
                text: "Esta acción marcará el pedido como eliminado",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Marcar como eliminado en lugar de borrarlo del array
                    pedidos[index].is_deleted = true;
                    actualizarListaPedidos();
                    Swal.fire(
                        'Eliminado!',
                        'El pedido ha sido marcado como eliminado.',
                        'success'
                    );
                }
            });
        });

        // Actualizar pedido completo
        $('#btn-actualizar').click(function() {
            const pedidosActivos = pedidos.filter(p => !p.is_deleted);
            
            if (pedidosActivos.length === 0) {
                Swal.fire({
                    title: 'Error',
                    text: 'Debe agregar al menos un pedido',
                    icon: 'error',
                    confirmButtonText: 'Entendido'
                });
                return;
            }

            Swal.fire({
                title: '¿Confirmar cambios?',
                text: "Está a punto de actualizar el pedido con " + pedidosActivos.length + " items",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, actualizar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    actualizarPedido();
                }
            });
        });

        function actualizarPedido() {
            const formData = new FormData();
            formData.append('_method', 'PUT');
            
            // Solo enviar pedidos no eliminados
            pedidos.forEach((pedido, index) => {
                if (!pedido.is_deleted) {
                    formData.append(`detalles[${index}][id_pedidos_det]`, pedido.id_pedidos_det || '');
                    formData.append(`detalles[${index}][id_areas]`, pedido.id_area);
                    formData.append(`detalles[${index}][id_recetas]`, pedido.id_receta || '');
                    formData.append(`detalles[${index}][id_productos_api]`, pedido.id_producto || '');
                    formData.append(`detalles[${index}][cantidad]`, pedido.cantidad);
                    formData.append(`detalles[${index}][id_u_medidas]`, pedido.id_u_medida);
                    formData.append(`detalles[${index}][es_personalizado]`, pedido.es_personalizado ? '1' : '0');
                    formData.append(`detalles[${index}][descripcion]`, pedido.descripcion || '');
                    formData.append(`detalles[${index}][id_estados]`, pedido.id_estado);
                    formData.append(`detalles[${index}][is_deleted]`, '0'); // Siempre false para los activos

                    // Agregar la imagen si existe (nueva)
                    if (pedido.foto_referencial instanceof File) {
                        formData.append(`detalles[${index}][foto_referencial]`, pedido.foto_referencial);
                    } else if (pedido.foto_referencial_url) {
                        // Extraer solo el nombre del archivo si es una URL completa
                        const urlParts = pedido.foto_referencial_url.split('/');
                        const fileName = urlParts[urlParts.length - 1];
                        formData.append(`detalles[${index}][foto_referencial_url]`, 'pedidos/' + fileName);
                    }
                } else {
                    // Para pedidos eliminados, solo enviar el ID y la marca de eliminado
                    formData.append(`detalles[${index}][id_pedidos_det]`, pedido.id_pedidos_det || '');
                    formData.append(`detalles[${index}][is_deleted]`, '1');
                }
            });

            $.ajax({
                url: '{{ route("pedidos.update", $pedido->id_pedidos_cab) }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
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
                    let errorMsg = 'Error al actualizar el pedido';
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
                    title: '¿Cancelar cambios?',
                    text: "Todos los cambios no guardados se perderán",
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
<style>
    #sugerencias-recetas {
        position: absolute;
        z-index: 1000;
        width: 100%;
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
</style>
@endsection