@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Crear Nueva Receta</h1>

    <form id="recetaForm" action="{{ route('recetas.store') }}" method="POST">
        @csrf

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="id_areas">Área</label>
                    <select class="form-control" id="id_areas" name="id_areas" required>
                        <option value="">Seleccione un área</option>
                        @foreach($areas as $area)
                        <option value="{{ $area->id_areas }}">{{ $area->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="producto_nombre">Producto</label>
                    <input type="text" class="form-control" id="producto_nombre" placeholder="Buscar producto..." required>
                    <input type="hidden" id="id_productos_api" name="id_productos_api">
                    <div id="productoResults" class="list-group" style="display:none; position:absolute; z-index:1000; width:100%;"></div>
                </div>

                <div class="form-group">
                    <label for="nombre">Nombre Receta</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="cant_rendimiento">Rendimiento</label>
                    <input type="number" step="0.01" class="form-control" id="cant_rendimiento" name="cant_rendimiento" required>
                </div>

                <div class="form-group">
                    <label for="id_u_medidas">Unidad de Medida</label>
                    <select class="form-control" id="id_u_medidas" name="id_u_medidas" required>
                        <option value="">Seleccione una unidad</option>
                        @foreach($unidades as $unidad)
                        <option value="{{ $unidad->id_u_medidas }}">{{ $unidad->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="constante_crecimiento">Constante Crecimiento</label>
                            <input type="number" step="0.01" class="form-control" id="constante_crecimiento" name="constante_crecimiento" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="constante_peso_lata">Constante Peso Lata</label>
                            <input type="number" step="0.01" class="form-control" id="constante_peso_lata" name="constante_peso_lata" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr>

        <h3>Ingredientes</h3>

        <div class="row mb-3">
            <div class="col-md-8">
                <div class="form-group">
                    <label for="ingrediente_nombre">Producto Ingrediente</label>
                    <input type="text" class="form-control" id="ingrediente_nombre" placeholder="Buscar producto ingrediente...">
                    <input type="hidden" id="ingrediente_id">
                    <div id="ingredienteResults" class="list-group" style="display:none; position:absolute; z-index:1000; width:100%;"></div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label for="ingrediente_cantidad">Cantidad</label>
                    <input type="number" step="0.01" class="form-control" id="ingrediente_cantidad">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label for="ingrediente_presentacion">Presentación</label>
                    <input type="number" class="form-control" id="ingrediente_presentacion" value="1">
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-8">
                <div class="form-group">
                    <label for="ingrediente_u_medida">Unidad de Medida</label>
                    <select class="form-control" id="ingrediente_u_medida">
                        <option value="">Seleccione unidad</option>
                        @foreach($unidades as $unidad)
                        <option value="{{ $unidad->id_u_medidas }}">{{ $unidad->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="button" id="limpiarIngrediente" class="btn btn-secondary mr-2">Limpiar</button>
                <button type="button" id="agregarIngrediente" class="btn btn-primary">Agregar</button>
            </div>
        </div>

        <div class="table-responsive mb-4">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Presentación</th>
                        <th>U. Medida</th>
                        <th>Costo Unitario</th>
                        <th>Subtotal</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="ingredientesTable">
                    <!-- Aquí se agregarán los ingredientes dinámicamente -->
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6" class="text-right"><strong>Subtotal:</strong></td>
                        <td id="subtotalTotal">S/ 0.00</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <input type="hidden" name="ingredientes" id="ingredientesData">

        <div class="form-group">
            <button type="submit" class="btn btn-success">Guardar Receta</button>
            <a href="{{ route('recetas.index') }}" class="btn btn-danger">Cancelar</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Configuración AJAX global
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Función mejorada de búsqueda con mejor manejo de errores
        function buscarProductos(inputElement, resultsContainer) {
            const term = inputElement.val().trim();
            console.log("[DEBUG] Iniciando búsqueda con término:", term);

            // Limpiar resultados anteriores
            resultsContainer.hide().empty();

            if (term.length < 2) {
                console.log("[DEBUG] Término demasiado corto, mínimo 2 caracteres");
                return;
            }

            // Mostrar indicador de carga
            resultsContainer.html(`
            <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-center">
                    <span>Buscando productos...</span>
                    <div class="spinner-border spinner-border-sm" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                </div>
            </div>
        `).show();

            // Realizar la petición AJAX
            $.ajax({
                url: '{{ route("recetas.buscarProductos") }}',
                method: 'GET',
                data: {
                    term: term
                },
                dataType: 'json',
                success: function(response) {
                    console.log("[DEBUG] Respuesta recibida:", response);

                    // Limpiar el contenedor
                    resultsContainer.empty();

                    if (!response || response.length === 0) {
                        resultsContainer.html(`
                        <div class="list-group-item text-muted">
                            No se encontraron resultados para "${term}"
                        </div>
                    `).show();
                        return;
                    }

                    // Construir HTML con los resultados
                    let html = '';
                    response.forEach(function(producto) {
                        html += `
                    <a href="#" class="list-group-item list-group-item-action product-item"
                       data-id="${producto.id}"
                       data-nombre="${producto.text}"
                       data-costo="${producto.costo}">
                       <div class="d-flex justify-content-between">
                           <span><strong>${producto.text}</strong></span>
                           <small class="text-muted">${producto.codigo || 'Sin código'}</small>
                       </div>
                       <small class="text-success">S/ ${producto.costo.toFixed(2)}</small>
                    </a>`;
                    });

                    resultsContainer.html(html).show();
                },
                error: function(xhr, status, error) {
                    console.error("[ERROR] En la búsqueda:", status, error);
                    console.error("[ERROR] Respuesta completa:", xhr.responseText);

                    resultsContainer.html(`
                    <div class="list-group-item text-danger">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        Error al realizar la búsqueda
                    </div>
                `).show().delay(3000).fadeOut();
                },
                complete: function() {
                    console.log("[DEBUG] Búsqueda completada");
                }
            });
        }

        // Eventos de búsqueda con debounce para mejor performance
        let searchTimer;
        $('#producto_nombre, #ingrediente_nombre').on('input', function() {
            const input = $(this);
            const resultsContainer = input.attr('id') === 'producto_nombre' ?
                $('#productoResults') :
                $('#ingredienteResults');

            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                buscarProductos(input, resultsContainer);
            }, 300);
        });

        // Selección de productos (común para ambos campos)
        $(document).on('click', '#productoResults .product-item, #ingredienteResults .product-item', function(e) {
            e.preventDefault();
            const producto = {
                id: $(this).data('id'),
                nombre: $(this).data('nombre'),
                costo: $(this).data('costo')
            };

            // Determinar si es producto principal o ingrediente
            if ($(this).parent().attr('id') === 'productoResults') {
                $('#producto_nombre').val(producto.nombre);
                $('#id_productos_api').val(producto.id);
                $('#nombre').val(producto.nombre);
                $('#productoResults').hide();
            } else {
                $('#ingrediente_nombre').val(producto.nombre);
                $('#ingrediente_id').val(producto.id);
                $('#ingredienteResults').hide();
            }
        });

        // [Mantén el resto de tus funciones igual...]
        // Limpiar campos ingrediente
        $('#limpiarIngrediente').click(function() {
            $('#ingrediente_nombre').val('');
            $('#ingrediente_id').val('');
            $('#ingrediente_cantidad').val('');
            $('#ingrediente_presentacion').val('1');
            $('#ingrediente_u_medida').val('');
        });

        // Agregar ingrediente a la tabla
        $('#agregarIngrediente').click(function() {
            const id = $('#ingrediente_id').val();
            const nombre = $('#ingrediente_nombre').val();
            const cantidad = $('#ingrediente_cantidad').val();
            const presentacion = $('#ingrediente_presentacion').val();
            const uMedidaId = $('#ingrediente_u_medida').val();
            const uMedidaNombre = $('#ingrediente_u_medida option:selected').text();

            if (!id || !cantidad || !uMedidaId) {
                alert('Por favor complete todos los campos del ingrediente');
                return;
            }

            $.ajax({
                url: '{{ route("recetas.agregarIngrediente") }}',
                method: 'POST',
                data: {
                    id_productos_api: id,
                    cantidad: cantidad,
                    cant_presentacion: presentacion,
                    id_u_medidas: uMedidaId
                },
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        const rowId = 'ingrediente-' + Date.now();
                        $('#ingredientesTable').append(`
                    <tr id="${rowId}">
                        <td>${data.id}</td>
                        <td>${data.nombre}</td>
                        <td>${data.cantidad}</td>
                        <td>${data.cant_presentacion}</td>
                        <td>${data.u_medida}</td>
                        <td>S/ ${data.costo_unitario.toFixed(2)}</td>
                        <td>S/ ${data.subtotal.toFixed(2)}</td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm eliminar-ingrediente" data-row="${rowId}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    `);

                        actualizarSubtotal();
                        $('#limpiarIngrediente').click();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr) {
                    alert('Error en la solicitud. Por favor intente nuevamente.');
                    console.error(xhr.responseText);
                }
            });
        });

        // Eliminar ingrediente de la tabla
        $(document).on('click', '.eliminar-ingrediente', function() {
            $(this).closest('tr').remove();
            actualizarSubtotal();
        });

        // Actualizar subtotal
        function actualizarSubtotal() {
            let subtotal = 0;

            $('#ingredientesTable tr').each(function() {
                const subtotalText = $(this).find('td:eq(6)').text().replace('S/ ', '');
                subtotal += parseFloat(subtotalText);
            });

            $('#subtotalTotal').text('S/ ' + subtotal.toFixed(2));

            // Preparar datos para el formulario
            const ingredientes = [];
            $('#ingredientesTable tr').each(function() {
                ingredientes.push({
                    id_productos_api: $(this).find('td:eq(0)').text(),
                    cantidad: $(this).find('td:eq(2)').text(),
                    cant_presentacion: $(this).find('td:eq(3)').text(),
                    id_u_medidas: $('#ingrediente_u_medida option:contains("' + $(this).find('td:eq(4)').text() + '")').val()
                });
            });

            $('#ingredientesData').val(JSON.stringify(ingredientes));
        }

        // Ocultar resultados al hacer clic fuera
        $(document).click(function(e) {
            if (!$(e.target).closest('#productoResults, #producto_nombre').length) {
                $('#productoResults').hide();
            }
            if (!$(e.target).closest('#ingredienteResults, #ingrediente_nombre').length) {
                $('#ingredienteResults').hide();
            }
        });
    });
</script>
<style>
    /* Estilos para los resultados de búsqueda */
    #productoResults,
    #ingredienteResults {
        position: absolute;
        z-index: 1050;
        /* Mayor que el z-index por defecto de Bootstrap */
        width: calc(100% - 30px);
        /* Ajustar al ancho del input */
        margin-top: 2px;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        background: white;
    }

    /* Items de resultados */
    .product-item {
        transition: all 0.2s;
        border-left: none;
        border-right: none;
    }

    .product-item:hover {
        background-color: #f8f9fa;
        transform: translateX(2px);
    }

    /* Indicador de carga */
    .spinner-border {
        width: 1rem;
        height: 1rem;
        border-width: 0.15em;
    }
</style>

@endpush