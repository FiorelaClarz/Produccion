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
                <div class="form-group">
                    <label for="cant_rendimiento">Rendimiento</label>
                    <input type="number" step="0.01" class="form-control @error('cant_rendimiento') is-invalid @enderror"
                        id="cant_rendimiento" name="cant_rendimiento" required
                        value="{{ old('cant_rendimiento') }}">
                    @error('cant_rendimiento')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

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
                        <div class="form-group">
                            <label for="constante_peso_lata">Constante Peso Lata</label>
                            <input type="number" step="0.01" class="form-control @error('constante_peso_lata') is-invalid @enderror"
                                id="constante_peso_lata" name="constante_peso_lata" required
                                value="{{ old('constante_peso_lata') }}">
                            @error('constante_peso_lata')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr>

        <h3>Ingredientes</h3>

        <div class="alert alert-danger" id="ingredientesError" style="display:none;">
            Debe agregar al menos un ingrediente
        </div>

        <div class="alert alert-danger" id="ingredienteDuplicadoError" style="display:none;">
            Este producto ya ha sido agregado como ingrediente
        </div>

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
                    <label for="ingrediente_cantidad">Cantidad</label>
                    <input type="number" step="0.01" class="form-control" id="ingrediente_cantidad" min="0.01" value="1">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label for="ingrediente_presentacion">Presentación</label>
                    <input type="number" class="form-control" id="ingrediente_presentacion" min="1" value="1">
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

        <input type="hidden" name="ingredientes" id="ingredientesData" value="{{ old('ingredientes') }}">

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
        // Buscador de producto principal
        new BuscadorAjax({
            inputSelector: '#producto_nombre',
            resultsContainerSelector: '#productoResults',
            endpoint: '{{ route("recetas.buscarProductos") }}',
            minChars: 2,
            template: (producto) => {
                const costo = parseFloat(producto.costo) || 0;
                return `
                    <div class="d-flex justify-content-between">
                        <span>${producto.text}</span>
                        <small>${producto.codigo || 'N/A'}</small>
                    </div>
                    <small>S/ ${costo.toFixed(2)}</small>
                `;
            },
            onSelect: (producto) => {
                $('#producto_nombre').val(producto.text);
                $('#id_productos_api').val(producto.id);
                $('#nombre').val(producto.text).prop('readonly', false);

                // Verificar si ya existe receta
                $.get('{{ route("recetas.verificarProducto") }}', {
                    id_producto: producto.id,
                    _token: '{{ csrf_token() }}'
                }).done(response => {
                    if (response.tiene_receta) {
                        alert('Ya existe una receta para este producto');
                        $('#producto_nombre').val('').focus();
                        $('#id_productos_api').val('');
                        $('#nombre').val('');
                    }
                }).fail(error => {
                    console.error('Error verificando producto:', error);
                });
            }
        });

        // Buscador de ingredientes
        new BuscadorAjax({
            inputSelector: '#ingrediente_nombre',
            resultsContainerSelector: '#ingredienteResults',
            endpoint: '{{ route("recetas.buscarProductos") }}',
            minChars: 2,
            template: (producto) => {
                const costo = parseFloat(producto.costo) || 0;
                return `
                    <div class="d-flex justify-content-between">
                        <span>${producto.text}</span>
                        <small>${producto.codigo || 'N/A'}</small>
                    </div>
                    <small>S/ ${costo.toFixed(2)}</small>
                `;
            },
            onSelect: (producto) => {
                $('#ingrediente_nombre').val(producto.text);
                $('#ingrediente_id').val(producto.id);
                
                // Sugerir unidad de medida si es posible
                if ($('#ingrediente_u_medida').val() === '') {
                    const defaultUnidadId = '{{ $unidades->first()->id_u_medidas ?? "" }}';
                    if (defaultUnidadId) {
                        $('#ingrediente_u_medida').val(defaultUnidadId);
                    }
                }
            }
        });

        // Variables globales para ingredientes
        let ingredientes = [];
        let totalSubtotal = 0;

        // Limpiar campos de ingrediente
        $('#limpiarIngrediente').click(function() {
            $('#ingrediente_nombre').val('');
            $('#ingrediente_id').val('');
            $('#ingrediente_cantidad').val('1');
            $('#ingrediente_presentacion').val('1');
            $('#ingrediente_u_medida').val('');
            $('#ingredienteDuplicadoError').hide();
        });

        // Agregar ingrediente a la tabla
        $('#agregarIngrediente').click(function() {
            const id = $('#ingrediente_id').val();
            const nombre = $('#ingrediente_nombre').val();
            const cantidad = parseFloat($('#ingrediente_cantidad').val());
            const presentacion = parseInt($('#ingrediente_presentacion').val());
            const uMedidaId = $('#ingrediente_u_medida').val();
            const uMedidaNombre = $('#ingrediente_u_medida option:selected').text();

            // Validaciones
            if (!id || !nombre) {
                alert('Por favor seleccione un producto');
                return;
            }

            if (!cantidad || cantidad <= 0) {
                alert('La cantidad debe ser mayor a 0');
                $('#ingrediente_cantidad').focus();
                return;
            }

            if (!presentacion || presentacion <= 0) {
                alert('La presentación debe ser mayor a 0');
                $('#ingrediente_presentacion').focus();
                return;
            }

            if (!uMedidaId) {
                alert('Por favor seleccione una unidad de medida');
                $('#ingrediente_u_medida').focus();
                return;
            }

            // Verificar si el ingrediente ya existe
            if (ingredientes.some(ing => ing.id_productos_api === id)) {
                $('#ingredienteDuplicadoError').show();
                return;
            }

            // Obtener costo del producto
            const costoItem = $('#ingredienteResults').find('.result-item[data-id="'+id+'"]').data('costo');
            const costo = parseFloat(costoItem) || 0;
            const subtotal = cantidad * costo;

            // Agregar a la lista de ingredientes
            ingredientes.push({
                id_productos_api: id,
                nombre: nombre,
                cantidad: cantidad,
                cant_presentacion: presentacion,
                id_u_medidas: uMedidaId,
                u_medida: uMedidaNombre,
                costo_unitario: costo,
                subtotal: subtotal
            });

            // Actualizar tabla
            updateIngredientesTable();

            // Limpiar campos
            $('#limpiarIngrediente').click();
        });

        // Actualizar tabla de ingredientes
        function updateIngredientesTable() {
            const tableBody = $('#ingredientesTable');
            tableBody.empty();
            totalSubtotal = 0;

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
                            <button type="button" class="btn btn-sm btn-danger eliminar-ingrediente">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });

            // Actualizar total
            $('#subtotalTotal').text('S/ ' + totalSubtotal.toFixed(2));

            // Actualizar campo hidden para el formulario
            $('#ingredientesData').val(JSON.stringify(ingredientes));
            
            // Ocultar mensaje de error si hay ingredientes
            if (ingredientes.length > 0) {
                $('#ingredientesError').hide();
            }
        }

        // Eliminar ingrediente
        $(document).on('click', '.eliminar-ingrediente', function() {
            const row = $(this).closest('tr');
            const index = row.data('index');
            
            ingredientes.splice(index, 1);
            updateIngredientesTable();
        });

        // Validación en tiempo real
        function validateRecetaForm() {
            let isValid = true;
            
            // Validar producto principal
            if ($('#id_productos_api').val() === '') {
                $('#producto_nombre').addClass('is-invalid');
                isValid = false;
            } else {
                $('#producto_nombre').removeClass('is-invalid');
            }

            // Validar área
            if ($('#id_areas').val() === '') {
                $('#id_areas').addClass('is-invalid');
                isValid = false;
            } else {
                $('#id_areas').removeClass('is-invalid');
            }

            // Validar unidad de medida
            if ($('#id_u_medidas').val() === '') {
                $('#id_u_medidas').addClass('is-invalid');
                isValid = false;
            } else {
                $('#id_u_medidas').removeClass('is-invalid');
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

        // Validar campos al cambiar
        $('#id_areas, #id_u_medidas').on('change', function() {
            if ($(this).val() !== '') {
                $(this).removeClass('is-invalid');
            }
        });
    });
</script>

<style>
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

    .result-item {
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .result-item:hover {
        background-color: #f8f9fa;
    }

    .form-group {
        position: relative;
    }

    #ingredientesError,
    #ingredienteDuplicadoError {
        margin-bottom: 1rem;
    }

    .eliminar-ingrediente {
        padding: 0.25rem 0.5rem;
    }
</style>
@endpush