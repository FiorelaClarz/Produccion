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
            <form action="{{ route('produccion.guardar-personal') }}" method="POST">
                @csrf
                
                @if($equipoActivo)
                    <input type="hidden" name="id_equipos" value="{{ $equipoActivo->id_equipos_cab }}">
                @endif
                
                <div class="table-responsive">
                    <table class="table table-hover production-table" id="dataTable" width="100%" cellspacing="0">
                        <thead class="production-table-header">
                            <tr>
                                <th>Producto</th>
                                <th>Receta</th>
                                <th class="text-center">Cant. Pedido</th>
                                <th class="text-center">Cant. Esperada</th>
                                <th class="text-center">Cant. Producida</th>
                                <th>Unidad Medida</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Subtotal</th>
                                <th class="text-center">Costo Diseño</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Harina</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($recetasAgrupadas && count($recetasAgrupadas) > 0)
                                @foreach($recetasAgrupadas as $idReceta => $recetaData)
                                    @php
                                        $receta = $recetaData['receta'];
                                        $cantidadPedido = $recetaData['cantidad_total'];
                                        $cantidadEsperada = $cantidadPedido * $receta->constante_crecimiento;
                                        
                                        // Calcular subtotal basado en los detalles de la receta
                                        $subtotalReceta = 0;
                                        foreach ($receta->detalles as $detalle) {
                                            $subtotalReceta += $detalle->subtotal_receta * $cantidadPedido;
                                        }
                                        
                                        // Buscar componente de harina
                                        $componenteHarina = $receta->detalles->first(function($item) {
                                            return $item->producto && stripos($item->producto->nombre, 'harina') !== false;
                                        });
                                        $cantHarina = $componenteHarina ? $componenteHarina->cantidad * $cantidadPedido : 0;
                                        
                                        // Calcular costo diseño
                                        $costoDiseno = $recetaData['es_personalizado'] ? (old('costo_diseño.'.$loop->index) ?? 0) : 0;
                                        $total = $subtotalReceta + $costoDiseno;
                                    @endphp
                                    <tr class="production-item {{ $recetaData['es_personalizado'] ? 'personalizado-row' : '' }}">
                                        <td>
                                            <strong>{{ $receta->producto->nombre ?? 'N/A' }}</strong>
                                            @if($recetaData['es_personalizado'])
                                                <span class="badge badge-warning ml-2">Personalizado</span>
                                            @endif
                                        </td>
                                        <td>{{ $receta->nombre ?? 'N/A' }}</td>
                                        <td class="text-center">{{ number_format($cantidadPedido, 2) }}</td>
                                        <td class="text-center">{{ number_format($cantidadEsperada, 2) }}</td>
                                        <td>
                                            <input type="number" name="cantidad_producida_real[]" 
                                                   class="form-control form-control-sm production-input" 
                                                   step="0.01" min="0"
                                                   value="{{ old('cantidad_producida_real.'.$loop->index, $cantidadEsperada) }}" 
                                                   {{ $recetaData['es_personalizado'] ? '' : 'readonly' }}>
                                        </td>
                                        <td>
                                            <select name="id_u_medidas_prodcc[]" class="form-control form-control-sm">
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
                                                <label class="btn btn-sm btn-outline-primary estado-btn {{ old('es_iniciado.'.$loop->index) ? 'active' : '' }}">
                                                    <input type="checkbox" name="es_iniciado[]" autocomplete="off" 
                                                           onchange="actualizarEstados(this)"> Iniciar
                                                </label>
                                                <label class="btn btn-sm btn-outline-success estado-btn {{ old('es_terminado.'.$loop->index) ? 'active' : '' }}">
                                                    <input type="checkbox" name="es_terminado[]" autocomplete="off" 
                                                           disabled> Terminar
                                                </label>
                                                <label class="btn btn-sm btn-outline-danger estado-btn {{ old('es_cancelado.'.$loop->index) ? 'active' : '' }}">
                                                    <input type="checkbox" name="es_cancelado[]" autocomplete="off" 
                                                           onchange="actualizarEstados(this)"> Cancelar
                                                </label>
                                            </div>
                                        </td>
                                        <td class="text-center">S/ {{ number_format($subtotalReceta, 2) }}</td>
                                        <td>
                                            @if($recetaData['es_personalizado'])
                                            <input type="number" name="costo_diseño[]" 
                                                   class="form-control form-control-sm production-input" 
                                                   step="0.01" min="0"
                                                   value="{{ old('costo_diseño.'.$loop->index, 0) }}">
                                            @else
                                            <input type="number" value="0.00" readonly 
                                                   class="form-control form-control-sm production-input">
                                            <input type="hidden" name="costo_diseño[]" value="0">
                                            @endif
                                        </td>
                                        <td class="text-center">S/ {{ number_format($total, 2) }}</td>
                                        <td class="text-center">{{ number_format($cantHarina, 2) }} kg</td>
                                        <input type="hidden" name="id_recetas_cab[]" value="{{ $idReceta }}">
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="11" class="text-center text-muted py-4 no-orders">
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

<style>
    /* Estilos para la notificación modal */
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
        width: 100%;
        max-width: 500px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        animation: fadeIn 0.3s ease-out;
    }
    
    .notification-header {
        background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
        color: white;
        padding: 20px;
        text-align: center;
    }
    
    .notification-title {
        margin-top: 10px;
        font-weight: 600;
    }
    
    .notification-icon {
        color: white;
    }
    
    .notification-body {
        padding: 25px;
        text-align: center;
        font-size: 16px;
        line-height: 1.6;
    }
    
    .notification-footer {
        padding: 15px;
        display: flex;
        justify-content: center;
        gap: 10px;
        background-color: #f8f9fa;
        border-top: 1px solid #eee;
    }
    
    .btn-notification {
        padding: 8px 20px;
        border-radius: 50px;
        font-weight: 500;
    }
    
    /* Estilos para la tarjeta de equipo */
    .equipo-card {
        border: none;
        border-radius: 10px;
        overflow: hidden;
    }
    
    .equipo-card-header {
        background: linear-gradient(135deg, #2c3e50 0%, #4ca1af 100%);
        color: white;
        border-bottom: none;
    }
    
    .equipo-info {
        display: flex;
        justify-content: space-between;
    }
    
    .equipo-info-item {
        display: flex;
        align-items: center;
        padding: 10px;
    }
    
    .equipo-info-icon {
        background-color: #f8f9fa;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        color: #4ca1af;
    }
    
    /* Estilos para la tabla de producción */
    .production-card {
        border-radius: 10px;
        border: none;
    }
    
    .production-card-header {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
        border-bottom: none;
    }
    
    .production-table {
        border-collapse: separate;
        border-spacing: 0 8px;
    }
    
    .production-table-header th {
        border: none;
        white-space: nowrap;
        font-weight: 600;
        color: #495057;
    }
    
    .production-item {
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }
    
    .production-item:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }
    
    .personalizado-row {
        background-color: #fffaf3;
    }
    
    .personalizado-row:hover {
        background-color: #fff5e6;
    }
    
    .production-input {
        border-radius: 20px;
        border: 1px solid #e0e0e0;
        text-align: center;
    }
    
    .estado-btn {
        border-radius: 20px !important;
        margin: 2px;
    }
    
    .btn-save {
        padding: 10px 30px;
        border-radius: 50px;
        font-weight: 600;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
    }
    
    .no-orders {
        background-color: #f8f9fa;
        border-radius: 10px;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<script>
function actualizarEstados(checkbox) {
    const row = checkbox.closest('tr');
    const iniciarCheck = row.querySelector('input[name="es_iniciado[]"]');
    const terminarCheck = row.querySelector('input[name="es_terminado[]"]');
    const cancelarCheck = row.querySelector('input[name="es_cancelado[]"]');
    
    if (checkbox === iniciarCheck && checkbox.checked) {
        terminarCheck.disabled = false;
        terminarCheck.parentElement.classList.remove('disabled');
        if (cancelarCheck.checked) {
            cancelarCheck.checked = false;
            cancelarCheck.parentElement.classList.remove('active');
        }
    } else if (checkbox === cancelarCheck && checkbox.checked) {
        if (iniciarCheck.checked) {
            iniciarCheck.checked = false;
            iniciarCheck.parentElement.classList.remove('active');
        }
        terminarCheck.checked = false;
        terminarCheck.disabled = true;
        terminarCheck.parentElement.classList.add('disabled');
    } else if (checkbox === iniciarCheck && !checkbox.checked) {
        terminarCheck.checked = false;
        terminarCheck.disabled = true;
        terminarCheck.parentElement.classList.add('disabled');
    }
    
    if (checkbox === terminarCheck && checkbox.checked && !iniciarCheck.checked) {
        iniciarCheck.checked = true;
        iniciarCheck.parentElement.classList.add('active');
    }
}

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
</script>
@endsection