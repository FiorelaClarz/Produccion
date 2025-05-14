@php
use Illuminate\Support\Facades\Storage;
@endphp
@extends('layouts.app')

@section('content')

<div class="container">

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif




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

<div class="row mb-4">
    <div class="col-12">
        <ul class="nav nav-tabs" id="productionTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link {{ $estadoActual === 'pendientes' ? 'active' : '' }}" 
                   href="{{ route('produccion.index-personal', ['estado' => 'pendientes']) }}">
                    <i class="fas fa-clock mr-2"></i>Pendientes
                    <span class="badge badge-primary ml-2">{{ $totalPendientes }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $estadoActual === 'terminados' ? 'active' : '' }}" 
                   href="{{ route('produccion.index-personal', ['estado' => 'terminados']) }}">
                    <i class="fas fa-check-circle mr-2"></i>Terminados
                    <span class="badge badge-success ml-2">{{ $totalTerminados }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $estadoActual === 'cancelados' ? 'active' : '' }}" 
                   href="{{ route('produccion.index-personal', ['estado' => 'cancelados']) }}">
                    <i class="fas fa-times-circle mr-2"></i>Cancelados
                    <span class="badge badge-danger ml-2">{{ $totalCancelados }}</span>
                </a>
            </li>
        </ul>
    </div>
</div>

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
                <span class="badge badge-pill badge-primary" style="color: black;">
                    {{ count($recetasAgrupadas) }} {{ count($recetasAgrupadas) === 1 ? 'pedido' : 'productos' }}
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
                                <th>Producto</th>
                                <th>Receta</th>
                                <th class="text-center">Cant. Pedido</th>
                                <th class="text-center">Unidad Pedido</th>
                                <th class="text-center">Cant. Esperada</th>
                                <th class="text-center">Cant. Producida</th>
                                <th class="text-center">Unidad Producción</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Subtotal</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Harina</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($recetasAgrupadas && count($recetasAgrupadas) > 0)
                            @php
                            $recetaCounter = 1; // Contador para recetas agrupadas
                            @endphp
                            @foreach($recetasAgrupadas as $idReceta => $recetaData)
                            @php
                            $receta = $recetaData['receta'];
                            $cantidadPedido = $recetaData['cantidad_total'];

                            $cantidadEsperada = ($receta->id_areas == 1)
                            ? $cantidadPedido * $receta->constante_peso_lata
                            : $cantidadPedido * 1;

                            $subtotalReceta = 0;
                            foreach ($receta->detalles as $detalle) {
                            $subtotalReceta += $detalle->subtotal_receta * $cantidadEsperada ;
                            }

                            $componenteHarina = $receta->detalles->first(function($item) {
                            return $item->producto && stripos($item->producto->nombre, 'harina') !== false;
                            });
                            $cantHarina = $componenteHarina ? $componenteHarina->cantidad * $cantidadEsperada : 0;

                            $pedidosPersonalizados = isset($recetaData['pedidos']) ? $recetaData['pedidos']->filter(function($pedido) {
                            return isset($pedido['es_personalizado']) && $pedido['es_personalizado'];
                            }) : collect([]);

                            $unidadPedido = $recetaData['id_u_medidas'] ?? null;
                            $nombreUnidadPedido = $unidadesMedida->firstWhere('id_u_medidas', $unidadPedido)->nombre ?? 'N/A';
                            @endphp

                            <!-- Fila principal -->
                            <tr class="production-item {{ $recetaData['es_personalizado'] ? 'personalizado-row' : '' }}" id="row-{{ $idReceta }}">
                                <td>

                                    <strong>{{ $recetaCounter }}. {{ $receta->producto->nombre ?? 'N/A' }}</strong>
                                    @if($recetaData['es_personalizado'])
                                    <span class="badge badge-warning ml-2" style="color: #BFA100;">Contiene<br>personalizado</span>
                                    @endif

                                </td>
                                <td>
                                    {{ $receta->nombre ?? 'N/A' }}
                                    @if($pedidosPersonalizados->count() > 0)

                                    <span class="badge badge-danger ml-2" data-toggle="tooltip" style="color: #BFA100;"
                                        title="{{ $pedidosPersonalizados->count() }} pedido(s) personalizado(s)">
                                        <i class="fas fa-exclamation-circle" style="color: #BFA100;"></i> {{ $pedidosPersonalizados->count() }}
                                    </span>
                                    @endif
                                </td>
                                <td class="text-center">{{ number_format($cantidadPedido, 2) }}</td>
                                <td class="text-center">{{ $nombreUnidadPedido }}</td>
                                <td class="text-center">{{ number_format($cantidadEsperada, 2) }}</td>
                               <td class="text-center">
    @if($estadoActual === 'pendientes')
        <input type="number" name="cantidad_producida_real[{{ $idReceta }}]"
            class="form-control form-control-sm production-input"
            step="0.01" min="0"
            value="{{ old("cantidad_producida_real.$idReceta", $cantidadEsperada) }}"
            {{ $recetaData['es_personalizado'] ? '' : 'readonly' }}">
    @else
        {{ number_format($recetaData['cantidad_producida_real'] ?? $cantidadEsperada, 2) }}
    @endif
</td>
<td class="text-center">
    @if($estadoActual === 'pendientes')
        <select name="id_u_medidas_prodcc[{{ $idReceta }}]" class="form-control form-control-sm">
            @foreach($unidadesMedida as $unidad)
            <option value="{{ $unidad->id_u_medidas }}"
                {{ $unidad->id_u_medidas == $recetaData['id_u_medidas'] ? 'selected' : '' }}>
                {{ $unidad->nombre }}
            </option>
            @endforeach
        </select>
    @else
        {{ $unidadesMedida->firstWhere('id_u_medidas', $recetaData['id_u_medidas_prodcc'] ?? $recetaData['id_u_medidas'])->nombre ?? 'N/A' }}
    @endif
</td>
                                
                                <td class="text-center">
    @if($estadoActual === 'pendientes')
        <div class="btn-group btn-group-toggle" data-toggle="buttons">
            <input type="hidden" name="es_iniciado[{{ $idReceta }}]" value="0">
            <input type="hidden" name="es_terminado[{{ $idReceta }}]" value="0">
            <input type="hidden" name="es_cancelado[{{ $idReceta }}]" value="0">

            <label class="btn btn-sm btn-outline-primary estado-btn {{ $recetaData['estado_general'] === 'en_proceso' ? 'active' : '' }}">
                <input type="checkbox" name="es_iniciado[{{ $idReceta }}]"
                    autocomplete="off" value="1"
                    onchange="actualizarEstados(this, {{ $idReceta }})"
                    {{ $recetaData['estado_general'] === 'en_proceso' ? 'checked' : '' }}> Iniciar
            </label>
            <label class="btn btn-sm btn-outline-success estado-btn {{ $recetaData['estado_general'] === 'terminado' ? 'active' : '' }}">
                <input type="checkbox" name="es_terminado[{{ $idReceta }}]"
                    autocomplete="off" value="1"
                    onchange="actualizarEstados(this, {{ $idReceta }})"
                    {{ $recetaData['estado_general'] === 'terminado' ? 'checked' : '' }}> Terminar
            </label>
            <label class="btn btn-sm btn-outline-danger estado-btn {{ $recetaData['estado_general'] === 'cancelado' ? 'active' : '' }}">
                <input type="checkbox" name="es_cancelado[{{ $idReceta }}]"
                    autocomplete="off" value="1"
                    onchange="mostrarModalObservacion(this, {{ $idReceta }})"
                    {{ $recetaData['estado_general'] === 'cancelado' ? 'checked' : '' }}> Cancelar
            </label>
        </div>
    @else
        <div class="estado-final">
            @if($recetaData['estado_general'] === 'terminado')
                <span class="badge badge-success">
                    <i class="fas fa-check-circle"></i> Terminado
                </span>
                @if($recetaData['es_personalizado'])
                    <small class="text-muted d-block">Costo diseño: S/ {{ number_format($recetaData['costo_diseño'] ?? 0, 2) }}</small>
                @endif
            @elseif($recetaData['estado_general'] === 'cancelado')
                <span class="badge badge-danger">
                    <i class="fas fa-times-circle"></i> Cancelado
                </span>
                @if(!empty($recetaData['observaciones']))
                    <small class="text-muted d-block">Obs: {{ Str::limit($recetaData['observaciones'], 30) }}</small>
                @endif
            @endif
        </div>
    @endif
</td>
                                <td class="text-center">S/ {{ number_format($subtotalReceta, 2) }}</td>
                                <td class="text-center total-receta" id="total-{{ $idReceta }}">S/ {{ number_format($subtotalReceta, 2) }}</td>
                                <td class="text-center">{{ number_format($cantHarina, 2) }} gramos</td>
                                <td class="text-center">
                                    @if($receta->instructivo)
<button type="button" class="btn btn-sm btn-outline-info"
    data-toggle="tooltip" title="Ver instructivo"
    onclick="cargarInstructivo({{ $receta->id_recetas }}, '{{ $recetaData['estado_general'] }}')">
    <i class="fas fa-book-open"></i>
</button>
@endif
                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                        data-toggle="tooltip" title="Agregar observación"
                                        onclick="mostrarModalObservacion(null, {{ $idReceta }})">
                                        <i class="fas fa-comment"></i>
                                    </button>
                                </td>
                            </tr>

                            <!-- Filas de pedidos personalizados -->
                            @if($pedidosPersonalizados->count() > 0)
                            @php
                            $pedidoCounter = 1; // Contador para pedidos personalizados
                            @endphp
                            @foreach($pedidosPersonalizados as $pedido)
                            <tr class="pedido-personalizado" data-recid="{{ $idReceta }}">
                                <td colspan="12">
                                    <div class="d-flex justify-content-between align-items-center p-2">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center">
                                                <!-- <i class="fas fa-star text-warning mr-2"></i> -->
                                                <div>
                                                    <strong class="d-block">{{ $recetaCounter }}.{{ $pedidoCounter }} Pedido Personalizado (Nº {{ $pedido['id_pedidos_det'] ?? '' }})</strong>
                                                    <p class="mb-1 small"><em>{{ $pedido['descripcion'] ?? 'Sin descripción' }}</em></p>
                                                    <div class="d-flex align-items-center mt-1">
                                                        <span class="badge badge-info mr-2" style="font-weight: bold;">
                                                            Cantidad: {{ $pedido['cantidad'] }}
                                                        </span>
                                                        @if(isset($pedido['foto_referencial']) && $pedido['foto_referencial'])
                                                        @php
                                                        $filename = str_replace('pedidos/', '', $pedido['foto_referencial']);
                                                        $imageUrl = asset('storage/pedidos/'.$filename);
                                                        @endphp
                                                        <button type="button" class="btn btn-xs btn-outline-primary view-image-btn"
                                                            data-image-url="{{ $imageUrl }}">
                                                            <i class="fas fa-image"></i> Ver imagen
                                                        </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <div class="form-group mb-0">
                                                <label class="small text-muted mb-0">Costo Diseño</label>
                                                <div class="input-group input-group-sm">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">S/.</span>
                                                    </div>
                                                    <input type="number"
                                                        name="costo_diseño[{{ $pedido['id_pedidos_det'] }}]"
                                                        class="form-control form-control-sm costo-diseno"
                                                        step="0.01" min="0"
                                                        value="{{ old("costo_diseño.".$pedido['id_pedidos_det'], 0) }}"
                                                        disabled
                                                        onchange="actualizarTotalReceta({{ $idReceta }})">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @php
                            $pedidoCounter++; // Incrementar contador de pedidos
                            @endphp
                            @endforeach
                            @endif
                            @php
                            $recetaCounter++; // Incrementar contador de recetas
                            @endphp
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

                @if($recetasAgrupadas && count($recetasAgrupadas) > 0 && $equipoActivo && $estadoActual === 'pendientes')
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
<div class="modal fade" id="instructivoModal" tabindex="-1" aria-labelledby="instructivoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="instructivoModalLabel">Instructivo de Producción</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="instructivoContent">
                <!-- Contenido cargado via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ver imágenes de pedidos personalizados -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="imageModalLabel">Imagen de Referencia</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" class="img-fluid" alt="Imagen de referencia del pedido" style="max-height: 70vh;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para observaciones -->
<div class="modal fade" id="observacionModal" tabindex="-1" aria-labelledby="observacionModalLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="observacionModalLabel">Agregar Observación</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="observacionRecetaId">
                <input type="hidden" id="esCancelacion">
                <div class="form-group">
                    <label for="observacionTexto">Observación:</label>
                    <textarea class="form-control" id="observacionTexto" rows="4"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarObservacion()">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para errores en costos de diseño -->
<div class="modal fade" id="errorCostosModal" tabindex="-1" aria-labelledby="errorCostosModalLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="errorCostosModalLabel">Error en Costos de Diseño</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Debe completar los costos de diseño para todos los pedidos personalizados antes de terminar esta producción.</p>
                <ul id="listaPedidosFaltantes" class="list-group">
                    <!-- Aquí se agregarán dinámicamente los pedidos faltantes -->
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Entendido</button>
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

    .pedido-personalizado .badge-info {
        font-size: 12px;
        padding: 3px 8px;
        font-weight: normal;
        background-color: #FFD700;
        color: black;
    }

    .pedido-personalizado .d-flex.align-items-center {
        flex-wrap: wrap;
        gap: 5px;
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

    /* Estilos para los estados */
    .estado-btn {
        position: relative;
        z-index: 2;
        transition: all 0.3s;
    }

    .estado-btn.active {
        font-weight: bold;
        box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.1);
    }

    /* Estilo para filas enviadas */
    .production-item.enviado {
        background-color: #e8f5e9 !important;
    }

    /* Estilo para filas canceladas */
    .production-item.cancelado {
        background-color: #ffebee !important;
    }

    /* Estilo para botón de observación */
    .btn-observacion {
        transition: all 0.3s;
    }

    .btn-observacion:hover {
        transform: scale(1.1);
    }

    /* Estilos para las pestañas */
    .nav-tabs {
        border-bottom: 2px solid #dee2e6;
    }

    .nav-tabs .nav-link {
        border: none;
        color: #6c757d;
        font-weight: 600;
        padding: 12px 20px;
        transition: all 0.3s;
    }

    .nav-tabs .nav-link:hover {
        border: none;
        color: #4e73df;
    }

    .nav-tabs .nav-link.active {
        color: #4e73df;
        background-color: transparent;
        border-bottom: 3px solid #4e73df;
    }

    .nav-tabs .nav-link .badge {
        font-size: 0.7rem;
        position: relative;
        top: -1px;
    }

    /* Estilos para el contenido de cada pestaña */
    .tab-content {
        padding: 20px 0;
    }

    /* Estilos específicos para cada estado */
    .production-item.terminado {
        background-color: #e8f5e9 !important;
    }

    .production-item.cancelado {
        background-color: #ffebee !important;
    }

    .production-item.en-proceso {
        background-color: #e3f2fd !important;
    }
</style>

<script>
    // Función mejorada para actualizar estados
    function actualizarEstados(checkbox, idReceta) {
        const name = checkbox.name;
        const isChecked = checkbox.checked;
        const row = document.getElementById(`row-${idReceta}`);

        // Actualizar el valor del campo oculto correspondiente
        const hiddenInput = document.querySelector(`input[type="hidden"][name="${name}"]`);
        if (hiddenInput) {
            hiddenInput.value = isChecked ? '1' : '0';
        }

        // Habilitar campos de costo diseño cuando se marca "Iniciar"
        if (name === `es_iniciado[${idReceta}]` && isChecked) {
            const costosDiseno = row.parentNode.querySelectorAll(`tr.pedido-personalizado[data-recid="${idReceta}"] input.costo-diseno`);
            costosDiseno.forEach(input => {
                input.disabled = false;
            });
        }

        // Validar costos de diseño al marcar "Terminar"
        if (name === `es_terminado[${idReceta}]` && isChecked) {
            const tienePersonalizados = row.classList.contains('personalizado-row');
            if (tienePersonalizados) {
                const costosDiseno = row.parentNode.querySelectorAll(`tr.pedido-personalizado[data-recid="${idReceta}"] input.costo-diseno`);
                let faltanCostos = false;
                const pedidosFaltantes = [];

                costosDiseno.forEach(input => {
                    if (!input.value || parseFloat(input.value) <= 0) {
                        faltanCostos = true;
                        const pedidoId = input.name.match(/\[(.*?)\]/)[1];
                        pedidosFaltantes.push(pedidoId);
                    }
                });

                if (faltanCostos) {
                    // Mostrar modal de error
                    const lista = document.getElementById('listaPedidosFaltantes');
                    lista.innerHTML = '';
                    pedidosFaltantes.forEach(id => {
                        lista.innerHTML += `<li class="list-group-item">Pedido #${id}</li>`;
                    });

                    $('#errorCostosModal').modal('show');

                    // Desmarcar el checkbox
                    checkbox.checked = false;
                    if (hiddenInput) {
                        hiddenInput.value = '0';
                    }
                    return false;
                }
            }
        }

        // Si se marca "Terminar", también debe marcarse "Iniciar"
        if (name === `es_terminado[${idReceta}]` && isChecked) {
            const iniciarCheckbox = document.querySelector(`input[name="es_iniciado[${idReceta}]"]`);
            if (iniciarCheckbox && !iniciarCheckbox.checked) {
                iniciarCheckbox.checked = true;
                const iniciarHidden = document.querySelector(`input[type="hidden"][name="es_iniciado[${idReceta}]"]`);
                if (iniciarHidden) {
                    iniciarHidden.value = '1';
                }
            }
        }

        // Si se marca "Cancelar", desmarcar otros estados
        if (name === `es_cancelado[${idReceta}]` && isChecked) {
            const terminarCheckbox = document.querySelector(`input[name="es_terminado[${idReceta}]"]`);

            if (terminarCheckbox) {
                terminarCheckbox.checked = false;
                const terminarHidden = document.querySelector(`input[type="hidden"][name="es_terminado[${idReceta}]"]`);
                if (terminarHidden) {
                    terminarHidden.value = '0';
                }
            }

            mostrarModalObservacion(checkbox, idReceta);
        }

        return true;
    }

    // Modificamos el evento submit del formulario
    document.getElementById('produccionForm').addEventListener('submit', function(e) {
        console.log("Preparando envío del formulario...");

        // Verificar datos antes de enviar
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());
        console.log("Datos a enviar:", data);

        // Validación de estados
        let tieneCanceladosSinObservacion = false;

        // Verificar cada receta
        recetas.forEach(input => {
            const idReceta = input.name.match(/\[(.*?)\]/)[1];

            // Verificar si está cancelado pero sin observación
            const canceladoCheckbox = document.querySelector(`input[name="es_cancelado[${idReceta}]"]`);
            const observacion = document.querySelector(`input[name="observaciones[${idReceta}]"]`);
            if (canceladoCheckbox && canceladoCheckbox.checked && (!observacion || !observacion.value)) {
                tieneCanceladosSinObservacion = true;
            }
        });

        if (tieneCanceladosSinObservacion) {
            alert('Hay pedidos cancelados sin observación. Por favor, agrega una observación para los pedidos cancelados.');
            e.preventDefault();
            return false;
        }

        return true;
    });

    // Función para actualizar el total de una receta específica
    function actualizarTotalReceta(idReceta) {
        const row = document.getElementById(`row-${idReceta}`);
        const subtotalText = row.querySelector('td:nth-child(9)').textContent;
        const subtotal = parseFloat(subtotalText.replace('S/ ', '').replace(',', ''));
        let costoDisenoTotal = 0;

        // Sumar todos los costos de diseño de pedidos personalizados
        document.querySelectorAll(`tr.pedido-personalizado[data-recid="${idReceta}"] input.costo-diseno`).forEach(input => {
            const valor = parseFloat(input.value) || 0;
            costoDisenoTotal += valor;
        });

        // Actualizar el total con animación
        const totalCell = document.getElementById(`total-${idReceta}`);
        const nuevoTotal = subtotal + costoDisenoTotal;
        totalCell.textContent = 'S/ ' + nuevoTotal.toFixed(2);
        totalCell.classList.add('highlight');

        // Remover la clase de animación después de que termine
        setTimeout(() => {
            totalCell.classList.remove('highlight');
        }, 1500);
    }

    function mostrarModalObservacion(checkbox, idReceta) {
        const modal = $('#observacionModal');
        const esCancelacion = checkbox !== null;

        // Configurar el modal según si es cancelación o observación normal
        if (esCancelacion) {
            $('#observacionModalLabel').text('Observación de Cancelación');
            $('#observacionTexto').attr('placeholder', 'Ingrese el motivo de la cancelación...');
        } else {
            $('#observacionModalLabel').text('Agregar Observación');
            $('#observacionTexto').attr('placeholder', 'Ingrese cualquier observación sobre esta producción...');
        }

        // Cargar observación existente si hay
        const observacionExistente = document.querySelector(`input[name="observaciones[${idReceta}]"]`)?.value || '';
        $('#observacionTexto').val(observacionExistente);

        // Guardar referencia a la receta
        $('#observacionRecetaId').val(idReceta);
        $('#esCancelacion').val(esCancelacion ? '1' : '0');

        modal.modal('show');
    }

    function guardarObservacion() {
        const idReceta = $('#observacionRecetaId').val();
        const esCancelacion = $('#esCancelacion').val() === '1';
        const observacion = $('#observacionTexto').val();

        // Crear o actualizar el input oculto para la observación
        let inputObservacion = document.querySelector(`input[name="observaciones[${idReceta}]"]`);
        if (!inputObservacion) {
            inputObservacion = document.createElement('input');
            inputObservacion.type = 'hidden';
            inputObservacion.name = `observaciones[${idReceta}]`;
            document.getElementById('produccionForm').appendChild(inputObservacion);
        }
        inputObservacion.value = observacion;

        // Si es cancelación, mostrar badge
        if (esCancelacion) {
            const row = document.getElementById(`row-${idReceta}`);
            let badge = row.querySelector('.badge-observacion-cancelacion');

            if (!badge) {
                badge = document.createElement('span');
                badge.className = 'badge badge-danger badge-observacion-cancelacion ml-2';
                badge.innerHTML = '<i class="fas fa-exclamation-circle"></i> Cancelado';
                badge.setAttribute('data-toggle', 'tooltip');
                badge.setAttribute('title', observacion);
                row.querySelector('td:first-child').appendChild(badge);

                // Inicializar tooltip
                new bootstrap.Tooltip(badge);
            } else {
                // Actualizar tooltip si ya existe
                badge.setAttribute('title', observacion);
                const tooltipInstance = bootstrap.Tooltip.getInstance(badge);
                if (tooltipInstance) {
                    tooltipInstance.dispose();
                    new bootstrap.Tooltip(badge);
                }
            }
        }

        $('#observacionModal').modal('hide');
    }

    // Función para cargar el instructivo en el modal
    function cargarInstructivo(idReceta, estado) {
    const modal = $('#instructivoModal');
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
        data: {
            id_receta: idReceta,
            estado: estado // Pasamos el estado actual
        },
        success: function(data) {
            $('#instructivoContent').html(data);
        },
        error: function(xhr, status, error) {
            $('#instructivoContent').html(`
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> Error al cargar el instructivo: ${xhr.statusText}
            </div>
            <div class="text-center">
                <button class="btn btn-primary" onclick="cargarInstructivo(${idReceta}, '${estado}')">
                    <i class="fas fa-sync-alt"></i> Intentar nuevamente
                </button>
            </div>
            `);
            console.error('Error al cargar instructivo:', error);
        }
    });
}

    // Función para mostrar la imagen en el modal
    function mostrarImagen(url) {
        const modal = $('#imageModal');
        $('#modalImage').attr('src', url);
        const modalInstance = new bootstrap.Modal(modal[0]);
        modalInstance.show();
    }

    // Inicialización cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        // Manejar eventos del modal para accesibilidad
        const instructivoModal = document.getElementById('instructivoModal');
        if (instructivoModal) {
            instructivoModal.addEventListener('show.bs.modal', function() {
                this.setAttribute('aria-hidden', 'false');
            });

            instructivoModal.addEventListener('hidden.bs.modal', function() {
                this.setAttribute('aria-hidden', 'true');
            });
        }

        // Inicializar tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Eventos para los botones de ver imagen
        document.querySelectorAll('.view-image-btn').forEach(button => {
            button.addEventListener('click', function() {
                const imageUrl = this.getAttribute('data-image-url');
                mostrarImagen(imageUrl);
            });
        });

        document.querySelectorAll('.production-item').forEach(row => {
            const estado = row.dataset.estado;
            if (estado) {
                row.classList.add(estado);
            }
        });

        // En el evento submit del formulario, modificar para eliminar la validación de enviados
        document.getElementById('produccionForm').addEventListener('submit', function(e) {
            console.log("Preparando envío del formulario...");

            // Agregar recetas al formulario
            const recetas = document.querySelectorAll('input[name^="cantidad_producida_real"]');
            recetas.forEach(input => {
                const idReceta = input.name.match(/\[(.*?)\]/)[1];
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'id_recetas_cab[]';
                hiddenInput.value = idReceta;
                this.appendChild(hiddenInput);
            });

            // Verificar datos antes de enviar - ELIMINAR LA PARTE DE "tieneEnviados"
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            console.log("Datos a enviar:", data);

            // Validación de estados - SOLO MANTENER LA DE CANCELADOS SIN OBSERVACIÓN
            let tieneCanceladosSinObservacion = false;

            // Verificar cada receta
            recetas.forEach(input => {
                const idReceta = input.name.match(/\[(.*?)\]/)[1];

                // Verificar si está cancelado pero sin observación
                const canceladoCheckbox = document.querySelector(`input[name="es_cancelado[${idReceta}]"]`);
                const observacion = document.querySelector(`input[name="observaciones[${idReceta}]"]`);
                if (canceladoCheckbox && canceladoCheckbox.checked && (!observacion || !observacion.value)) {
                    tieneCanceladosSinObservacion = true;
                }
            });

            if (tieneCanceladosSinObservacion) {
                alert('Hay pedidos cancelados sin observación. Por favor, agrega una observación para los pedidos cancelados.');
                e.preventDefault();
                return false;
            }

            return true; // Permitir siempre el envío si no hay cancelados sin observación
        });
        // Función para actualizar estados
        function actualizarEstados(checkbox, idReceta) {
            console.log(`Actualizando estado para receta ${idReceta}`, checkbox.name, checkbox.checked);
            const row = document.getElementById(`row-${idReceta}`);

            const form = document.getElementById('produccionForm');

            // Obtener todos los checkboxes relacionados
            const iniciarCheck = row.querySelector(`input[name="es_iniciado[${idReceta}]"]`);
            const terminarCheck = row.querySelector(`input[name="es_terminado[${idReceta}]"]`);
            const enviarCheck = row.querySelector(`input[name="es_enviado[${idReceta}]"]`);
            const cancelarCheck = row.querySelector(`input[name="es_cancelado[${idReceta}]"]`);

            // Actualizar los hidden inputs correspondientes
            form.querySelector(`input[type="hidden"][name="es_iniciado[${idReceta}]"]`).value = iniciarCheck.checked ? '1' : '0';
            form.querySelector(`input[type="hidden"][name="es_terminado[${idReceta}]"]`).value = terminarCheck.checked ? '1' : '0';
            form.querySelector(`input[type="hidden"][name="es_enviado[${idReceta}]"]`).value = enviarCheck.checked ? '1' : '0';
            form.querySelector(`input[type="hidden"][name="es_cancelado[${idReceta}]"]`).value = cancelarCheck.checked ? '1' : '0';

            console.log(`Estados actualizados - Iniciado: ${iniciarCheck.checked}, Terminado: ${terminarCheck.checked}, Enviado: ${enviarCheck.checked}, Cancelado: ${cancelarCheck.checked}`);


            if (row) {
                row.className = 'production-item';

                if (checkbox.name.includes('es_cancelado') && checkbox.checked) {
                    row.classList.add('cancelado');
                } else if (checkbox.name.includes('es_terminado') && checkbox.checked) {
                    row.classList.add('terminado');
                } else if (checkbox.name.includes('es_iniciado') && checkbox.checked) {
                    row.classList.add('en-proceso');
                }
            }

            console.log(`Estado actualizado para fila ${idReceta}`);


        }

        // Función para actualizar el total
        function actualizarTotalReceta(idReceta) {
            console.log(`Actualizando total para receta ${idReceta}`);
            const row = document.getElementById(`row-${idReceta}`);
            const subtotalText = row.querySelector('td:nth-child(9)').textContent;
            const subtotal = parseFloat(subtotalText.replace('S/ ', '').replace(',', ''));
            let costoDisenoTotal = 0;

            // Sumar todos los costos de diseño de pedidos personalizados
            document.querySelectorAll(`tr.pedido-personalizado[data-recid="${idReceta}"] input.costo-diseno`).forEach(input => {
                const valor = parseFloat(input.value) || 0;
                costoDisenoTotal += valor;
            });

            // Actualizar el total
            const totalCell = document.getElementById(`total-${idReceta}`);
            const nuevoTotal = subtotal + costoDisenoTotal;
            totalCell.textContent = 'S/ ' + nuevoTotal.toFixed(2);

            console.log(`Total actualizado para receta ${idReceta}: ${nuevoTotal}`);
        }

        function mostrarErroresCancelados(ids) {
            let html = '<p>Los siguientes registros cancelados necesitan observación:</p><ul>';
            ids.forEach(id => {
                html += `<li>Receta ID: ${id}</li>`;
            });
            html += '</ul>';

            Swal.fire({
                title: 'Observaciones faltantes',
                html: html,
                icon: 'error',
                confirmButtonText: 'Entendido'
            });
        }
    });

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
</script>
@endsection