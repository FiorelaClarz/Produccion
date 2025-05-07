@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3>Crear Instructivo para: {{ $receta->nombre }}</h3>
                    <p class="mb-0">Producto: {{ $receta->producto->nombre }}</p>
                </div>
                
                <div class="card-body">
                    <form id="instructivoForm" action="{{ route('recetas.store-instructivo', $receta->id_recetas) }}" method="POST">
                        @csrf
                        
                        <div class="form-group">
                            <label for="titulo">Título del Instructivo</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" 
                                   value="{{ $receta->nombre }} - Instructivo" required>
                            @error('titulo')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="ingredientes-disponibles mb-4">
                            <h5>Ingredientes Disponibles:</h5>
                            <div class="d-flex flex-wrap">
                                @foreach($receta->detalles as $detalle)
                                <div class="ingrediente-item mr-3 mb-2" 
                                     data-id="{{ $detalle->id_productos_api }}"
                                     data-nombre="{{ $detalle->producto->nombre }}"
                                     data-cantidad="{{ $detalle->cantidad }}"
                                     data-u-medida="{{ $detalle->uMedida->nombre }}">
                                    <span class="badge badge-info">
                                        {{ $detalle->producto->nombre }} ({{ $detalle->cantidad }} {{ $detalle->uMedida->nombre }})
                                    </span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        
                        <h4 class="mb-3">Pasos del Instructivo</h4>
                        
                        <div id="pasosContainer">
                            <!-- Los pasos se agregarán dinámicamente aquí -->
                        </div>
                        
                        <button type="button" id="agregarPaso" class="btn btn-secondary mb-4">
                            <i class="fas fa-plus"></i> Agregar Paso
                        </button>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save"></i> Guardar Instructivo
                            </button>
                            <a href="{{ route('recetas.show', $receta->id_recetas) }}" class="btn btn-danger">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para seleccionar ingredientes -->
<div class="modal fade" id="ingredientesModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Seleccionar Ingredientes</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalIngredientesBody">
                <!-- Contenido dinámico -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="confirmarIngredientes">Confirmar</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let pasoCounter = 1;
    let currentPasoIndex = null;
    let selectedIngredientes = [];
    
    // Agregar primer paso al cargar
    agregarPaso();
    
    // Función para agregar un nuevo paso
    function agregarPaso() {
        const pasoId = `paso-${pasoCounter}`;
        const pasoNumero = pasoCounter;
        
        const pasoHTML = `
        <div class="card mb-3 paso-card" id="${pasoId}" data-paso="${pasoNumero}">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Paso ${pasoNumero}</h5>
                <button type="button" class="btn btn-sm btn-danger eliminar-paso" ${pasoCounter === 1 ? 'disabled' : ''}>
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="contenido-${pasoNumero}">Instrucciones</label>
                    <textarea class="form-control paso-contenido" id="contenido-${pasoNumero}" 
                              name="pasos[${pasoNumero - 1}][contenido]" rows="3" required></textarea>
                    <div class="invalid-feedback paso-error-${pasoNumero}"></div>
                </div>
                
                <div class="ingredientes-seleccionados mb-3" id="ingredientes-${pasoNumero}">
                    <small class="text-muted">Ingredientes usados en este paso:</small>
                    <div class="d-flex flex-wrap ingredientes-container"></div>
                </div>
                
                <button type="button" class="btn btn-sm btn-outline-primary agregar-ingrediente" data-paso="${pasoNumero}">
                    <i class="fas fa-plus"></i> Agregar Ingrediente
                </button>
                
                <input type="hidden" name="pasos[${pasoNumero - 1}][ingredientes]" class="ingredientes-data" value="[]">
            </div>
        </div>
        `;
        
        $('#pasosContainer').append(pasoHTML);
        pasoCounter++;
        reordenarPasos();
    }
    
    // Reordenar pasos después de eliminar uno
    function reordenarPasos() {
        $('.paso-card').each(function(index) {
            const newPasoNumero = index + 1;
            const $card = $(this);
            
            $card.attr('data-paso', newPasoNumero);
            $card.find('.card-header h5').text(`Paso ${newPasoNumero}`);
            $card.find('.paso-contenido').attr('name', `pasos[${index}][contenido]`);
            $card.find('.agregar-ingrediente').attr('data-paso', newPasoNumero);
            $card.find('.ingredientes-data').attr('name', `pasos[${index}][ingredientes]`);
            
            // Habilitar botón de eliminar si hay más de un paso
            $card.find('.eliminar-paso').prop('disabled', newPasoNumero === 1);
        });
    }
    
    // Agregar nuevo paso
    $('#agregarPaso').click(function() {
        agregarPaso();
    });
    
    // Eliminar paso
    $(document).on('click', '.eliminar-paso', function() {
        $(this).closest('.paso-card').remove();
        reordenarPasos();
    });
    
    // Abrir modal para seleccionar ingredientes
    $(document).on('click', '.agregar-ingrediente', function() {
        currentPasoIndex = $(this).data('paso');
        try {
            selectedIngredientes = JSON.parse($(`#ingredientes-${currentPasoIndex} .ingredientes-data`).val() || '[]');
        } catch(e) {
            selectedIngredientes = [];
        }
        
        // Llenar modal con ingredientes disponibles
        const modalBody = $('#modalIngredientesBody');
        modalBody.empty();
        
        $('.ingrediente-item').each(function() {
            const $ing = $(this);
            const id = $ing.data('id');
            const nombre = $ing.data('nombre');
            const cantidad = $ing.data('cantidad');
            const uMedida = $ing.data('u-medida');
            
            const isSelected = selectedIngredientes.some(ing => ing.id == id);
            
            modalBody.append(`
                <div class="form-check mb-2">
                    <input class="form-check-input ingrediente-checkbox" type="checkbox" 
                           id="ing-${id}-${currentPasoIndex}" value="${id}" ${isSelected ? 'checked' : ''}>
                    <label class="form-check-label" for="ing-${id}-${currentPasoIndex}">
                        ${nombre} (${cantidad} ${uMedida})
                    </label>
                </div>
            `);
        });
        
        $('#ingredientesModal').modal('show');
    });
    
    // Confirmar selección de ingredientes
    // Confirmar selección de ingredientes
$('#confirmarIngredientes').click(function() {
    selectedIngredientes = [];
    
    $('#modalIngredientesBody .ingrediente-checkbox:checked').each(function() {
        const id = $(this).val();
        const $ing = $(`.ingrediente-item[data-id="${id}"]`);
        
        selectedIngredientes.push({
            id: id,
            nombre: $ing.data('nombre'),
            cantidad: $ing.data('cantidad'),
            u_medida: $ing.data('u-medida')
        });
    });
    
    // Actualizar vista con ingredientes seleccionados
    const $container = $(`#ingredientes-${currentPasoIndex} .ingredientes-container`);
    $container.empty();
    
    selectedIngredientes.forEach(ing => {
        $container.append(`
            <span class="badge badge-info mr-2 mb-2">
                ${ing.nombre} (${ing.cantidad} ${ing.u_medida})
            </span>
        `);
    });
    
    // Actualizar campo oculto con los datos, asegurando que siempre sea un array
    const ingredientesValue = selectedIngredientes.length > 0 ? JSON.stringify(selectedIngredientes) : '[]';
    $(`#ingredientes-${currentPasoIndex} .ingredientes-data`).val(ingredientesValue);
    
    $('#ingredientesModal').modal('hide');
});
    
    // Manejar el envío del formulario
    // En la función que maneja el submit del formulario, reemplaza con esto:
$('#instructivoForm').submit(function(e) {
    e.preventDefault();
    let isValid = true;
    
    // Validar todos los pasos
    $('.paso-contenido').each(function() {
        if ($(this).val().trim() === '') {
            $(this).addClass('is-invalid');
            isValid = false;
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    if (!isValid) {
        alert('Por favor complete todos los pasos antes de guardar.');
        $('.is-invalid').first().focus();
        return;
    }
    
    // Preparar los datos correctamente
    let formData = {
        titulo: $('#titulo').val(),
        pasos: []
    };
    
    $('.paso-card').each(function(index) {
        let ingredientesData = $(this).find('.ingredientes-data').val();
        let ingredientes = [];
        
        try {
            ingredientes = ingredientesData ? JSON.parse(ingredientesData) : [];
        } catch(e) {
            console.error('Error parsing ingredientes:', e);
            ingredientes = [];
        }
        
        formData.pasos.push({
            contenido: $(this).find('.paso-contenido').val(),
            ingredientes: ingredientes
        });
    });
    
    console.log('Datos a enviar:', formData);
    
    // Enviar con AJAX
    $.ajax({
        url: $(this).attr('action'),
        method: 'POST',
        data: JSON.stringify(formData),
        contentType: 'application/json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        },
        success: function(response) {
            window.location.href = "{{ route('recetas.show', $receta->id_recetas) }}";
        },
        error: function(xhr) {
            console.error('Error:', xhr.responseText);
            let errorMsg = 'Error al guardar el instructivo';
            if(xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            alert(errorMsg);
        }
    });
});
});
</script>

<style>
    .paso-card {
        transition: all 0.3s ease;
    }
    
    .paso-card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    .ingrediente-item {
        cursor: pointer;
        transition: transform 0.2s;
    }
    
    .ingrediente-item:hover {
        transform: scale(1.05);
    }
    
    .ingredientes-seleccionados {
        min-height: 50px;
        border: 1px dashed #ccc;
        border-radius: 5px;
        padding: 10px;
    }
    
    .card-header {
        background-color: #f8f9fa;
    }
    
    .is-invalid {
        border-color: #dc3545;
    }
    
    .invalid-feedback {
        display: none;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 80%;
        color: #dc3545;
    }
    
    .is-invalid ~ .invalid-feedback {
        display: block;
    }
</style>
@endpush