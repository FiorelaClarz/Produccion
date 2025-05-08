@extends('layouts.app')

@section('content')
<div class="container">
    @if(!$equipoActivo)
    <!-- Notificación centrada para ingresar equipo de trabajo -->
    <div class="modal-notification" id="equipoNotification">
        <div class="notification-content">
            <div class="notification-header">
                <i class="fas fa-users fa-3x notification-icon"></i>
                <h3 class="notification-title">¡Atención!</h3>
            </div>
            <div class="notification-body">
                <p>Para registrar la producción, primero debes ingresar tu equipo de trabajo.</p>
                <p>Selecciona a tus compañeros y el turno correspondiente.</p>
            </div>
            <div class="notification-footer">
                <a href="{{ route('equipos.create') }}" class="btn btn-primary btn-notification">
                    <i class="fas fa-user-plus"></i> Ingresar equipo de trabajo
                </a>
                <button class="btn btn-outline-secondary btn-notification" onclick="closeNotification()">
                    Recordarme más tarde
                </button>
            </div>
        </div>
    </div>
    @endif

    <h1 class="mb-4 text-center">Producción del Día</h1>

    <!-- Mostrar información del equipo activo si existe -->
    @if($equipoActivo)
    <div class="card equipo-card mb-4">
        <div class="card-header equipo-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-users mr-2"></i>Equipo de Trabajo
                </h5>
                <a href="{{ route('equipos.show', $equipoActivo->id_equipos_cab) }}" class="btn btn-sm btn-outline-light">
                    <i class="fas fa-eye"></i> Ver detalles
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row equipo-info">
                <div class="col-md-4 equipo-info-item">
                    <div class="equipo-info-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div>
                        <small class="text-muted">Responsable</small>
                        <p class="mb-0">{{ $equipoActivo->usuario->nombre_personal }}</p>
                    </div>
                </div>
                <div class="col-md-4 equipo-info-item">
                    <div class="equipo-info-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div>
                        <small class="text-muted">Área</small>
                        <p class="mb-0">{{ $equipoActivo->area->nombre }}</p>
                    </div>
                </div>
                <div class="col-md-4 equipo-info-item">
                    <div class="equipo-info-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <small class="text-muted">Turno</small>
                        <p class="mb-0">{{ $equipoActivo->turno->nombre }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="card shadow mb-4 production-card">
        <div class="card-header py-3 production-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-clipboard-list mr-2"></i>Pedidos para Producción - {{ now()->format('d/m/Y') }}
                </h6>
                <span class="badge badge-pill badge-primary">
                    {{ count($recetasAgrupadas) }} {{ count($recetasAgrupadas) === 1 ? 'pedido' : 'pedidos' }}
                </span>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('produccion.guardar-personal') }}" method="POST" id="produccionForm">
                @csrf

                @if($equipoActivo)
                <input type="hidden" name="id_equipos" value="{{ $equipoActivo->id_equipos_cab }}">
                @endif

                <div class="table-responsive">
                    <table class="table table-hover production-table" id="dataTable" width="100%" cellspacing="0">
                    <thead class="production-table-header">
    <tr>
        <th>Producto</th>                    <!-- Columna 1 -->
        <th>Receta</th>                      <!-- Columna 2 -->
        <th class="text-center">Cant. Pedido</th>         <!-- Columna 3 -->
        <th class="text-center">Unidad Pedido</th>        <!-- Columna 4 -->
        <th class="text-center">Cant. Esperada</th>       <!-- Columna 5 -->
        <th class="text-center">Cant. Producida</th>      <!-- Columna 6 -->
        <th class="text-center">Unidad Producción</th>    <!-- Columna 7 -->
        <th class="text-center">Estado</th>               <!-- Columna 8 -->
        <th class="text-center">Subtotal</th>             <!-- Columna 9 -->
        <th class="text-center">Total</th>                <!-- Columna 10 -->
        <th class="text-center">Harina</th>              <!-- Columna 11 -->
        <th class="text-center">Acciones</th>            <!-- Columna 12 -->
    </tr>
</thead>
<tbody>
    @if($recetasAgrupadas && count($recetasAgrupadas) > 0)
        @foreach($recetasAgrupadas as $idReceta => $recetaData)
            @php
                $receta = $recetaData['receta'];
                $cantidadPedido = $recetaData['cantidad_total'];
                $cantidadEsperada = $cantidadPedido * $receta->constante_crecimiento;
                
                $subtotalReceta = 0;
                foreach ($receta->detalles as $detalle) {
                    $subtotalReceta += $detalle->subtotal_receta * $cantidadPedido;
                }
                
                $componenteHarina = $receta->detalles->first(function($item) {
                    return $item->producto && stripos($item->producto->nombre, 'harina') !== false;
                });
                $cantHarina = $componenteHarina ? $componenteHarina->cantidad * $cantidadPedido : 0;
                
                $pedidosPersonalizados = isset($recetaData['pedidos']) ? $recetaData['pedidos']->filter(function($pedido) {
                    return isset($pedido['es_personalizado']) && $pedido['es_personalizado'];
                }) : collect([]);
                
                $unidadPedido = $recetaData['id_u_medidas'] ?? null;
                $nombreUnidadPedido = $unidadesMedida->firstWhere('id_u_medidas', $unidadPedido)->nombre ?? 'N/A';
                
                // Valores para los inputs
                $oldCantidad = old("cantidad_producida_real.$idReceta", $cantidadEsperada);
                $oldCostoDiseno = old("costo_diseño.$idReceta", 0);
            @endphp

            <!-- Fila principal -->
            <tr class="production-item {{ $recetaData['es_personalizado'] ? 'personalizado-row' : '' }}">
                <td>
                    <strong>{{ $receta->producto->nombre ?? 'N/A' }}</strong>
                    @if($recetaData['es_personalizado'])
                        <span class="badge badge-warning ml-2">Personalizado</span>
                    @endif
                </td>
                <td>
                    {{ $receta->nombre ?? 'N/A' }}
                    @if($pedidosPersonalizados->count() > 0)
                        <span class="badge badge-danger ml-2" data-toggle="tooltip"
                            title="{{ $pedidosPersonalizados->count() }} pedido(s) personalizado(s)">
                            <i class="fas fa-exclamation-circle"></i> {{ $pedidosPersonalizados->count() }}
                        </span>
                    @endif
                </td>
                <td class="text-center">{{ number_format($cantidadPedido, 2) }}</td>
                <td class="text-center">{{ $nombreUnidadPedido }}</td>
                <td class="text-center">{{ number_format($cantidadEsperada, 2) }}</td>
                <td class="text-center">
                    <input type="number" name="cantidad_producida_real[{{ $idReceta }}]"
                        class="form-control form-control-sm production-input"
                        step="0.01" min="0"
                        value="{{ $oldCantidad }}"
                        {{ $recetaData['es_personalizado'] ? '' : 'readonly' }}">
                </td>
                <td class="text-center">
                    <select name="id_u_medidas_prodcc[{{ $idReceta }}]" class="form-control form-control-sm">
                        @foreach($unidadesMedida as $unidad)
                            <option value="{{ $unidad->id_u_medidas }}"
                                {{ $unidad->id_u_medidas == $recetaData['id_u_medidas'] ? 'selected' : '' }}>
                                {{ $unidad->nombre }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td class="text-center">
                    <div class="btn-group btn-group-toggle" data-toggle="buttons">
                        <label class="btn btn-sm btn-outline-primary estado-btn {{ old("es_iniciado.$idReceta") ? 'active' : '' }}">
                            <input type="checkbox" name="es_iniciado[{{ $idReceta }}]" autocomplete="off"
                                onchange="actualizarEstados(this)" {{ old("es_iniciado.$idReceta") ? 'checked' : '' }}> Iniciar
                        </label>
                        <label class="btn btn-sm btn-outline-success estado-btn {{ old("es_terminado.$idReceta") ? 'active' : '' }}">
                            <input type="checkbox" name="es_terminado[{{ $idReceta }}]" autocomplete="off"
                                disabled {{ old("es_terminado.$idReceta") ? 'checked' : '' }}> Terminar
                        </label>
                        <label class="btn btn-sm btn-outline-danger estado-btn {{ old("es_cancelado.$idReceta") ? 'active' : '' }}">
                            <input type="checkbox" name="es_cancelado[{{ $idReceta }}]" autocomplete="off"
                                onchange="actualizarEstados(this)" {{ old("es_cancelado.$idReceta") ? 'checked' : '' }}> Cancelar
                        </label>
                    </div>
                </td>
                <td class="text-center">S/ {{ number_format($subtotalReceta, 2) }}</td>
                <td class="text-center">S/ {{ number_format($subtotalReceta, 2) }}</td>
                <td class="text-center">{{ number_format($cantHarina, 2) }} kg</td>
                <td class="text-center">
                    @if($receta->instructivo)
                        <button type="button" class="btn btn-sm btn-outline-info"
                            data-toggle="tooltip" title="Ver instructivo"
                            onclick="cargarInstructivo({{ $receta->id_recetas }})">
                            <i class="fas fa-book-open"></i>
                        </button>
                    @endif
                </td>
            </tr>

            <!-- Filas de pedidos personalizados -->
            @if($pedidosPersonalizados->count() > 0)
                @foreach($pedidosPersonalizados as $pedido)
                    <tr class="pedido-personalizado">
                        <td colspan="12">
                            <div class="d-flex justify-content-between align-items-center p-2">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-star text-warning mr-2"></i>
                                        <div>
                                            <strong class="d-block">Pedido Personalizado #{{ $pedido['id_pedidos_det'] ?? '' }}</strong>
                                            <p class="mb-1 small"><em>{{ $pedido['descripcion'] ?? 'Sin descripción' }}</em></p>
                                            @if(isset($pedido['foto_referencial_url']) && $pedido['foto_referencial_url'])
                                                <button type="button" class="btn btn-xs btn-outline-primary view-image-btn" 
                                                    data-image-url="{{ $pedido['foto_referencial_url'] }}">
                                                    <i class="fas fa-image"></i> Ver imagen
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <div class="form-group mb-0">
                                        <label class="small text-muted mb-0">Costo Diseño</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">S/</span>
                                            </div>
                                            <input type="number"
                                                name="costo_diseño[{{ $pedido['id_pedidos_det'] }}]"
                                                class="form-control form-control-sm"
                                                step="0.01" min="0"
                                                value="{{ old("costo_diseño.".$pedido['id_pedidos_det'], 0) }}"
                                                {{ old("es_terminado.$idReceta") ? '' : 'disabled' }}>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            @endif
        @endforeach
    @else
        <tr>
            <td colspan="12" class="text-center text-muted py-4 no-orders">
                <i class="fas fa-info-circle fa-3x mb-3"></i>
                <h4>No hay pedidos para producción hoy</h4>
                <p class="text-muted">Los pedidos aparecerán aquí cuando sean asignados a tu área.</p>
            </td>
        </tr>
    @endif
</tbody>
                    </table>
                </div>

                @if($recetasAgrupadas && count($recetasAgrupadas) > 0 && $equipoActivo)
                <div class="form-group mt-4 text-center">
                    <button type="submit" class="btn btn-primary btn-lg btn-save">
                        <i class="fas fa-save mr-2"></i> Guardar Producción
                    </button>
                </div>
                @endif
            </form>
        </div>
    </div>
</div>
<!-- Modal para ver instructivo -->
<div class="modal fade" id="instructivoModal" tabindex="-1" aria-labelledby="instructivoModalLabel" aria-modal="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="instructivoModalLabel">Instructivo de Producción</h5>
                <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="instructivoContent">
                <!-- Contenido cargado via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Estilos CSS para la vista */
    .modal-notification {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1050;
    }

    .notification-content {
        background-color: white;
        border-radius: 10px;
        width: 90%;
        max-width: 500px;
        padding: 20px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    }

    .notification-header {
        text-align: center;
        margin-bottom: 15px;
    }

    .notification-icon {
        color: #4e73df;
        margin-bottom: 10px;
    }

    .notification-title {
        color: #4e73df;
        font-weight: bold;
    }

    .notification-body {
        margin-bottom: 20px;
        text-align: center;
    }

    .notification-footer {
        display: flex;
        justify-content: center;
        gap: 10px;
    }

    .btn-notification {
        padding: 8px 20px;
        border-radius: 5px;
        font-weight: 500;
    }

    .equipo-card {
        border: none;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .equipo-card-header {
        background-color: #4e73df;
        color: white;
        padding: 15px 20px;
        border-bottom: none;
    }

    .equipo-info {
        display: flex;
        flex-wrap: wrap;
    }

    .equipo-info-item {
        display: flex;
        align-items: center;
        padding: 10px;
    }

    .equipo-info-icon {
        width: 40px;
        height: 40px;
        background-color: #f8f9fc;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        color: #4e73df;
    }

    .production-card {
        border: none;
        border-radius: 10px;
        overflow: hidden;
    }

    .production-card-header {
        background-color: #f8f9fc;
        border-bottom: 1px solid #e3e6f0;
    }

    .production-table-header {
        background-color: #f8f9fc;
    }

    .production-table th {
        border-top: none;
        font-weight: 600;
        color: #5a5c69;
    }

    .production-input {
        max-width: 100px;
        margin: 0 auto;
    }

    .estado-btn {
        padding: 3px 8px;
        font-size: 12px;
    }

    .personalizado-row {
        background-color: #fff8e1;
    }

    .pedido-personalizado td {
        padding: 0 !important;
        background-color: #fff8e1;
    }

    .no-orders {
        background-color: #f8f9fc;
        border-radius: 10px;
    }

    .btn-save {
        padding: 10px 30px;
        border-radius: 30px;
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .equipo-info-item {
            width: 100%;
            margin-bottom: 10px;
        }

        .production-table th,
        .production-table td {
            padding: 8px 5px;
            font-size: 12px;
        }

        .estado-btn {
            padding: 2px 5px;
            font-size: 10px;
        }
    }
</style>

<script>
    // Función para actualizar estados de producción
    function actualizarEstados(checkbox) {
        const row = checkbox.closest('tr');
        const iniciarCheck = row.querySelector('input[name^="es_iniciado"]');
        const terminarCheck = row.querySelector('input[name^="es_terminado"]');
        const cancelarCheck = row.querySelector('input[name^="es_cancelado"]');

        if (checkbox === iniciarCheck && checkbox.checked) {
            terminarCheck.disabled = false;
            terminarCheck.parentElement.classList.remove('disabled');
            if (cancelarCheck.checked) {
                cancelarCheck.checked = false;
                cancelarCheck.parentElement.classList.remove('active');
            }

            // Habilitar campos de costo diseño para pedidos personalizados
            const idReceta = iniciarCheck.name.match(/\[(.*?)\]/)[1];
            document.querySelectorAll(`input[name^="costo_diseño"]`).forEach(input => {
                const prevRow = input.closest('tr').previousElementSibling;
                if (prevRow && prevRow.querySelector(`input[name="es_iniciado[${idReceta}]"]`)) {
                    input.disabled = !prevRow.querySelector(`input[name="es_iniciado[${idReceta}]"]`).checked;
                }
            });
        } else if (checkbox === cancelarCheck && checkbox.checked) {
            if (iniciarCheck.checked) {
                iniciarCheck.checked = false;
                iniciarCheck.parentElement.classList.remove('active');
            }
            terminarCheck.checked = false;
            terminarCheck.disabled = true;
            terminarCheck.parentElement.classList.add('disabled');

            // Deshabilitar campos de costo diseño
            document.querySelectorAll(`input[name^="costo_diseño"]`).forEach(input => {
                input.disabled = true;
            });
        } else if (checkbox === iniciarCheck && !checkbox.checked) {
            terminarCheck.checked = false;
            terminarCheck.disabled = true;
            terminarCheck.parentElement.classList.add('disabled');

            // Deshabilitar campos de costo diseño
            document.querySelectorAll(`input[name^="costo_diseño"]`).forEach(input => {
                input.disabled = true;
            });
        }

        if (checkbox === terminarCheck && checkbox.checked && !iniciarCheck.checked) {
            iniciarCheck.checked = true;
            iniciarCheck.parentElement.classList.add('active');
        }
    }

        // Función para cerrar la notificación de equipo
        function closeNotification() {
        document.getElementById('equipoNotification').style.display = 'none';
    }

    // Cerrar notificación al hacer click fuera de ella
    document.addEventListener('click', function(event) {
        const notification = document.getElementById('equipoNotification');
        if (notification && !notification.contains(event.target)) {
            closeNotification();
        }
    });
// Función para cargar el instructivo en el modal
function cargarInstructivo(idReceta) {
    // Verificar si jQuery está disponible
    if (typeof $ === 'undefined') {
        console.error('jQuery no está cargado');
        return;
    }

    const modal = $('#instructivoModal');
    
    // Mostrar el modal usando Bootstrap 5
    const modalInstance = new bootstrap.Modal(modal[0]);
    modalInstance.show();

    // Mostrar spinner de carga
    $('#instructivoContent').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Cargando...</span>
            </div>
            <p class="mt-3">Cargando instructivo...</p>
        </div>
    `);

    // Hacer la petición AJAX
    $.ajax({
        url: "{{ route('recetas.show-instructivo') }}",
        type: 'GET',
        data: { id_receta: idReceta },
        success: function(data) {
            $('#instructivoContent').html(data);
        },
        error: function(xhr, status, error) {
            $('#instructivoContent').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> Error al cargar el instructivo: ${xhr.statusText}
                </div>
                <div class="text-center">
                    <button class="btn btn-primary" onclick="cargarInstructivo(${idReceta})">
                        <i class="fas fa-sync-alt"></i> Intentar nuevamente
                    </button>
                </div>
            `);
            console.error('Error al cargar instructivo:', error);
        }
    });
}

// Inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Verificar si jQuery está disponible
    if (typeof $ === 'undefined') {
        console.error('jQuery no está cargado');
        return;
    }

    // Manejar eventos del modal para accesibilidad
    const modal = document.getElementById('instructivoModal');
    if (modal) {
        modal.addEventListener('show.bs.modal', function() {
            this.setAttribute('aria-hidden', 'false');
        });
        
        modal.addEventListener('hidden.bs.modal', function() {
            this.setAttribute('aria-hidden', 'true');
        });
    }

    // Resto de tu código de inicialización...
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Validar formulario antes de enviar
    const form = document.getElementById('produccionForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const tieneTerminados = document.querySelectorAll('input[name^="es_terminado"]:checked').length > 0;
            
            if (!tieneTerminados) {
                e.preventDefault();
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "No has marcado ningún pedido como terminado. ¿Deseas continuar?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, guardar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            }
        });
    }
});
</script>
@endsection