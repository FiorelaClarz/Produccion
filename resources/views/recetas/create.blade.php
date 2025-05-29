@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Crear Nueva Receta</h1>

    <!-- Formulario principal para crear recetas -->
    <form id="recetaForm" action="{{ route('recetas.store') }}" method="POST">
        @csrf <!-- Token CSRF para protección contra ataques -->

        <div class="row mb-4">
            <div class="col-md-6">
                <!-- Selección de área -->
                <div class="form-group">
                    <label for="id_areas">Área</label>
                    <select class="form-control @error('id_areas') is-invalid @enderror" id="id_areas" name="id_areas" required>
                        <option value="">Seleccione un área</option>
                        @foreach($areas as $area)
                        <option value="{{ $area->id_areas }}" {{ old('id_areas') == $area->id_areas ? 'selected' : '' }}>
                            {{ $area->nombre }}
                        </option>
                        @endforeach
                    </select>
                    @error('id_areas')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Búsqueda de producto principal con autocompletado -->
                <div class="form-group">
                    <label for="producto_nombre">Producto Principal</label>
                    <div class="position-relative">
                        <input type="text" class="form-control @error('id_productos_api') is-invalid @enderror"
                            id="producto_nombre" name="producto_nombre" placeholder="Buscar producto..."
                            required autocomplete="off"
                            value="{{ old('producto_nombre') }}">
                        <input type="hidden" id="id_productos_api" name="id_productos_api" value="{{ old('id_productos_api') }}">
                        @error('id_productos_api')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <div id="productoResults" class="list-group mt-1"
                            style="display:none; position:absolute; z-index:1000; width:100%; max-height:300px; overflow-y:auto;"></div>
                    </div>
                    <small class="form-text text-muted">Escribe al menos 2 caracteres</small>
                </div>

                <!-- Nombre de la receta (se autocompleta con el nombre del producto) -->
                <div class="form-group">
                    <label for="nombre">Nombre Receta</label>
                    <input type="text" class="form-control @error('nombre') is-invalid @enderror"
                        id="nombre" name="nombre" required readonly
                        value="{{ old('nombre') }}">
                    @error('nombre')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-md-6">
                <!-- Rendimiento de la receta -->
                <div class="form-group">
                    <label for="cant_rendimiento">Rendimiento</label>
                    <input type="number" step="0.01" class="form-control @error('cant_rendimiento') is-invalid @enderror"
                        id="cant_rendimiento" name="cant_rendimiento" required
                        value="{{ old('cant_rendimiento') }}">
                    @error('cant_rendimiento')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Unidad de medida del rendimiento -->
                <div class="form-group">
                    <label for="id_u_medidas">Unidad de Medida</label>
                    <select class="form-control @error('id_u_medidas') is-invalid @enderror"
                        id="id_u_medidas" name="id_u_medidas" required>
                        <option value="">Seleccione una unidad</option>
                        @foreach($unidades as $unidad)
                        <option value="{{ $unidad->id_u_medidas }}" {{ old('id_u_medidas') == $unidad->id_u_medidas ? 'selected' : '' }}>
                            {{ $unidad->nombre }}
                        </option>
                        @endforeach
                    </select>
                    @error('id_u_medidas')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <!-- Constante de crecimiento (para cálculos de costos) -->
                        <div class="form-group">
                            <label for="constante_crecimiento">Constante Crecimiento</label>
                            <input type="number" step="0.01" class="form-control @error('constante_crecimiento') is-invalid @enderror"
                                id="constante_crecimiento" name="constante_crecimiento" required
                                value="{{ old('constante_crecimiento') }}">
                            @error('constante_crecimiento')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <!-- Constante de peso lata (solo visible para área de pan) -->
                        <div class="form-group" id="pesoLataContainer" style="display: none;">
                            <label for="constante_peso_lata">Constante Peso Lata</label>
                            <input type="number" step="0.01" class="form-control @error('constante_peso_lata') is-invalid @enderror"
                                id="constante_peso_lata" name="constante_peso_lata" value="0">
                            @error('constante_peso_lata')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr>

        <!-- Sección de ingredientes -->
        <h3>Ingredientes</h3>

        <!-- Mensajes de error para ingredientes -->
        <div class="alert alert-danger" id="ingredientesError" style="display:none;">
            Debe agregar al menos un ingrediente
        </div>

        <div class="alert alert-danger" id="ingredienteDuplicadoError" style="display:none;">
            Este producto ya ha sido agregado como ingrediente
        </div>

        <div class="alert alert-danger" id="unidadMedidaError" style="display:none;">
            Las cantidades ingresadas deben estar en la misma unidad de medida que la presentación del producto
        </div>

        <!-- Formulario para agregar ingredientes -->
        <div class="row mb-3">
            <div class="col-md-5">
                <div class="form-group">
                    <label for="ingrediente_nombre">Producto Ingrediente</label>
                    <div class="position-relative">
                        <input type="text" class="form-control @error('ingredientes') is-invalid @enderror"
                            id="ingrediente_nombre" placeholder="Buscar producto ingrediente..."
                            autocomplete="off">
                        <input type="hidden" id="ingrediente_id">
                        <div id="ingredienteResults" class="list-group mt-1"
                            style="display:none; position:absolute; z-index:1000; width:100%; max-height:300px; overflow-y:auto;"></div>
                    </div>
                    @error('ingredientes')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label for="ingrediente_u_medida">Unidad de Medida</label>
                    <select class="form-control" id="ingrediente_u_medida">
                        <option value="">Seleccione unidad</option>
                        @foreach($unidades as $unidad)
                        <option value="{{ $unidad->id_u_medidas }}">{{ $unidad->nombre }}</option>
                        @endforeach
                    </select>
                    <small class="text-danger" id="uMedidaError" style="display:none;">Este campo es requerido</small>
                </div>
            </div>


            
            <div class="col-md-2">
                <div class="form-group">
                    <label for="ingrediente_cantidad">Cantidad a usar</label>
                    <input type="number" step="0.01" class="form-control" id="ingrediente_cantidad" min="0.01" value="1">
                    <small class="text-danger" id="cantidadError" style="display:none;">Este campo es requerido</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label for="ingrediente_presentacion">Cantidad de Presentación</label>
                    <input type="number" class="form-control" id="ingrediente_presentacion" min="1" value="1">
                    <small class="text-danger" id="presentacionError" style="display:none;">Este campo es requerido</small>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-10">
                <!-- <div class="form-group">
                    <label for="ingrediente_u_medida">Unidad de Medida</label>
                    <select class="form-control" id="ingrediente_u_medida">
                        <option value="">Seleccione unidad</option>
                        @foreach($unidades as $unidad)
                        <option value="{{ $unidad->id_u_medidas }}">{{ $unidad->nombre }}</option>
                        @endforeach
                    </select>
                    <small class="text-danger" id="uMedidaError" style="display:none;">Este campo es requerido</small>
                </div> -->
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" id="limpiarIngrediente" class="btn btn-secondary mr-2">Limpiar</button>
                <button type="button" id="agregarIngrediente" class="btn btn-primary">Agregar</button>
            </div>
        </div>

        <!-- Tabla de ingredientes agregados -->
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
                    <!-- Aquí se agregarán los ingredientes dinámicamente mediante JavaScript -->
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

        <!-- Campos ocultos para enviar datos al servidor -->
        <input type="hidden" name="ingredientes" id="ingredientesData" value="">
        <input type="hidden" name="costo_receta" id="costoRecetaTotal" value="0">
        <input type="hidden" id="editingIndex" value="-1">

        <!-- Botones de acción -->
        <div class="form-group">
            <button type="submit" class="btn btn-success">Guardar Receta</button>
            <a href="{{ route('recetas.index') }}" class="btn btn-danger">Cancelar</a>
        </div>
    </form>
</div>

<!-- Modal para continuar después de guardar -->
<div class="modal fade" id="continueModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Receta Guardada</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>¿Deseas agregar otra receta?</p>
            </div>
            <div class="modal-footer">
                <a href="{{ route('recetas.create') }}" class="btn btn-primary">Sí, agregar otra</a>
                <a href="{{ route('recetas.index') }}" class="btn btn-secondary">No, ver listado</a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Variables globales para manejar los ingredientes y búsquedas
        let searchXHR = null;
        let ingredientes = <?php echo json_encode(old('ingredientes', [])); ?>;
        let totalSubtotal = 0;
        let currentProductCost = 0;

        // Mostrar/ocultar campo de peso lata según área seleccionada
        $('#id_areas').change(function() {
            const areaId = $(this).val();
            if (areaId == 1) { // 1 es el ID para "pan"
                $('#pesoLataContainer').show();
                $('#constante_peso_lata').prop('required', true);
            } else {
                $('#pesoLataContainer').hide();
                $('#constante_peso_lata').prop('required', false);
                $('#constante_peso_lata').val('0');
            }
        });

        // Trigger change event on page load in case there's a selected value
        $('#id_areas').trigger('change');

        // Función para mostrar resultados de búsqueda
        function showResults(data, container) {
            container.empty();

            if (data.length > 0) {
                data.forEach(item => {
                    const costo = parseFloat(item.costo) || 0;

                    container.append(`
                        <a href="#" class="list-group-item list-group-item-action product-item"
                           data-id="${item.id}"
                           data-nombre="${item.text}"
                           data-codigo="${item.codigo || 'N/A'}"
                           data-costo="${costo}"
                           data-u-medida="${item.id_u_medidas || ''}">
                           <div class="d-flex justify-content-between">
                               <span>${item.text}</span>
                               <small>${item.codigo || 'N/A'}</small>
                           </div>
                           <small>S/ ${costo.toFixed(2)}</small>
                        </a>
                    `);
                });
                container.show();
            } else {
                container.append('<div class="list-group-item">No se encontraron coincidencias</div>').show();
            }
        }

        // Función para buscar productos con AJAX
        function buscarProductos(term, container) {
            if (term.length < 2) {
                container.hide().empty();
                return;
            }

            container.html('<div class="list-group-item">Buscando...</div>').show();

            if (searchXHR) searchXHR.abort();

            searchXHR = $.ajax({
                url: '{{ route("recetas.buscarProductos") }}',
                type: 'GET',
                data: { term: term },
                dataType: 'json',
                success: function(data) {
                    try {
                        if (!Array.isArray(data)) {
                            throw new Error('Respuesta no es un array');
                        }

                        if (data.length === 0) {
                            container.html('<div class="list-group-item text-muted">No se encontraron coincidencias</div>').show();
                        } else {
                            showResults(data, container);
                        }
                    } catch (error) {
                        console.error('Error procesando resultados:', error);
                        container.html('<div class="list-group-item text-danger">Error al mostrar resultados</div>').show();
                    }
                },
                error: function(xhr, status, error) {
                    if (status !== 'abort') {
                        console.error("Error en la búsqueda:", error);
                        container.html('<div class="list-group-item text-danger">Error en la búsqueda</div>').show();
                    }
                }
            });
        }

        // Eventos de búsqueda con debounce para mejor performance
        let searchTimeout = null;
        $('#producto_nombre, #ingrediente_nombre').on('input', function() {
            const term = $(this).val().trim();
            const container = $(this).attr('id') === 'producto_nombre' ?
                $('#productoResults') :
                $('#ingredienteResults');

            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => buscarProductos(term, container), 300);
        });

        // Selección de producto principal
        $(document).on('click', '#productoResults .product-item', function(e) {
            e.preventDefault();
            const $item = $(this);

            const producto = {
                id: $item.data('id'),
                nombre: $item.data('nombre'),
                costo: parseFloat($item.data('costo')) || 0,
                codigo: $item.data('codigo')
            };

            if (!producto.id || !producto.nombre) {
                console.error('Datos de producto incompletos');
                return;
            }

            $('#producto_nombre').val(producto.nombre);
            $('#id_productos_api').val(producto.id);
            $('#nombre').val(producto.nombre).prop('readonly', false);
            $('#productoResults').hide();

            // Verificar si ya existe receta para este producto
            $.get('{{ route("recetas.verificarProducto") }}', {
                    id_producto: producto.id
                })
                .done(function(response) {
                    if (response.tiene_receta) {
                        alert('Ya existe una receta para este producto');
                        $('#producto_nombre').val('').focus();
                        $('#id_productos_api').val('');
                        $('#nombre').val('');
                    }
                });
        });

        // Selección de ingrediente
        $(document).on('click', '#ingredienteResults .product-item', function(e) {
            e.preventDefault();
            const producto = $(this).data();

            $('#ingrediente_nombre').val(producto.nombre);
            $('#ingrediente_id').val(producto.id);
            $('#ingredienteResults').hide();

            currentProductCost = parseFloat(producto.costo) || 0;
            $('#unidadMedidaError').hide();
        });

        // Limpiar campos de ingrediente
        $('#limpiarIngrediente').click(function() {
            $('#ingrediente_nombre').val('');
            $('#ingrediente_id').val('');
            $('#ingrediente_cantidad').val('1');
            $('#ingrediente_presentacion').val('1');
            $('#ingrediente_u_medida').val('');
            $('#ingredienteDuplicadoError').hide();
            $('#unidadMedidaError').hide();
            $('#editingIndex').val('-1');
            $('#agregarIngrediente').text('Agregar');

            $('#cantidadError').hide();
            $('#presentacionError').hide();
            $('#uMedidaError').hide();
        });

        // Validar campos requeridos en tiempo real
        $('#ingrediente_cantidad, #ingrediente_presentacion, #ingrediente_u_medida').on('input change', function() {
            const id = $(this).attr('id');
            const value = $(this).val();

            if (value === '' || (id === 'ingrediente_cantidad' && parseFloat(value) <= 0) ||
                (id === 'ingrediente_presentacion' && parseInt(value) <= 0)) {
                $('#' + id + 'Error').show();
            } else {
                $('#' + id + 'Error').hide();
            }
        });

        // Agregar o actualizar ingrediente a la tabla
        $('#agregarIngrediente').click(function() {
            const id = $('#ingrediente_id').val();
            const nombre = $('#ingrediente_nombre').val();
            const cantidad = parseFloat($('#ingrediente_cantidad').val());
            const presentacion = parseInt($('#ingrediente_presentacion').val());
            const uMedidaId = $('#ingrediente_u_medida').val();
            const uMedidaNombre = $('#ingrediente_u_medida option:selected').text();
            const editingIndex = $('#editingIndex').val();

            // Validaciones
            let isValid = true;

            if (!id || !nombre) {
                alert('Por favor seleccione un producto ingrediente');
                isValid = false;
            }

            if (!cantidad || cantidad <= 0) {
                $('#cantidadError').show();
                isValid = false;
            } else {
                $('#cantidadError').hide();
            }

            if (!presentacion || presentacion <= 0) {
                $('#presentacionError').show();
                isValid = false;
            } else {
                $('#presentacionError').hide();
            }

            if (!uMedidaId) {
                $('#uMedidaError').show();
                isValid = false;
            } else {
                $('#uMedidaError').hide();
            }

            if (!isValid) {
                return;
            }

            // Calcular subtotal según la fórmula: (cantidad / cant_presentacion) * costo_unitario
            const subtotal = (cantidad / presentacion) * currentProductCost;

            // Verificar si estamos editando o agregando nuevo
            if (editingIndex >= 0) {
                // Actualizar ingrediente existente (mantener el costo original)
                ingredientes[editingIndex] = {
                    id_productos_api: id,
                    nombre: nombre,
                    cantidad: cantidad,
                    cant_presentacion: presentacion,
                    id_u_medidas: uMedidaId,
                    u_medida: uMedidaNombre,
                    costo_unitario: ingredientes[editingIndex].costo_unitario,
                    subtotal: subtotal
                };
            } else {
                // Verificar si el ingrediente ya existe
                if (ingredientes.some(ing => ing.id_productos_api === id)) {
                    $('#ingredienteDuplicadoError').show();
                    return;
                } else {
                    $('#ingredienteDuplicadoError').hide();
                }

                // Agregar nuevo ingrediente
                ingredientes.push({
                    id_productos_api: id,
                    nombre: nombre,
                    cantidad: cantidad,
                    cant_presentacion: presentacion,
                    id_u_medidas: uMedidaId,
                    u_medida: uMedidaNombre,
                    costo_unitario: currentProductCost,
                    subtotal: subtotal
                });
            }

            // Actualizar tabla
            updateIngredientesTable();

            // Limpiar campos
            $('#limpiarIngrediente').click();
        });

        // Actualizar tabla de ingredientes y calcular totales
        function updateIngredientesTable() {
            const tableBody = $('#ingredientesTable');
            tableBody.empty();
            totalSubtotal = 0;

            if (ingredientes.length === 0) {
                tableBody.append('<tr><td colspan="8" class="text-center">No hay ingredientes agregados</td></tr>');
                $('#subtotalTotal').text('S/ 0.00');
                $('#ingredientesData').val(JSON.stringify([]));
                return;
            }

            // Mostrar los ingredientes
            ingredientes.forEach((ingrediente, index) => {
                totalSubtotal += ingrediente.subtotal;

                tableBody.append(`
                    <tr data-index="${index}">
                        <td>${ingrediente.id_productos_api}</td>
                        <td>${ingrediente.nombre}</td>
                        <td>${ingrediente.cantidad.toFixed(2)}</td>
                        <td>${ingrediente.cant_presentacion}</td>
                        <td>${ingrediente.u_medida}</td>
                        <td>S/ ${ingrediente.costo_unitario.toFixed(2)}</td>
                        <td>S/ ${ingrediente.subtotal.toFixed(2)}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary editar-ingrediente mr-1">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger eliminar-ingrediente">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });

            // Actualizar total y el campo oculto para costo_receta
            $('#subtotalTotal').text('S/ ' + totalSubtotal.toFixed(2));
            $('#costoRecetaTotal').val(totalSubtotal.toFixed(2));

            // Preparar datos para enviar al servidor
            const datosParaEnviar = ingredientes.map(ing => ({
                id_productos_api: ing.id_productos_api,
                cantidad: ing.cantidad,
                cant_presentacion: ing.cant_presentacion,
                id_u_medidas: ing.id_u_medidas
            }));
            $('#ingredientesData').val(JSON.stringify(datosParaEnviar));
        }

        // Editar ingrediente existente
        $(document).on('click', '.editar-ingrediente', function() {
            const row = $(this).closest('tr');
            const index = row.data('index');
            const ingrediente = ingredientes[index];

            // Llenar formulario con los datos del ingrediente
            $('#ingrediente_nombre').val(ingrediente.nombre);
            $('#ingrediente_id').val(ingrediente.id_productos_api);
            $('#ingrediente_cantidad').val(ingrediente.cantidad);
            $('#ingrediente_presentacion').val(ingrediente.cant_presentacion);
            $('#ingrediente_u_medida').val(ingrediente.id_u_medidas);

            currentProductCost = ingrediente.costo_unitario;
            $('#agregarIngrediente').text('Actualizar');
            $('#editingIndex').val(index);

            // Scroll al formulario de ingredientes
            $('html, body').animate({
                scrollTop: $('#ingrediente_nombre').offset().top - 100
            }, 500);
        });

        // Eliminar ingrediente
        $(document).on('click', '.eliminar-ingrediente', function() {
            const row = $(this).closest('tr');
            const index = row.data('index');

            ingredientes.splice(index, 1);
            updateIngredientesTable();
        });

        // Validación en tiempo real del formulario principal
        function validateFormFields() {
            let isValid = true;

            // Validar campos requeridos
            $('[required]').each(function() {
                const $field = $(this);
                if ($field.val() === '') {
                    $field.addClass('is-invalid');
                    isValid = false;
                } else {
                    $field.removeClass('is-invalid');
                }
            });

            return isValid;
        }

        // Validación completa del formulario de receta
        function validateRecetaForm() {
            let isValid = true;
            $('#ingredientesError').hide();

            // Validar campos del formulario
            isValid = validateFormFields();

            // Validar producto principal
            if ($('#id_productos_api').val() === '') {
                $('#producto_nombre').addClass('is-invalid');
                isValid = false;
            } else {
                $('#producto_nombre').removeClass('is-invalid');
            }

            // Validar ingredientes
            if (ingredientes.length === 0) {
                $('#ingredientesError').show();
                isValid = false;
            }

            return isValid;
        }

        // Validar al enviar el formulario
        $('#recetaForm').on('submit', function(e) {
            if (!validateRecetaForm()) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: $('.is-invalid').first().offset().top - 100
                }, 500);
            }
        });

        // Validación en tiempo real para campos requeridos
        $('input[required], select[required]').on('input change', function() {
            if ($(this).val() === '') {
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        // Ocultar resultados al hacer clic fuera
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#producto_nombre, #productoResults').length) {
                $('#productoResults').hide();
            }

            if (!$(e.target).closest('#ingrediente_nombre, #ingredienteResults').length) {
                $('#ingredienteResults').hide();
            }
        });
        
        // Mostrar modal si existe la variable de sesión
        @if(session('show_continue_modal'))
        $('#continueModal').modal('show');
        @endif
    });
</script>

<style>
    /* Estilos para los resultados de búsqueda */
    #productoResults,
    #ingredienteResults {
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

    .product-item {
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .product-item:hover {
        background-color: #f8f9fa;
    }

    .form-group {
        position: relative;
    }

    /* Estilos para mensajes de error */
    #ingredientesError,
    #ingredienteDuplicadoError,
    #unidadMedidaError {
        margin-bottom: 1rem;
    }

    /* Estilos para botones de acciones */
    .eliminar-ingrediente,
    .editar-ingrediente {
        padding: 0.25rem 0.5rem;
    }

    /* Transición para el contenedor de peso lata */
    #pesoLataContainer {
        transition: all 0.3s ease;
    }

    /* Estilos para mensajes de error pequeños */
    .text-danger {
        font-size: 0.875em;
    }

    /* Clase para campos inválidos */
    .is-invalid {
        border-color: #dc3545;
    }
</style>
@endpush