@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Editar Pedido #{{ $pedido->doc_interno }}</h1>
    
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <span>Información del Pedido</span>
                <div id="reloj-countdown" class="badge bg-primary fs-5"></div>
            </div>
        </div>
        
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <p><strong>Usuario:</strong> {{ $pedido->usuario->nombre_personal }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Tienda:</strong> {{ $pedido->tienda->nombre }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Fecha/Hora creación:</strong> {{ $pedido->fecha_created }} {{ $pedido->hora_created }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Última actualización:</strong> {{ $pedido->fecha_last_update }} {{ $pedido->hora_last_update }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Hora Límite:</strong> {{ $horaLimite->hora_limite }}</p>
                </div>
            </div>
            
            <form id="pedidoForm" method="POST" action="{{ route('pedidos.update', $pedido->id_pedidos_cab) }}">
                @csrf
                @method('PUT')
                
                <div class="card mb-4">
                    <div class="card-header">Detalles del Pedido</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="id_areas" class="form-label">Área</label>
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
                                    <label for="buscar_receta" class="form-label">Buscar Receta</label>
                                    <input type="text" class="form-control" id="buscar_receta" placeholder="Ingrese al menos 3 caracteres">
                                    <div id="resultados_recetas" class="mt-2 d-none"></div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="cantidad" class="form-label">Cantidad</label>
                                    <input type="number" step="0.01" min="0.1" class="form-control" id="cantidad" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="id_u_medidas" class="form-label">Unidad de Medida</label>
                                    <select class="form-select" id="id_u_medidas" name="id_u_medidas" required>
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
                        
                        <div id="personalizado_fields" class="row d-none">
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
                                    <small class="text-muted">Opcional</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" id="limpiarBtn" class="btn btn-secondary">Limpiar</button>
                            <button type="button" id="agregarBtn" class="btn btn-primary">Agregar</button>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">Lista de Pedidos</div>
                    <div class="card-body">
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table" id="tablaDetalles">
                                <thead>
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
                                <tbody id="detallesBody">
                                    <!-- Aquí se agregarán los detalles dinámicamente -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('pedidos.index') }}" class="btn btn-danger">Cancelar</a>
                    <button type="submit" class="btn btn-success">Actualizar Pedido</button>
                </div>
                
                <!-- Input oculto para almacenar los detalles -->
                <input type="hidden" name="detalles" id="detallesInput">
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Variables globales
    const hora_limite = '{{ $horaLimite->hora_limite }}';
    let detalles = @json($pedido->pedidosDetalle->map(function($detalle) {
        return [
            'id_areas' => $detalle->id_areas,
            'area_nombre' => $detalle->area->nombre,
            'id_recetas' => $detalle->id_recetas,
            'receta_nombre' => $detalle->receta ? $detalle->receta->nombre : 'Personalizado',
            'cantidad' => $detalle->cantidad,
            'id_u_medidas' => $detalle->id_u_medidas,
            'unidad_nombre' => $detalle->uMedida->nombre,
            'es_personalizado' => $detalle->es_personalizado,
            'descripcion' => $detalle->descripcion,
            'foto_referencial_url' => $detalle->foto_referencial_url,
            'id_estados' => $detalle->id_estados,
            'id_productos_api' => $detalle->id_productos_api
        ];
    }));
    
    // Inicializar el reloj countdown
    function iniciarCountdown() {
        const ahora = new Date();
        const hora_limite_date = new Date(`${ahora.toDateString()} ${hora_limite}`);
        
        // Actualizar cada segundo
        const countdown = setInterval(() => {
            const ahora = new Date();
            const diff = hora_limite_date - ahora;
            
            if (diff <= 0) {
                clearInterval(countdown);
                $('#reloj-countdown').text('Tiempo agotado!').removeClass('bg-primary bg-warning').addClass('bg-danger');
                Swal.fire({
                    icon: 'warning',
                    title: 'Tiempo agotado',
                    text: 'El tiempo para editar pedidos ha terminado',
                }).then(() => {
                    window.location.href = "{{ route('pedidos.index') }}";
                });
                return;
            }
            
            const minutos = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const segundos = Math.floor((diff % (1000 * 60)) / 1000);
            
            // Cambiar colores según el tiempo restante
            if (minutos < 5) {
                $('#reloj-countdown').removeClass('bg-primary bg-warning').addClass('bg-danger');
            } else if (minutos < 15) {
                $('#reloj-countdown').removeClass('bg-primary bg-danger').addClass('bg-warning');
            }
            
            $('#reloj-countdown').text(`${minutos}m ${segundos}s`);
        }, 1000);
    }
    
    // Buscar recetas al escribir
    $('#buscar_receta').on('input', function() {
        const termino = $(this).val().trim();
        const id_areas = $('#id_areas').val();
        
        if (termino.length >= 3 && id_areas) {
            $.ajax({
                url: "{{ route('pedidos.buscar-recetas') }}",
                method: 'GET',
                data: {
                    id_areas: id_areas,
                    termino: termino
                },
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
                                    ${receta.nombre} (${receta.u_medida_nombre})
                                </div>
                            `);
                        });
                    } else {
                        resultados.html('<div class="list-group-item">No se encontraron recetas</div>')
                                 .removeClass('d-none');
                    }
                },
                error: function(xhr) {
                    console.error('Error en la búsqueda:', xhr.responseText);
                    $('#resultados_recetas').html('<div class="list-group-item text-danger">Error en la búsqueda</div>')
                                         .removeClass('d-none');
                }
            });
        } else {
            $('#resultados_recetas').addClass('d-none');
        }
    });
    
    // Seleccionar receta
    $(document).on('click', '.receta-item', function() {
        const id_recetas = $(this).data('id');
        const nombre = $(this).data('nombre');
        const id_u_medida = $(this).data('id_u_medida');
        const u_medida_nombre = $(this).data('u_medida_nombre');
        
        $('#buscar_receta').val(nombre);
        $('#id_u_medidas').val(id_u_medida).trigger('change');
        $('#resultados_recetas').addClass('d-none');
        
        $('#buscar_receta').data('id_recetas', id_recetas);
        $('#buscar_receta').data('id_productos_api', $(this).data('id_producto'));
    });
    
    // Mostrar/ocultar campos personalizados
    $('#es_personalizado').change(function() {
        if ($(this).is(':checked')) {
            $('#personalizado_fields').removeClass('d-none');
        } else {
            $('#personalizado_fields').addClass('d-none');
        }
    });
    
    // Agregar/Actualizar detalle
    $('#agregarBtn').click(function() {
        const id_areas = $('#id_areas').val();
        const area_nombre = $('#id_areas option:selected').text();
        const id_recetas = $('#buscar_receta').data('id_recetas');
        const receta_nombre = $('#buscar_receta').val();
        const cantidad = parseFloat($('#cantidad').val());
        const id_u_medidas = $('#id_u_medidas').val();
        const unidad_nombre = $('#id_u_medidas option:selected').text();
        const es_personalizado = $('#es_personalizado').is(':checked');
        const descripcion = $('#descripcion').val();
        const foto_referencial_url = $('#foto_referencial_url').val();

        // Validaciones
        if (!id_areas) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Por favor seleccione un área',
            });
            return;
        }
        if (!cantidad || cantidad <= 0) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Por favor ingrese una cantidad válida',
            });
            return;
        }
        if (!id_u_medidas) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Por favor seleccione una unidad de medida',
            });
            return;
        }
        if (es_personalizado && !descripcion) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Por favor ingrese una descripción para el pedido personalizado',
            });
            return;
        }

        const detalle = {
            id_areas: id_areas,
            area_nombre: area_nombre,
            id_recetas: id_recetas || null,
            receta_nombre: receta_nombre || 'Personalizado',
            cantidad: cantidad,
            id_u_medidas: id_u_medidas,
            unidad_nombre: unidad_nombre,
            es_personalizado: es_personalizado,
            descripcion: es_personalizado ? descripcion : null,
            foto_referencial_url: es_personalizado ? foto_referencial_url : null,
            id_estados: 2, // Pendiente por defecto
            id_productos_api: $('#buscar_receta').data('id_productos_api') || null
        };

        const editIndex = $(this).data('edit-index');
        if (editIndex !== undefined) {
            detalles[editIndex] = detalle;
            $(this).text('Agregar').removeData('edit-index');
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
        $('#agregarBtn').text('Agregar').removeData('edit-index');
    }
    
    // Actualizar tabla de detalles
    function actualizarTablaDetalles() {
        const tbody = $('#detallesBody');
        tbody.empty();
        
        detalles.forEach((detalle, index) => {
            const estadoColor = getEstadoColor(detalle.id_estados);
            
            tbody.append(`
                <tr>
                    <td>${detalle.area_nombre}</td>
                    <td>${detalle.receta_nombre}</td>
                    <td>${detalle.cantidad}</td>
                    <td>${detalle.unidad_nombre}</td>
                    <td><span class="badge ${estadoColor}">${getEstadoNombre(detalle.id_estados)}</span></td>
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
        
        // Actualizar input oculto con los detalles
        $('#detallesInput').val(JSON.stringify(detalles));
    }
    
    // Obtener color según estado
    function getEstadoColor(id_estados) {
        switch(id_estados) {
            case 2: return 'bg-light text-dark'; // Pendiente
            case 3: return 'bg-info'; // Procesando
            case 4: return 'bg-success'; // Terminado
            case 5: return 'bg-danger'; // Cancelado
            default: return 'bg-secondary';
        }
    }
    
    // Obtener nombre del estado
    function getEstadoNombre(id_estados) {
        switch(id_estados) {
            case 2: return 'Pendiente';
            case 3: return 'Procesando';
            case 4: return 'Terminado';
            case 5: return 'Cancelado';
            default: return 'Desconocido';
        }
    }
    
    // Editar detalle
    $(document).on('click', '.editar-detalle', function() {
        const index = $(this).data('index');
        const detalle = detalles[index];
        
        // Llenar campos con los datos del detalle
        $('#id_areas').val(detalle.id_areas).trigger('change');
        $('#buscar_receta').val(detalle.receta_nombre === 'Personalizado' ? '' : detalle.receta_nombre)
                          .data('id_recetas', detalle.id_recetas)
                          .data('id_productos_api', detalle.id_productos_api);
        $('#cantidad').val(detalle.cantidad);
        $('#id_u_medidas').val(detalle.id_u_medidas);
        $('#es_personalizado').prop('checked', detalle.es_personalizado);

        if (detalle.es_personalizado) {
            $('#personalizado_fields').removeClass('d-none');
            $('#descripcion').val(detalle.descripcion);
            $('#foto_referencial_url').val(detalle.foto_referencial_url);
        } else {
            $('#personalizado_fields').addClass('d-none');
        }

        // Cambiar texto del botón
        $('#agregarBtn').text('Actualizar').data('edit-index', index);

        // Eliminar el detalle de la lista temporalmente
        detalles.splice(index, 1);
        actualizarTablaDetalles();
    });
    
    // Eliminar detalle
    $(document).on('click', '.eliminar-detalle', function() {
        const index = $(this).data('index');
        detalles.splice(index, 1);
        actualizarTablaDetalles();
    });
    
    // Validar formulario antes de enviar
    $('#pedidoForm').submit(function(e) {
        if (detalles.length === 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Debe agregar al menos un detalle al pedido',
            });
            return false;
        }
        
        // Validar que cada detalle tenga los campos requeridos
        const detallesInvalidos = detalles.some(detalle => {
            return !detalle.id_areas || !detalle.cantidad || !detalle.id_u_medidas || 
                   (detalle.es_personalizado && !detalle.descripcion);
        });
        
        if (detallesInvalidos) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Uno o más detalles tienen campos incompletos',
            });
            return false;
        }
    });
    
    // Iniciar el countdown al cargar la página
    $(document).ready(function() {
        iniciarCountdown();
        actualizarTablaDetalles();
    });
</script>
@endpush