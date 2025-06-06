@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Receta</h1>

    <!-- Formulario para editar receta existente -->
    <form id="recetaForm" action="{{ route('recetas.update', $receta->id_recetas) }}" method="POST">
        @csrf
        @method('PUT') <!-- Método HTTP PUT para actualización -->

        <div class="row mb-4">
            <div class="col-md-6">
                <!-- Selección de área -->
                <div class="form-group">
                    <label for="id_areas">Área</label>
                    <select class="form-control @error('id_areas') is-invalid @enderror" id="id_areas" name="id_areas" required>
                        <option value="">Seleccione un área</option>
                        @foreach($areas as $area)
                        <option value="{{ $area->id_areas }}" {{ $receta->id_areas == $area->id_areas ? 'selected' : '' }}>
                            {{ $area->nombre }}
                        </option>
                        @endforeach
                    </select>
                    @error('id_areas')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Producto principal (solo lectura ya que no se puede cambiar) -->
                <div class="form-group">
                    <label for="producto_nombre">Producto Principal</label>
                    <div class="position-relative">
                        <input type="text" class="form-control @error('id_productos_api') is-invalid @enderror"
                            id="producto_nombre" name="producto_nombre" placeholder="Buscar producto..."
                            required autocomplete="off"
                            value="{{ $receta->producto->nombre }}" readonly>
                        <input type="hidden" id="id_productos_api" name="id_productos_api" value="{{ $receta->id_productos_api }}">
                        @error('id_productos_api')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <div id="productoResults" class="list-group mt-1"
                            style="display:none; position:absolute; z-index:1000; width:100%; max-height:300px; overflow-y:auto;"></div>
                    </div>
                </div>

                <!-- Nombre de la receta (editable) -->
                <div class="form-group">
                    <label for="nombre">Nombre Receta</label>
                    <input type="text" class="form-control @error('nombre') is-invalid @enderror"
                        id="nombre" name="nombre" required
                        value="{{ $receta->nombre }}">
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
                        value="{{ $receta->cant_rendimiento }}">
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
                        <option value="{{ $unidad->id_u_medidas }}" {{ $receta->id_u_medidas == $unidad->id_u_medidas ? 'selected' : '' }}>
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
                        <!-- Constante de crecimiento -->
                        <div class="form-group">
                            <label for="constante_crecimiento">Constante Crecimiento</label>
                            <input type="number" step="0.01" class="form-control @error('constante_crecimiento') is-invalid @enderror"
                                id="constante_crecimiento" name="constante_crecimiento" required
                                value="{{ $receta->constante_crecimiento }}">
                            @error('constante_crecimiento')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <!-- Constante de peso lata (solo visible para área de pan) -->
                        <div class="form-group" id="pesoLataContainer" style="{{ $receta->id_areas == 1 ? '' : 'display: none;' }}">
                            <label for="constante_peso_lata">Constante Peso Lata</label>
                            <input type="number" step="0.01" class="form-control @error('constante_peso_lata') is-invalid @enderror"
                                id="constante_peso_lata" name="constante_peso_lata"
                                value="{{ $receta->constante_peso_lata ?? 0 }}"
                                {{ $receta->id_areas == 1 ? 'required' : '' }}>
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

        <!-- Formulario para agregar nuevos ingredientes -->
        <div class="row mb-3">
            <div class="col-md-8">
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
            <div class="col-md-8">
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
            <div class="col-md-4 d-flex align-items-end">
                <button type="button" id="limpiarIngrediente" class="btn btn-secondary mr-2">Limpiar</button>
                <button type="button" id="agregarIngrediente" class="btn btn-primary">Agregar</button>
            </div>
        </div>

        <!-- Tabla de ingredientes -->
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
                    <!-- Aquí se cargarán los ingredientes dinámicamente mediante JavaScript -->
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6" class="text-right"><strong>Total:</strong></td>
                        <td id="subtotalTotal">S/ 0.00</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Campos ocultos para enviar datos al servidor -->
        <input type="hidden" name="ingredientes" id="ingredientesData" value="">
        <input type="hidden" name="costo_receta" id="costoRecetaTotal" value="{{ $receta->costo_receta }}">
        <input type="hidden" id="editingIndex" value="-1">

        <!-- Botones de acción -->
        <div class="form-group">
            <button type="submit" class="btn btn-success">Actualizar Receta</button>
            <a href="{{ route('recetas.index') }}" class="btn btn-danger">Cancelar</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Variables globales
        let searchXHR = null;
        let ingredientes = []; // Almacena los ingredientes actuales
        let ingredientesOriginales = []; // Almacena los ingredientes originales para comparación
        let totalSubtotal = 0; // Total de costos de ingredientes
        let currentProductCost = 0; // Costo del producto seleccionado actualmente

        // Cargar ingredientes existentes desde la receta
        @foreach($receta->detalles as $detalle)
            ingredientes.push({
                id_productos_api: {{ $detalle->id_productos_api ?? 0 }},
                nombre: `{{ addslashes($detalle->producto->nombre ?? '') }}`,
                cantidad: {{ $detalle->cantidad ?? 0 }},
                cant_presentacion: {{ $detalle->cant_presentacion ?? 1 }},
                id_u_medidas: {{ $detalle->id_u_medidas ?? 0 }},
                u_medida: `{{ addslashes($detalle->uMedida->nombre ?? '') }}`,
                costo_unitario: {{ $detalle->costo_unitario ?? 0 }},
                subtotal: {{ $detalle->subtotal_receta ?? 0 }},
                esNuevo: false, // Indica que es un ingrediente existente
                fueModificado: false // Indica si ha sido modificado
            });
            
            // Guardar copia de los valores originales para comparación
            ingredientesOriginales.push({
                id_productos_api: {{ $detalle->id_productos_api ?? 0 }},
                cantidad: {{ $detalle->cantidad ?? 0 }},
                cant_presentacion: {{ $detalle->cant_presentacion ?? 1 }},
                id_u_medidas: {{ $detalle->id_u_medidas ?? 0 }}
            });
            
            // Sumar al total
            totalSubtotal += {{ $detalle->subtotal_receta ?? 0 }};
        @endforeach

        // Actualizar tabla al cargar la página
        updateIngredientesTable();

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

        // Función para mostrar resultados de búsqueda de productos
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

        // Función para buscar productos mediante AJAX
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

        // Evento de búsqueda con debounce para mejor performance
        let searchTimeout = null;
        $('#ingrediente_nombre').on('input', function() {
            const term = $(this).val().trim();
            const container = $('#ingredienteResults');

            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => buscarProductos(term, container), 300);
        });

        // Selección de ingrediente desde los resultados de búsqueda
        $(document).on('click', '#ingredienteResults .product-item', function(e) {
            e.preventDefault();
            const producto = $(this).data();

            $('#ingrediente_nombre').val(producto.nombre);
            $('#ingrediente_id').val(producto.id);
            $('#ingredienteResults').hide();

            // Guardar el costo del producto seleccionado
            currentProductCost = parseFloat(producto.costo) || 0;

            // Ocultar alerta de unidad de medida
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

            // Ocultar mensajes de error
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

        // Agregar o actualizar ingrediente
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
                // Actualizar ingrediente existente
                const ingredienteOriginal = ingredientesOriginales.find(ing =>
                    ing.id_productos_api == ingredientes[editingIndex].id_productos_api);

                // Verificar si hubo cambios
                const fueModificado = !ingredienteOriginal ||
                    ingredienteOriginal.cantidad != cantidad ||
                    ingredienteOriginal.cant_presentacion != presentacion ||
                    ingredienteOriginal.id_u_medidas != uMedidaId;

                ingredientes[editingIndex] = {
                    id_productos_api: id,
                    nombre: nombre,
                    cantidad: cantidad,
                    cant_presentacion: presentacion,
                    id_u_medidas: uMedidaId,
                    u_medida: uMedidaNombre,
                    costo_unitario: ingredientes[editingIndex].costo_unitario,
                    subtotal: subtotal,
                    esNuevo: false,
                    fueModificado: fueModificado
                };
            } else {
                // Agregar nuevo ingrediente
                ingredientes.push({
                    id_productos_api: id,
                    nombre: nombre,
                    cantidad: cantidad,
                    cant_presentacion: presentacion,
                    id_u_medidas: uMedidaId,
                    u_medida: uMedidaNombre,
                    costo_unitario: currentProductCost,
                    subtotal: subtotal,
                    esNuevo: true,
                    fueModificado: true
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

            // Verificar si hay ingredientes para mostrar
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
                id_u_medidas: ing.id_u_medidas,
                esNuevo: ing.esNuevo,
                fueModificado: ing.fueModificado
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

            // Guardar el costo del producto que se está editando
            currentProductCost = ingrediente.costo_unitario;

            // Cambiar texto del botón a "Actualizar"
            $('#agregarIngrediente').text('Actualizar');

            // Guardar índice del ingrediente que estamos editando
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

        // Validación del formulario principal
        function validateFormFields() {
            let isValid = true;
            let camposFallidos = [];

            // Validar campos requeridos
            $('[required]').each(function() {
                const $field = $(this);
                const fieldId = $field.attr('id') || $field.attr('name');
                
                if ($field.val() === '') {
                    $field.addClass('is-invalid');
                    isValid = false;
                    camposFallidos.push(fieldId);
                    console.log('Campo requerido vacío:', fieldId);
                } else {
                    $field.removeClass('is-invalid');
                }
            });

            if (!isValid) {
                console.warn('Validación de campos falló. Campos faltantes:', camposFallidos);
            } else {
                console.log('Todos los campos requeridos están completos');
            }
            
            return isValid;
        }

        // Validación completa del formulario
        function validateRecetaForm() {
            console.log('Iniciando validación del formulario completo...');
            let isValid = true;
            $('#ingredientesError').hide();

            // Validar campos del formulario
            const camposValidos = validateFormFields();
            if (!camposValidos) {
                console.warn('La validación de campos requeridos falló');
                isValid = false;
            }

            // Validar ingredientes
            console.log('Validando ingredientes. Cantidad actual:', ingredientes.length);
            if (ingredientes.length === 0) {
                $('#ingredientesError').show();
                console.warn('No hay ingredientes agregados a la receta');
                isValid = false;
            }

            console.log('Resultado final de validación:', isValid ? 'Éxito' : 'Fallido');
            return isValid;
        }

        // Validar al enviar el formulario
        $('#recetaForm').on('submit', function(e) {
            console.log('Enviando formulario...');
            e.preventDefault(); // Detener el envío normal
            
            // Convertir el array de ingredientes a JSON y asignarlo al campo oculto
            const ingredientesJSON = JSON.stringify(ingredientes);
            $('#ingredientesData').val(ingredientesJSON);
            console.log('Ingredientes serializados:', ingredientesJSON);
            console.log('Número de ingredientes:', ingredientes.length);
            
            // Asegurarse de que la ruta está correcta
            const formAction = $(this).attr('action');
            const formMethod = $(this).attr('method');
            console.log('Ruta de envío:', formAction);
            console.log('Método HTTP:', formMethod);
            
            // Simplemente enviar el formulario con una redirección manual
            $.ajax({
                url: formAction,
                type: formMethod,
                data: $(this).serialize(),
                success: function(response) {
                    console.log('Respuesta recibida');
                    // Redirigir a la lista de recetas
                    window.location.href = '{{ route("recetas.index") }}';
                },
                error: function(xhr, status, error) {
                    console.error('Error en la solicitud:', error);
                    
                    // Intentar mostrar la respuesta del servidor
                    if (xhr.responseText) {
                        console.log('Respuesta del servidor:', xhr.responseText);
                    }
                    
                    // Si la respuesta contiene HTML (posiblemente una redirección)
                    if (xhr.responseText && xhr.responseText.includes('<html')) {
                        console.log('La respuesta parece ser HTML, posiblemente una redirección');
                        window.location.href = '{{ route("recetas.index") }}';
                        return;
                    }
                    
                    alert('Error al actualizar la receta. Por favor, revisa la consola para más detalles.');
                }
            });
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
            if (!$(e.target).closest('#ingrediente_nombre, #ingredienteResults').length) {
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
        z-index: 1000;
        width: calc(100% - 30px);
        max-height: 300px;
        overflow-y: auto;
        background: white;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    /* Estilos para items de productos */
    .product-item {
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .product-item:hover {
        background-color: #f8f9fa;
    }

    /* Posicionamiento relativo para grupos de formulario */
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

    /* Transición para contenedor de peso lata */
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
@endsection