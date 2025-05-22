@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Notificaciones de sesión -->
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

    <!-- Notificación para ingresar equipo de trabajo -->
    @if(!$equipoActivo)
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

    <!-- Pestañas de estados -->
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

    <!-- Información del equipo activo -->
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

    <!-- Tarjeta principal de producción -->
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
                                <th class="text-center">Costo Diseño</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Harina</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($recetasAgrupadas && count($recetasAgrupadas) > 0)
                            @php
                            $recetaCounter = 1;
                            @endphp
                            @foreach($recetasAgrupadas as $idReceta => $recetaData)
                            @php
                            $receta = $recetaData['receta'];
                            
                            // Separar pedidos personalizados y no personalizados
                            $pedidosNoPersonalizados = $recetaData['pedidos']->where('es_personalizado', false);
                            $pedidosPersonalizados = $recetaData['pedidos']->where('es_personalizado', true);
                            
                            $cantidadNoPersonalizada = $pedidosNoPersonalizados->sum('cantidad');
                            $cantidadEsperada = ($receta->id_areas == 1)
                                ? $cantidadNoPersonalizada * $receta->constante_peso_lata
                                : $cantidadNoPersonalizada;

                            // Determinar si debemos deshabilitar controles
                            $disableControls = $cantidadNoPersonalizada == 0 && $pedidosPersonalizados->count() > 0;

                            $subtotalReceta = 0;
                            foreach ($receta->detalles as $detalle) {
                                $subtotalReceta += $detalle->subtotal_receta * $cantidadEsperada;
                            }

                            $componenteHarina = $receta->detalles->first(function($item) {
                                return $item->producto && stripos($item->producto->nombre, 'harina') !== false;
                            });
                            $cantHarina = $componenteHarina ? $componenteHarina->cantidad * $cantidadEsperada : 0;
                            $idHarina = $componenteHarina ? $componenteHarina->id_recetas_det : null;

                            $unidadPedido = $recetaData['id_u_medidas'] ?? null;
                            $nombreUnidadPedido = $unidadesMedida->firstWhere('id_u_medidas', $unidadPedido)->nombre ?? 'N/A';
                            @endphp

                            <!-- Fila principal para receta agrupada -->
                            <tr class="production-item {{ $recetaData['es_personalizado'] ? 'personalizado-row' : '' }}" id="row-{{ $idReceta }}">
                                <td>
                                    <strong>{{ $recetaCounter }}. {{ $receta->producto->nombre ?? 'N/A' }}</strong>
                                    @if($pedidosPersonalizados->count() > 0)
                                    <span class="badge badge-warning ml-2" style="color: #BFA100;">Contiene<br>personalizado</span>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-link" onclick="toggleDetalles({{ $idReceta }})">
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
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
                                <td class="text-center">
                                    @if($estadoActual === 'pendientes')
                                        {{ number_format($cantidadNoPersonalizada, 2) }}
                                    @elseif($estadoActual === 'terminados')
                                        {{ number_format($pedidosNoPersonalizados->where('id_estados', 4)->sum('cantidad'), 2) }}
                                    @elseif($estadoActual === 'cancelados')
                                        {{ number_format($pedidosNoPersonalizados->where('id_estados', 5)->sum('cantidad'), 2) }}
                                    @endif
                                </td>
                                <td class="text-center">{{ $nombreUnidadPedido }}</td>
                                <td class="text-center">{{ number_format($cantidadEsperada, 2) }}</td>
                                <td class="text-center cantidadProducidaAcu">
                                    @if($estadoActual === 'pendientes')
                                        <input type="number" name="cantidad_producida_real[{{ $idReceta }}]"
                                            class="form-control form-control-sm production-input cantidad-no-personalizada" data-recid="{{ $idReceta }}"
                                            step="0.01" min="0"
                                            value="{{ old('cantidad_producida_real.'.$idReceta, $cantidadEsperada) }}"
                                            @if($recetaData['estado_general'] !== 'en_proceso') disabled @endif
                                            oninput="actualizarTotales({{ $idReceta }})">
                                        <input type="hidden" name="cantidad_producida_real_hidden[{{ $idReceta }}]" 
                                            value="{{ old("cantidad_producida_real.$idReceta", $cantidadEsperada) }}">
                                    @else
                                        @php
                                            // Obtener el acumulado de cantidad producida real para pedidos no personalizados terminados
                                            $cantidadProducidaAcumulada = \App\Models\ProduccionDetalle::where('id_recetas_cab', $receta->id_recetas)
                                                ->where('es_terminado', true)
                                                ->where('costo_diseño', 0)
                                                ->whereDate('created_at', now())
                                                ->sum('cantidad_producida_real');
                                        @endphp
                                        {{ number_format($cantidadProducidaAcumulada, 2) }}
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($estadoActual === 'pendientes')
                                        <select name="id_u_medidas_prodcc[{{ $idReceta }}]" class="form-control form-control-sm" {{ $recetaData['estado_general'] === 'en_proceso' ? '' : 'disabled' }}>
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
                                            <!-- Campos ocultos para el estado real -->
                                            <input type="hidden" name="es_iniciado[{{ $idReceta }}]" value="{{ $recetaData['estado_general'] === 'en_proceso' ? '1' : '0' }}">
                                            <input type="hidden" name="es_terminado[{{ $idReceta }}]" value="{{ $recetaData['estado_general'] === 'terminado' ? '1' : '0' }}">
                                            <input type="hidden" name="es_cancelado[{{ $idReceta }}]" value="{{ $recetaData['estado_general'] === 'cancelado' ? '1' : '0' }}">

                                            <!-- Checkbox UI para Iniciar -->
                                            <label class="btn btn-sm btn-outline-primary estado-btn {{ $recetaData['estado_general'] === 'en_proceso' ? 'active' : '' }} {{ $disableControls ? 'disabled' : '' }}">
                                                <input type="checkbox" name="es_iniciado_ui[{{ $idReceta }}]"
                                                    autocomplete="off" 
                                                    onchange="manejarEstado(this, {{ $idReceta }})"
                                                    {{ $recetaData['estado_general'] === 'en_proceso' ? 'checked' : '' }}
                                                    {{ $disableControls ? 'disabled' : '' }}> Iniciar
                                            </label>

                                            <!-- Checkbox UI para Terminar -->
                                            <label class="btn btn-sm btn-outline-success estado-btn {{ $recetaData['estado_general'] === 'terminado' ? 'active' : '' }}" 
                                                id="terminar-btn-{{ $idReceta }}"
                                                style="{{ $recetaData['estado_general'] === 'en_proceso' ? '' : 'pointer-events: none; opacity: 0.65;' }}">
                                                <input type="checkbox" name="es_terminado_ui[{{ $idReceta }}]"
                                                    autocomplete="off" 
                                                    onchange="manejarEstado(this, {{ $idReceta }})"
                                                    {{ $recetaData['estado_general'] === 'terminado' ? 'checked' : '' }}
                                                    {{ $recetaData['estado_general'] === 'en_proceso' ? '' : 'disabled' }}> Terminar
                                            </label>
                                            
                                            <!-- Checkbox UI para Cancelar -->
                                            <label class="btn btn-sm btn-outline-danger estado-btn {{ $recetaData['estado_general'] === 'cancelado' ? 'active' : '' }} {{ $disableControls ? 'disabled' : '' }}">
                                                <input type="checkbox" name="es_cancelado_ui[{{ $idReceta }}]"
                                                    autocomplete="off" 
                                                    onchange="manejarEstado(this, {{ $idReceta }})"
                                                    {{ $recetaData['estado_general'] === 'cancelado' ? 'checked' : '' }}
                                                    {{ $disableControls ? 'disabled' : '' }}> Cancelar
                                            </label>
                                        </div>
                                    @else
                                        <div class="estado-final">
                                            @if($recetaData['estado_general'] === 'terminado')
                                                <span class="badge badge-success">
                                                    <i class="fas fa-check-circle"></i> Terminado
                                                </span>
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
                                <td class="text-center subtotal-receta" id="subtotal-{{ $idReceta }}">S/ {{ number_format($subtotalReceta, 2) }}</td>
                                <td class="text-center" id="costo-diseno-{{ $idReceta }}">S/ 0.00</td>
                                <td class="text-center total-receta" id="total-{{ $idReceta }}">S/ {{ number_format($subtotalReceta, 2) }}</td>
                                <td class="text-center">
                                    @if($estadoActual === 'pendientes' || $estadoActual === 'terminados' || $estadoActual === 'cancelados')
                                        @php
                                            // Mostrar la suma de harina para todos los pedidos no personalizados de esta receta
                                            $cantHarinaTotal = 0;
                                            foreach ($pedidosNoPersonalizados as $pedidoNoPersonalizado) {
                                                $cantidadEsperadaPedido = ($receta->id_areas == 1)
                                                    ? $pedidoNoPersonalizado->cantidad * $receta->constante_peso_lata
                                                    : $pedidoNoPersonalizado->cantidad;
                                                $cantHarinaPedido = $componenteHarina ? $componenteHarina->cantidad * $cantidadEsperadaPedido : 0;
                                                $cantHarinaTotal += $cantHarinaPedido;
                                            }
                                        @endphp
                                        {{ number_format($cantHarinaTotal, 2) }} gramos
                                        <input type="hidden" name="id_recetas_det_harina[{{ $idReceta }}]" value="{{ $idHarina }}">
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($receta->instructivo)
                                    <button type="button" class="btn btn-sm btn-outline-info" id="btn-instructivo-{{ $idReceta }}" {{ $disableControls ? 'disabled' : '' }}
                                        data-toggle="tooltip" title="Ver instructivo"
                                        onclick="cargarInstructivo({{ $receta->id_recetas }}, '{{ $recetaData['estado_general'] }}')">
                                        <i class="fas fa-book-open"></i>
                                    </button>
                                    @endif
                                    
                                    <!-- Botón para ver detalles de pedidos personalizados -->
                                    @if($pedidosPersonalizados->count() > 0)
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                        data-toggle="modal" data-target="#detallesModal"
                                        onclick="mostrarDetallesPersonales({{ $idReceta }})">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @endif
                                    
                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                        data-toggle="tooltip" title="Agregar observación"
                                        onclick="mostrarModalObservacion(null, {{ $idReceta }})">
                                        <i class="fas fa-comment"></i>
                                    </button>
                                </td>
                            </tr>

                            <!-- Fila de detalles desplegable -->
                            <tr class="detalles-row" id="detalles-{{ $idReceta }}" style="display: none;">
                                <td colspan="13">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title">Detalles de Pedidos</h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>ID Pedido</th>
                                                            <th>Cliente</th>
                                                            <th>Tienda</th>
                                                            <th>Fecha/Hora</th>
                                                            <th>Cantidad</th>
                                                            <th>Estado</th>
                                                            <th>Observaciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($recetaData['pedidos'] as $pedido)
                                                            <tr>
                                                                <td>{{ $pedido->id_pedidos_det }}</td>
                                                                <td>{{ $pedido->pedidoCabecera->usuario->nombre_personal }}</td>
                                                                <td>{{ $pedido->pedidoCabecera->tienda->nombre }}</td>
                                                                <td>{{ $pedido->pedidoCabecera->hora_created }}</td>
                                                                <td>{{ number_format($pedido->cantidad, 2) }}</td>
                                                                <td>
                                                                    @if($pedido->id_estados == 4)
                                                                        <span class="badge badge-success">Terminado</span>
                                                                    @elseif($pedido->id_estados == 5)
                                                                        <span class="badge badge-danger">Cancelado</span>
                                                                    @elseif($pedido->id_estados == 3)
                                                                        <span class="badge badge-primary">En Proceso</span>
                                                                    @else
                                                                        <span class="badge badge-secondary">Pendiente</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if($pedido->es_personalizado)
                                                                        <button type="button" class="btn btn-xs btn-info" 
                                                                            data-toggle="tooltip" 
                                                                            title="{{ $pedido->descripcion ?? 'Sin descripción' }}">
                                                                            <i class="fas fa-info-circle"></i>
                                                                        </button>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <!-- Filas de pedidos personalizados -->
                            @if($pedidosPersonalizados->count() > 0)
                            @php
                            $pedidoCounter = 1;
                            @endphp
                            @foreach($pedidosPersonalizados as $pedido)
                            @php
                            $esIniciado = $pedido->id_estados == 3; // 3 = En proceso
                            $estadoGeneral = $pedido->id_estados == 5 ? 'cancelado' : 
                                            ($pedido->id_estados == 4 ? 'terminado' :
                                            ($pedido->id_estados == 3 ? 'en_proceso' : 'pendiente'));
                            
                            $cantidadPersonalizada = $pedido->cantidad;
                            $cantidadEsperadaPersonalizada = ($receta->id_areas == 1)
                                ? $cantidadPersonalizada * $receta->constante_peso_lata
                                : $cantidadPersonalizada;
                            
                            $subtotalPersonalizado = 0;
                            foreach ($receta->detalles as $detalle) {
                                $subtotalPersonalizado += $detalle->subtotal_receta * $cantidadEsperadaPersonalizada;
                            }
                            
                            $harinaPersonalizada = $componenteHarina ? $componenteHarina->cantidad * $cantidadEsperadaPersonalizada : 0;
                            
                            $imagenUrl = $pedido->foto_referencial ? asset('storage/' . str_replace('pedidos/', 'pedidos/', $pedido->foto_referencial)) : null;
                            @endphp

                            @if(($estadoActual === 'cancelados' && $estadoGeneral === 'cancelado') || ($estadoActual !== 'cancelados' && $estadoGeneral !== 'cancelado'))
                            <tr class="pedido-personalizado" data-recid="{{ $idReceta }}">
                                <td colspan="2">
                                    <div class="d-flex align-items-center">
                                        <strong class="mr-2">{{ $recetaCounter }}.{{ $pedidoCounter }}. {{ $receta->producto->nombre ?? 'N/A' }}</strong>
                                        <span class="badge badge-warning">Personalizado</span>
                                        <!-- <button type="button" class="btn btn-xs btn-info ml-2" 
                                            data-toggle="tooltip" title="{{ $pedido->descripcion ?? 'Sin descripción' }}">
                                            <i class="fas fa-info-circle"></i>
                                        </button> -->
                                    </div>
                                    <small class="text-muted">Pedido #{{ $pedido->id_pedidos_det }}</small>
                                </td>
                                <td class="text-center">{{ number_format($cantidadPersonalizada, 2) }}</td>
                                <td class="text-center">{{ $nombreUnidadPedido }}</td>
                                <td class="text-center">{{ number_format($cantidadEsperadaPersonalizada, 2) }}</td>
                                <td class="text-center">
                                    @if($estadoActual === 'pendientes')
                                        <input type="number" name="cantidad_producida_real_personalizado[{{ $pedido->id_pedidos_det }}]"
                                            class="form-control form-control-sm production-input cantidad-personalizada" data-recid="{{ $idReceta }}" data-pedidoid="{{ $pedido->id_pedidos_det }}"
                                            step="0.01" min="0"
                                            value="{{ old('cantidad_producida_real_personalizado.'.$pedido->id_pedidos_det, $cantidadPersonalizada) }}"
                                            @if(!$esIniciado) disabled @endif
                                            oninput="actualizarTotales({{ $idReceta }})">
                                    @else
                                        @php
                                            $produccionDet = \App\Models\ProduccionDetalle::whereJsonContains('pedidos_ids', $pedido->id_pedidos_det)
                                                ->where(function($q) use ($estadoActual) {
                                                    if ($estadoActual === 'terminados') $q->where('es_terminado', true);
                                                    if ($estadoActual === 'cancelados') $q->where('es_cancelado', true);
                                                })
                                                ->first();
                                        @endphp
                                        {{ number_format($produccionDet ? $produccionDet->cantidad_producida_real : $cantidadPersonalizada, 2) }}
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($estadoActual === 'pendientes')
                                        <select name="id_u_medidas_prodcc_personalizado[{{ $pedido->id_pedidos_det }}]" class="form-control form-control-sm" {{ $esIniciado ? '' : 'disabled' }}>
                                            @foreach($unidadesMedida as $unidad)
                                                <option value="{{ $unidad->id_u_medidas }}"
                                                    {{ $unidad->id_u_medidas == $recetaData['id_u_medidas'] ? 'selected' : '' }}>
                                                    {{ $unidad->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        {{ $unidadesMedida->firstWhere('id_u_medidas', $recetaData['id_u_medidas'])->nombre ?? 'N/A' }}
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($estadoActual === 'pendientes')
                                        <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                            <input type="hidden" name="es_iniciado_personalizado[{{ $pedido->id_pedidos_det }}]" value="0">
                                            <input type="hidden" name="es_terminado_personalizado[{{ $pedido->id_pedidos_det }}]" value="0">
                                            <input type="hidden" name="es_cancelado_personalizado[{{ $pedido->id_pedidos_det }}]" value="0">

                                            <label class="btn btn-sm btn-outline-primary estado-btn {{ $pedido->id_estados == 3 ? 'active' : '' }}">
                                                <input type="checkbox" name="es_iniciado_personalizado[{{ $pedido->id_pedidos_det }}]"
                                                    autocomplete="off" value="1"
                                                    onchange="manejarEstadoPersonalizado(this, {{ $pedido->id_pedidos_det }}, {{ $idReceta }})"
                                                    {{ $pedido->id_estados == 3 ? 'checked' : '' }}> Iniciar
                                            </label>
                                            
                                            <label class="btn btn-sm btn-outline-success estado-btn {{ $pedido->id_estados == 4 ? 'active' : '' }} {{ $pedido->id_estados == 3 ? '' : 'disabled' }}" 
                                               id="terminar-btn-{{ $pedido->id_pedidos_det }}">
                                                <input type="checkbox" name="es_terminado_personalizado[{{ $pedido->id_pedidos_det }}]"
                                                       autocomplete="off" value="1"
                                                       onchange="manejarEstadoPersonalizado(this, {{ $pedido->id_pedidos_det }}, {{ $idReceta }})"
                                                       {{ $pedido->id_estados == 4 ? 'checked' : '' }}>
                                                Terminar
                                            </label>
                                            
                                            <label class="btn btn-sm btn-outline-danger estado-btn {{ $pedido->id_estados == 5 ? 'active' : '' }}">
                                                <input type="checkbox" name="es_cancelado_personalizado[{{ $pedido->id_pedidos_det }}]"
                                                    autocomplete="off" value="1"
                                                    onchange="manejarEstadoPersonalizado(this, {{ $pedido->id_pedidos_det }}, {{ $idReceta }})"
                                                    {{ $pedido->id_estados == 5 ? 'checked' : '' }}> Cancelar
                                            </label>
                                        </div>
                                    @else
                                        <span class="badge badge-secondary">
                                            @if($pedido->id_estados == 4)
                                                <i class="fas fa-check-circle"></i> Terminado
                                            @elseif($pedido->id_estados == 5)
                                                <i class="fas fa-times-circle"></i> Cancelado
                                            @else
                                                <i class="fas fa-clock"></i> Pendiente
                                            @endif
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($estadoActual === 'pendientes')
                                        S/ {{ number_format($subtotalPersonalizado, 2) }}
                                    @else
                                        @php
                                            $produccionDet = \App\Models\ProduccionDetalle::whereJsonContains('pedidos_ids', $pedido->id_pedidos_det)
                                                ->where('es_terminado', true)
                                                ->first();
                                        @endphp
                                        S/ {{ number_format($produccionDet ? $produccionDet->subtotal_receta : 0, 2) }}
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($estadoActual === 'pendientes')
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">S/</span>
                                        </div>
                                        <input type="number"
                                            name="costo_diseño[{{ $pedido->id_pedidos_det }}]"
                                            class="form-control form-control-sm costo-diseno input-costo-diseno costo-diseno-personalizado" data-recid="{{ $idReceta }}" data-pedidoid="{{ $pedido->id_pedidos_det }}"
                                            step="1.00" min="0"
                                            value="{{ old("costo_diseño.".$pedido->id_pedidos_det, $pedido->costo_diseño ?? 0) }}"
                                            {{ $esIniciado ? '' : 'disabled' }}
                                            onchange="actualizarTotales({{ $idReceta }})">
                                    </div>
                                    @else
                                        @php
                                            $produccionDet = \App\Models\ProduccionDetalle::whereJsonContains('pedidos_ids', $pedido->id_pedidos_det)
                                                ->where('es_terminado', true)
                                                ->first();
                                        @endphp
                                        S/ {{ number_format($produccionDet ? $produccionDet->costo_diseño : 0, 2) }}
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($estadoActual === 'pendientes')
                                    S/ {{ number_format($subtotalPersonalizado + ($pedido->costo_diseño ?? 0), 2) }}
                                    @else
                                        @php
                                            $produccionDet = \App\Models\ProduccionDetalle::whereJsonContains('pedidos_ids', $pedido->id_pedidos_det)
                                                ->where('es_terminado', true)
                                                ->first();
                                        @endphp
                                        S/ {{ number_format($produccionDet ? $produccionDet->total_receta : 0, 2) }}
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($estadoActual === 'pendientes')
                                    {{ number_format($harinaPersonalizada, 2) }} g
                                    @else
                                        @php
                                            $produccionDet = \App\Models\ProduccionDetalle::whereJsonContains('pedidos_ids', $pedido->id_pedidos_det)
                                                ->where('es_terminado', true)
                                                ->first();
                                        @endphp
                                        {{ number_format($produccionDet ? $produccionDet->cant_harina : 0, 2) }} g
                                    @endif
                                    <input type="hidden" name="id_recetas_det_harina_personalizado[{{ $pedido->id_pedidos_det }}]" value="{{ $idHarina }}">
                                </td>
                                <td class="text-center">
                                    @if($imagenUrl)
                                    <button type="button" class="btn btn-xs btn-primary view-image-btn"
                                        data-image-url="{{ $imagenUrl }}">
                                        <i class="fas fa-image"></i>
                                    </button>
                                    @endif
                                    
                                    @if($receta->instructivo)
                                    <button type="button" class="btn btn-xs btn-outline-info" 
                                        data-toggle="tooltip" title="Ver instructivo"
                                        onclick="cargarInstructivo({{ $receta->id_recetas }}, '{{ $estadoGeneral ?? 'pendiente' }}', {{ $pedido->id_pedidos_det }})">
                                        <i class="fas fa-book-open"></i>
                                    </button>
                                    @endif
                                    
                                    <button type="button" class="btn btn-xs btn-outline-secondary"
                                        data-toggle="tooltip" title="Agregar observación"
                                        onclick="mostrarModalObservacionPersonalizado({{ $pedido->id_pedidos_det }}, {{ $idReceta }})">
                                        <i class="fas fa-comment"></i>
                                    </button>
                                </td>
                            </tr>
                            @endif
                            @php
                            $pedidoCounter++;
                            @endphp
                            @endforeach

                            <!-- Fila de totales cuando hay pedidos personalizados -->
                            <tr class="total-receta-agrupada bg-light" data-recid="{{ $idReceta }}">
                                <td colspan="2" class="text-right"><strong>Totales:</strong></td>
                                <td class="text-center">{{ number_format($cantidadNoPersonalizada + $pedidosPersonalizados->sum('cantidad'), 2) }}</td>
                                <td class="text-center"></td>
                                <td class="text-center">{{ number_format($cantidadEsperada + $pedidosPersonalizados->sum(function($p) use ($receta) {
                                    return $receta->id_areas == 1 ? $p->cantidad * $receta->constante_peso_lata : $p->cantidad;
                                }), 2) }}</td>
                                <td class="text-center">
                                    @if($estadoActual === 'pendientes')
                                        {{ number_format(($recetaData['cantidad_producida_real'] ?? $cantidadEsperada) + $pedidosPersonalizados->sum('cantidad'), 2) }}
                                    @else
                                        @php
                                            $totalCantidadProducida = $pedidosPersonalizados->sum(function($pedido) {
                                                $produccionDet = \App\Models\ProduccionDetalle::whereJsonContains('pedidos_ids', $pedido->id_pedidos_det)
                                                    ->where('es_terminado', true)
                                                    ->first();
                                                return $produccionDet ? $produccionDet->cantidad_producida_real : 0;
                                            });
                                        @endphp
                                        {{ number_format($totalCantidadProducida, 2) }}
                                    @endif
                                </td>
                                <td class="text-center"></td>
                                <td class="text-center"></td>
                                <td class="text-center">
                                    @if($estadoActual === 'pendientes')
                                        @php
                                            $subtotalPersonalizados = $pedidosPersonalizados->sum(function($p) use ($receta) {
                                    $cant = $p->cantidad;
                                    $esperada = $receta->id_areas == 1 ? $cant * $receta->constante_peso_lata : $cant;
                                    $subtotal = 0;
                                    foreach ($receta->detalles as $detalle) {
                                        $subtotal += $detalle->subtotal_receta * $esperada;
                                    }
                                    return $subtotal + ($p->costo_diseño ?? 0);
                                            });
                                        @endphp
                                        S/ {{ number_format($subtotalReceta + $subtotalPersonalizados, 2) }}
                                    @else
                                        @php
                                            $totalGeneral = $pedidosPersonalizados->sum(function($pedido) {
                                                $produccionDet = \App\Models\ProduccionDetalle::whereJsonContains('pedidos_ids', $pedido->id_pedidos_det)
                                                    ->where('es_terminado', true)
                                                    ->first();
                                                return $produccionDet ? $produccionDet->total_receta : 0;
                                            });
                                        @endphp
                                        S/ {{ number_format($totalGeneral, 2) }}
                                    @endif
                                </td>
                                <td class="text-center" id="total-costo-diseno-{{ $idReceta }}">
                                    @if($estadoActual === 'pendientes')
                                        @php
                                            $totalCostoDiseno = $pedidosPersonalizados->sum(function($p) {
                                                return $p->costo_diseño ?? 0;
                                            });
                                        @endphp
                                        S/ {{ number_format($totalCostoDiseno, 2) }}
                                    @else
                                        @php
                                            $totalCostoDiseno = $pedidosPersonalizados->sum(function($pedido) {
                                                $produccionDet = \App\Models\ProduccionDetalle::whereJsonContains('pedidos_ids', $pedido->id_pedidos_det)
                                                    ->where('es_terminado', true)
                                                    ->first();
                                                return $produccionDet ? $produccionDet->costo_diseño : 0;
                                            });
                                        @endphp
                                        S/ {{ number_format($totalCostoDiseno, 2) }}
                                    @endif
                                </td>
                                <td class="text-center" id="total-general-{{ $idReceta }}">
                                    @if($estadoActual === 'pendientes')
                                        @php
                                            $subtotalPersonalizados = $pedidosPersonalizados->sum(function($p) use ($receta) {
                                    $cant = $p->cantidad;
                                    $esperada = $receta->id_areas == 1 ? $cant * $receta->constante_peso_lata : $cant;
                                    $subtotal = 0;
                                    foreach ($receta->detalles as $detalle) {
                                        $subtotal += $detalle->subtotal_receta * $esperada;
                                    }
                                    return $subtotal + ($p->costo_diseño ?? 0);
                                            });
                                        @endphp
                                        S/ {{ number_format($subtotalReceta + $subtotalPersonalizados, 2) }}
                                    @else
                                        @php
                                            $totalGeneral = $pedidosPersonalizados->sum(function($pedido) {
                                                $produccionDet = \App\Models\ProduccionDetalle::whereJsonContains('pedidos_ids', $pedido->id_pedidos_det)
                                                    ->where('es_terminado', true)
                                                    ->first();
                                                return $produccionDet ? $produccionDet->total_receta : 0;
                                            });
                                        @endphp
                                        S/ {{ number_format($totalGeneral, 2) }}
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($estadoActual === 'pendientes')
                                        @php
                                            $totalHarina = $cantHarina + $pedidosPersonalizados->sum(function($p) use ($componenteHarina, $receta) {
                                        $cant = $p->cantidad;
                                        $esperada = $receta->id_areas == 1 ? $cant * $receta->constante_peso_lata : $cant;
                                        return $componenteHarina ? $componenteHarina->cantidad * $esperada : 0;
                                            });
                                        @endphp
                                        {{ number_format($totalHarina, 2) }} g
                                    @else
                                        @php
                                            $totalHarina = $pedidosPersonalizados->sum(function($pedido) {
                                                $produccionDet = \App\Models\ProduccionDetalle::whereJsonContains('pedidos_ids', $pedido->id_pedidos_det)
                                                    ->where('es_terminado', true)
                                                    ->first();
                                                return $produccionDet ? $produccionDet->cant_harina : 0;
                                            });
                                        @endphp
                                        {{ number_format($totalHarina, 2) }} g
                                    @endif
                                </td>
                                <td class="text-center"></td>
                            </tr>
                            @endif
                            @php
                            $recetaCounter++;
                            @endphp
                            @endforeach
                            @else
                            <tr>
                                <td colspan="13" class="text-center text-muted py-4 no-orders">
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

<!-- Modal para detalles de pedidos personalizados -->
<div class="modal fade" id="detallesModal" tabindex="-1" aria-labelledby="detallesModalLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="detallesModalLabel">Detalles de Pedidos Personalizados</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detallesModalContent">
                <!-- Contenido se llenará dinámicamente -->
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
                    <div id="estadoObservacionGuardada" class="mt-2 text-success" style="display:none;">
    <i class="fas fa-check-circle"></i> Observación guardada correctamente.
</div>
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

    .estado-btn {
        min-width: 80px;
        text-align: center;
    }
    .estado-btn.disabled {
        opacity: 0.65;
        pointer-events: none;
    }

    /* Agrega esto al final de tus estilos */
    .estado-btn {
        position: relative;
    }
    .estado-btn.disabled {
        opacity: 0.6;
        pointer-events: none;
    }
    .estado-btn input[type="checkbox"] {
        position: absolute;
        opacity: 0;
        width: 100%;
        height: 100%;
        left: 0;
        top: 0;
        margin: 0;
        cursor: pointer;
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

    /* Animación para cambios en totales */
    .highlight {
        animation: highlight 1s;
    }

    @keyframes highlight {
        0% { background-color: #fffde7; }
        100% { background-color: transparent; }
    }

    .input-costo-diseno {
        min-width: 80px;
        max-width: 120px;
    }
</style>

<script>
/**
 * Sistema de gestión de estados de producción
 * 
 * Este script maneja la lógica de la interfaz de producción, incluyendo:
 * - Control de estados (iniciar, terminar, cancelar)
 * - Validaciones de formulario
 * - Manejo de modales
 * - Cálculo de totales
 */

// Sistema de logging mejorado
function logAction(message, data = {}) {
    const timestamp = new Date().toISOString();
    console.log(`[${timestamp}] ${message}`, data);
}

/**
 * Maneja el cambio de estado de una receta
 * @param {HTMLInputElement} checkbox - El checkbox que disparó el evento
 * @param {string|number} idReceta - ID de la receta afectada
 */
function manejarEstado(checkbox, idReceta) {
    const tipo = checkbox.name.split('[')[0];
    const isChecked = checkbox.checked;

    // Actualizar el campo oculto correspondiente
    const nombreCampoOculto = tipo.replace('_ui', '');
    const hiddenInput = document.querySelector(`input[type="hidden"][name="${nombreCampoOculto}[${idReceta}]"]`);
    
    if (hiddenInput) {
        hiddenInput.value = isChecked ? '1' : '0';
    }

    if (tipo === 'es_iniciado_ui') {
        const cantidadInput = document.querySelector(`input[name="cantidad_producida_real[${idReceta}]"]`);
        const unidadSelect = document.querySelector(`select[name="id_u_medidas_prodcc[${idReceta}]"]`);
        if (cantidadInput) cantidadInput.disabled = !isChecked;
        if (unidadSelect) unidadSelect.disabled = !isChecked;
        const terminarBtn = document.getElementById(`terminar-btn-${idReceta}`);
        if (terminarBtn) {
            terminarBtn.style.pointerEvents = isChecked ? 'auto' : 'none';
            terminarBtn.style.opacity = isChecked ? '1' : '0.65';
            terminarBtn.classList.toggle('disabled', !isChecked);
            const terminarCheckbox = terminarBtn.querySelector('input[type="checkbox"]');
            if (terminarCheckbox) terminarCheckbox.disabled = !isChecked;
        }
        if (isChecked) desmarcarOtrosEstados(idReceta, tipo, true);
    } else if (tipo === 'es_terminado_ui' && isChecked) {
        if (!confirm('¿Está seguro de marcar como terminado? Una vez terminado no podrá editar los datos.')) {
            checkbox.checked = false;
            if (hiddenInput) hiddenInput.value = '0';
            return;
        }
        if (!validarTerminado(idReceta)) {
            checkbox.checked = false;
            if (hiddenInput) hiddenInput.value = '0';
            return;
        }
        const iniciadoCheckbox = document.querySelector(`input[name="es_iniciado_ui[${idReceta}]"]`);
        const iniciadoHidden = document.querySelector(`input[type="hidden"][name="es_iniciado[${idReceta}]"]`);
        if (iniciadoCheckbox && !iniciadoCheckbox.checked) {
            iniciadoCheckbox.checked = true;
            if (iniciadoHidden) iniciadoHidden.value = '1';
        }
        const cantidadInput = document.querySelector(`input[name="cantidad_producida_real[${idReceta}]"]`);
        if (cantidadInput) {
            const cantidadHidden = document.createElement('input');
            cantidadHidden.type = 'hidden';
            cantidadHidden.name = `cantidad_producida_real[${idReceta}]`;
            cantidadHidden.value = cantidadInput.value;
            cantidadInput.parentNode.appendChild(cantidadHidden);
            cantidadInput.disabled = true;
        }
        const unidadSelect = document.querySelector(`select[name="id_u_medidas_prodcc[${idReceta}]"]`);
        if (unidadSelect) {
            const unidadHidden = document.createElement('input');
            unidadHidden.type = 'hidden';
            unidadHidden.name = `id_u_medidas_prodcc[${idReceta}]`;
            unidadHidden.value = unidadSelect.value;
            unidadSelect.parentNode.appendChild(unidadHidden);
            unidadSelect.disabled = true;
        }
        desmarcarOtrosEstados(idReceta, tipo, true);
    } else if (tipo === 'es_cancelado_ui' && isChecked) {
        // Al cancelar, desmarcar otros estados
        desmarcarOtrosEstados(idReceta, tipo, false);
        // Si no está iniciado, crear campos ocultos con valores por defecto
        const iniciadoHidden = document.querySelector(`input[type="hidden"][name="es_iniciado[${idReceta}]"]`);
        if (!iniciadoHidden || iniciadoHidden.value !== '1') {
            // Crear campo oculto para cantidad producida con valor 0
            const cantidadHidden = document.createElement('input');
            cantidadHidden.type = 'hidden';
            cantidadHidden.name = `cantidad_producida_real[${idReceta}]`;
            cantidadHidden.value = '0';
            document.getElementById('produccionForm').appendChild(cantidadHidden);
            // Crear campo oculto para unidad de medida con valor por defecto
            const unidadHidden = document.createElement('input');
            unidadHidden.type = 'hidden';
            unidadHidden.name = `id_u_medidas_prodcc[${idReceta}]`;
            unidadHidden.value = document.querySelector(`select[name="id_u_medidas_prodcc[${idReceta}]"]`)?.value || '';
            document.getElementById('produccionForm').appendChild(unidadHidden);
        }
        // Mostrar modal de observación
        mostrarModalObservacion(null, idReceta, true);
    }
}

/**
 * Habilita/deshabilita controles de una receta
 * @param {string|number} idReceta - ID de la receta
 * @param {boolean} habilitar - True para habilitar, false para deshabilitar
 */
function habilitarControlesReceta(idReceta, habilitar) {
    // Cantidad producida
    const cantidadInput = document.querySelector(`input[name="cantidad_producida_real[${idReceta}]"]`);
    if (cantidadInput) {
        cantidadInput.disabled = !habilitar;
        logAction(`Cantidad producida ${habilitar ? 'habilitada' : 'deshabilitada'}`);
    }

    // Unidad de medida
    const unidadSelect = document.querySelector(`select[name="id_u_medidas_prodcc[${idReceta}]"]`);
    if (unidadSelect) {
        unidadSelect.disabled = !habilitar;
        logAction(`Unidad de medida ${habilitar ? 'habilitada' : 'deshabilitada'}`);
    }

    // Botón Terminar
    const terminarBtn = document.getElementById(`terminar-btn-${idReceta}`);
    if (terminarBtn) {
        if (habilitar) {
            terminarBtn.style.pointerEvents = 'auto';
            terminarBtn.style.opacity = '1';
            terminarBtn.classList.remove('disabled');
        } else {
            terminarBtn.style.pointerEvents = 'none';
            terminarBtn.style.opacity = '0.65';
            terminarBtn.classList.add('disabled');
        }
        
        const terminarCheckbox = terminarBtn.querySelector('input[type="checkbox"]');
        if (terminarCheckbox) terminarCheckbox.disabled = !habilitar;
        logAction(`Botón Terminar ${habilitar ? 'habilitado' : 'deshabilitado'}`);
    }
}

/**
 * Desmarca otros estados cuando se selecciona uno
 * @param {string|number} idReceta - ID de la receta
 * @param {string} tipoActual - Tipo de estado actual ('es_iniciado_ui', etc.)
 * @param {boolean} mantenerIniciado - Si se debe mantener el estado iniciado
 */
function desmarcarOtrosEstados(idReceta, tipoActual, mantenerIniciado = false) {
    const estados = ['es_iniciado_ui', 'es_terminado_ui', 'es_cancelado_ui'];
    
    estados.forEach(tipo => {
        if (tipo !== tipoActual && !(mantenerIniciado && tipo === 'es_iniciado_ui')) {
            // Desmarcar checkbox UI
            const checkboxUI = document.querySelector(`input[name="${tipo}[${idReceta}]"]`);
            if (checkboxUI) checkboxUI.checked = false;
            
            // Actualizar campo oculto (elimina '_ui' del nombre)
            const nombreCampoOculto = tipo.replace('_ui', '');
            const hiddenInput = document.querySelector(`input[type="hidden"][name="${nombreCampoOculto}[${idReceta}]"]`);
            if (hiddenInput) hiddenInput.value = '0';
        }
    });
}

/**
 * Valida si se puede marcar como terminado
 * @param {string|number} idReceta - ID de la receta
 * @returns {boolean} True si es válido, false si no
 */
function validarTerminado(idReceta) {
    const cantidadInput = document.querySelector(`input[name="cantidad_producida_real[${idReceta}]"]`);
    
    if (!cantidadInput || !cantidadInput.value || parseFloat(cantidadInput.value) <= 0) {
        alert('Debe ingresar una cantidad producida válida antes de terminar.');
        logAction('Validación fallida - Cantidad producida inválida', {
            receta_id: idReceta,
            valor: cantidadInput?.value
        });
        return false;
    }
    
    logAction('Validación para terminar exitosa', { receta_id: idReceta });
    return true;
}

/**
 * Muestra el estado actual de una receta en consola
 * @param {string|number} idReceta - ID de la receta
 */
function mostrarEstadoActual(idReceta) {
    try {
        const estado = {
            iniciado: document.querySelector(`input[name="es_iniciado_ui[${idReceta}]"]`)?.checked || false,
            terminado: document.querySelector(`input[name="es_terminado_ui[${idReceta}]"]`)?.checked || false,
            cancelado: document.querySelector(`input[name="es_cancelado_ui[${idReceta}]"]`)?.checked || false,
            cantidad: document.querySelector(`input[name="cantidad_producida_real[${idReceta}]"]`)?.value || '0',
            cantidad_disabled: document.querySelector(`input[name="cantidad_producida_real[${idReceta}]"]`)?.disabled || true,
            unidad_disabled: document.querySelector(`select[name="id_u_medidas_prodcc[${idReceta}]"]`)?.disabled || true
        };

        logAction(`Estado actual - Receta ${idReceta}`, estado);
    } catch (error) {
        logAction('Error al mostrar estado actual', { error: error.message });
    }
}

/**
 * Sistema de gestión de estados para pedidos personalizados
 * 
 * Este sistema maneja tres estados principales:
 * 1. Iniciado (es_iniciado_personalizado)
 * 2. Terminado (es_terminado_personalizado)
 * 3. Cancelado (es_cancelado_personalizado)
 * 
 * Cada estado tiene su propio campo oculto y checkbox UI:
 * - Campo oculto: es_iniciado_personalizado[id], es_terminado_personalizado[id], es_cancelado_personalizado[id]
 * - Checkbox UI: es_iniciado_personalizado_ui[id], es_terminado_personalizado_ui[id], es_cancelado_personalizado_ui[id]
 */

/**
 * Maneja el cambio de estado de un pedido personalizado
 * @param {HTMLInputElement} checkbox - El checkbox que disparó el evento
 * @param {string|number} idPedido - ID del pedido personalizado
 * @param {string|number} idReceta - ID de la receta padre
 */
function manejarEstadoPersonalizado(checkbox, idPedido, idReceta) {
    const tipo = checkbox.name.split('[')[0];
    const isChecked = checkbox.checked;

    // Actualizar el campo oculto correspondiente
    const nombreCampoOculto = tipo;
    const hiddenInput = document.querySelector(`input[type="hidden"][name="${nombreCampoOculto}[${idPedido}]"]`);
    
    if (hiddenInput) {
        hiddenInput.value = isChecked ? '1' : '0';
        logAction(`Campo oculto actualizado - ${nombreCampoOculto}`, {
        pedido_id: idPedido,
            valor: hiddenInput.value,
            checkbox_checked: isChecked
        });
    }

    // Lógica específica para cada tipo de estado
    if (tipo === 'es_iniciado_personalizado') {
        // Habilitar/deshabilitar cantidad producida
        const cantidadInput = document.querySelector(`input[name="cantidad_producida_real_personalizado[${idPedido}]"]`);
        if (cantidadInput) {
            cantidadInput.disabled = !isChecked;
            logAction(`Cantidad producida ${isChecked ? 'habilitada' : 'deshabilitada'} para pedido ${idPedido}`);
        }

        // Habilitar/deshabilitar unidad de medida
        const unidadSelect = document.querySelector(`select[name="id_u_medidas_prodcc_personalizado[${idPedido}]"]`);
        if (unidadSelect) {
            unidadSelect.disabled = !isChecked;
            logAction(`Unidad de medida ${isChecked ? 'habilitada' : 'deshabilitada'} para pedido ${idPedido}`);
        }

        // Habilitar/deshabilitar costo diseño
        const costoInput = document.querySelector(`input[name="costo_diseño[${idPedido}]"]`);
        if (costoInput) {
            costoInput.disabled = !isChecked;
            logAction(`Costo diseño ${isChecked ? 'habilitado' : 'deshabilitado'} para pedido ${idPedido}`);
        }

        // Actualizar botón terminar
        const terminarBtn = document.getElementById(`terminar-btn-${idPedido}`);
        if (terminarBtn) {
            if (isChecked) {
                terminarBtn.style.pointerEvents = 'auto';
                terminarBtn.style.opacity = '1';
                terminarBtn.classList.remove('disabled');
                const terminarCheckbox = terminarBtn.querySelector('input[type="checkbox"]');
                if (terminarCheckbox) terminarCheckbox.disabled = false;
            } else {
                terminarBtn.style.pointerEvents = 'none';
                terminarBtn.style.opacity = '0.65';
            terminarBtn.classList.add('disabled');
            const terminarCheckbox = terminarBtn.querySelector('input[type="checkbox"]');
                if (terminarCheckbox) terminarCheckbox.disabled = true;
            }
        }

        if (isChecked) {
            desmarcarOtrosEstadosPersonalizado(idPedido, tipo, true);
        }
    } else if (tipo === 'es_terminado_personalizado' && isChecked) {
        // Mostrar confirmación antes de terminar
        if (!confirm('¿Está seguro de marcar como terminado? Una vez terminado no podrá editar los datos.')) {
        checkbox.checked = false;
            if (hiddenInput) hiddenInput.value = '0';
        return;
    }
    
        if (!validarTerminadoPersonalizado(checkbox, idPedido, idReceta)) {
        checkbox.checked = false;
            if (hiddenInput) hiddenInput.value = '0';
        return;
    }
    
        // Asegurarnos de que el estado iniciado esté marcado
        const iniciadoHidden = document.querySelector(`input[type="hidden"][name="es_iniciado_personalizado[${idPedido}]"]`);
        if (iniciadoHidden) {
            iniciadoHidden.value = '1';
            logAction('Estado iniciado forzado para pedido personalizado', {
                pedido_id: idPedido,
                valor: iniciadoHidden.value
            });
        }

        // Deshabilitar campos al terminar pero mantener los valores
        const cantidadInput = document.querySelector(`input[name="cantidad_producida_real_personalizado[${idPedido}]"]`);
        if (cantidadInput) {
            // Crear un campo oculto con el valor actual
            const cantidadHidden = document.createElement('input');
            cantidadHidden.type = 'hidden';
            cantidadHidden.name = `cantidad_producida_real_personalizado[${idPedido}]`;
            cantidadHidden.value = cantidadInput.value;
            cantidadInput.parentNode.appendChild(cantidadHidden);
            cantidadInput.disabled = true;
        }

        const unidadSelect = document.querySelector(`select[name="id_u_medidas_prodcc_personalizado[${idPedido}]"]`);
        if (unidadSelect) {
            // Crear un campo oculto con el valor actual
            const unidadHidden = document.createElement('input');
            unidadHidden.type = 'hidden';
            unidadHidden.name = `id_u_medidas_prodcc_personalizado[${idPedido}]`;
            unidadHidden.value = unidadSelect.value;
            unidadSelect.parentNode.appendChild(unidadHidden);
            unidadSelect.disabled = true;
        }

    const costoInput = document.querySelector(`input[name="costo_diseño[${idPedido}]"]`);
        if (costoInput) {
            // Crear un campo oculto con el valor actual
            const costoHidden = document.createElement('input');
            costoHidden.type = 'hidden';
            costoHidden.name = `costo_diseño[${idPedido}]`;
            costoHidden.value = costoInput.value;
            costoInput.parentNode.appendChild(costoHidden);
            costoInput.disabled = true;
        }
        
        desmarcarOtrosEstadosPersonalizado(idPedido, tipo, true);
    } else if (tipo === 'es_cancelado_personalizado' && isChecked) {
        // Al cancelar, desmarcar otros estados
        desmarcarOtrosEstadosPersonalizado(idPedido, tipo, false);

        // Si no está iniciado, crear campos ocultos con valores por defecto
        const iniciadoHidden = document.querySelector(`input[type="hidden"][name="es_iniciado_personalizado[${idPedido}]"]`);
        if (!iniciadoHidden || iniciadoHidden.value !== '1') {
            // Crear campo oculto para cantidad producida con valor 0
            const cantidadHidden = document.createElement('input');
            cantidadHidden.type = 'hidden';
            cantidadHidden.name = `cantidad_producida_real_personalizado[${idPedido}]`;
            cantidadHidden.value = '0';
            document.getElementById('produccionForm').appendChild(cantidadHidden);

            // Crear campo oculto para unidad de medida con valor por defecto
            const unidadHidden = document.createElement('input');
            unidadHidden.type = 'hidden';
            unidadHidden.name = `id_u_medidas_prodcc_personalizado[${idPedido}]`;
            unidadHidden.value = document.querySelector(`select[name="id_u_medidas_prodcc_personalizado[${idPedido}]"]`)?.value || '';
            document.getElementById('produccionForm').appendChild(unidadHidden);

            // Crear campo oculto para costo diseño con valor 0
            const costoHidden = document.createElement('input');
            costoHidden.type = 'hidden';
            costoHidden.name = `costo_diseño[${idPedido}]`;
            costoHidden.value = '0';
            document.getElementById('produccionForm').appendChild(costoHidden);
        }

        // Mostrar modal de observación
        mostrarModalObservacion(idPedido, idReceta, true);
    }
}

/**
 * Muestra el estado actual de un pedido personalizado en consola
 * @param {string|number} idPedido - ID del pedido personalizado
 */
function mostrarEstadoActualPersonalizado(idPedido) {
    console.log('=== MOSTRANDO ESTADO ACTUAL PERSONALIZADO ===');
    console.log('ID del pedido:', idPedido);
    
    try {
        // Obtener el estado actual del checkbox
        const iniciadoCheckbox = document.querySelector(`input[name="es_iniciado_personalizado[${idPedido}]"]`);
        const terminadoCheckbox = document.querySelector(`input[name="es_terminado_personalizado[${idPedido}]"]`);
        const canceladoCheckbox = document.querySelector(`input[name="es_cancelado_personalizado[${idPedido}]"]`);
        
        console.log('Checkbox iniciado encontrado:', iniciadoCheckbox);
        console.log('Checkbox terminado encontrado:', terminadoCheckbox);
        console.log('Checkbox cancelado encontrado:', canceladoCheckbox);
        
        // Obtener el estado de los controles
        const cantidadInput = document.querySelector(`input[name="cantidad_producida_real_personalizado[${idPedido}]"]`);
        const costoInput = document.querySelector(`input[name="costo_diseño[${idPedido}]"]`);
        
        console.log('Input cantidad encontrado:', cantidadInput);
        console.log('Input costo encontrado:', costoInput);
        
        const estado = {
            iniciado: iniciadoCheckbox ? iniciadoCheckbox.checked : false,
            terminado: terminadoCheckbox ? terminadoCheckbox.checked : false,
            cancelado: canceladoCheckbox ? canceladoCheckbox.checked : false,
            cantidad: cantidadInput ? cantidadInput.value : '0',
            cantidad_disabled: cantidadInput ? cantidadInput.disabled : true,
            costo_disabled: costoInput ? costoInput.disabled : true
        };

        console.log('Estado actual:', estado);
        console.log('=== FIN DE MOSTRAR ESTADO ACTUAL PERSONALIZADO ===');
    } catch (error) {
        console.error('Error al mostrar estado actual:', error);
    }



    try {
        const iniciado = document.querySelector(`input[name="es_iniciado_personalizado[${idPedido}]"]`)?.checked || false;
        const terminado = document.querySelector(`input[name="es_terminado_personalizado[${idPedido}]"]`)?.checked || false;
        const cancelado = document.querySelector(`input[name="es_cancelado_personalizado[${idPedido}]"]`)?.checked || false;
        const canceladoHidden = document.querySelector(`input[type="hidden"][name="es_cancelado_personalizado[${idPedido}]"]`)?.value === '1';
        logAction(`___________________Estado actual - Pedido personalizado ${idPedido}`, {
            iniciado,
            terminado,
            cancelado,
            canceladoHidden
        });
    } catch (error) {
        logAction('___________________Error al mostrar estado actual personalizado', { error: error.message });
    }
}

/**
 * Habilita/deshabilita controles de un pedido personalizado
 * @param {string|number} idPedido - ID del pedido personalizado
 * @param {boolean} habilitar - True para habilitar, false para deshabilitar
 */
function habilitarControlesPersonalizado(idPedido, habilitar) {
    // Cantidad producida
    const cantidadInput = document.querySelector(`input[name="cantidad_producida_real_personalizado[${idPedido}]"]`);
    if (cantidadInput) {
        cantidadInput.disabled = !habilitar;
        logAction(`Cantidad producida personalizada ${habilitar ? 'habilitada' : 'deshabilitada'}`, {
            pedido_id: idPedido,
            control: 'cantidad',
            disabled: !habilitar,
            valor: cantidadInput.value
        });
    }

    // Costo diseño
    const costoInput = document.querySelector(`input[name="costo_diseño[${idPedido}]"]`);
    if (costoInput) {
        costoInput.disabled = !habilitar;
        logAction(`Costo diseño ${habilitar ? 'habilitado' : 'deshabilitado'}`, {
            pedido_id: idPedido,
            control: 'costo',
            disabled: !habilitar,
            valor: costoInput.value
        });
    }

    // Botón Terminar
    const terminarBtn = document.getElementById(`terminar-btn-${idPedido}`);
    if (terminarBtn) {
        if (habilitar) {
            terminarBtn.style.pointerEvents = 'auto';
            terminarBtn.style.opacity = '1';
            terminarBtn.classList.remove('disabled');
        } else {
            terminarBtn.style.pointerEvents = 'none';
            terminarBtn.style.opacity = '0.65';
            terminarBtn.classList.add('disabled');
        }
        
        const terminarCheckbox = terminarBtn.querySelector('input[type="checkbox"]');
        if (terminarCheckbox) {
            terminarCheckbox.disabled = !habilitar;
            logAction(`Botón Terminar personalizado ${habilitar ? 'habilitado' : 'deshabilitado'}`, {
                pedido_id: idPedido,
                control: 'terminar',
                disabled: !habilitar,
                checked: terminarCheckbox.checked
            });
        }
    }
}

/**
 * Desmarca otros estados cuando se selecciona uno en pedido personalizado
 * @param {string|number} idPedido - ID del pedido personalizado
 * @param {string} tipoActual - Tipo de estado actual
 * @param {boolean} mantenerIniciado - Si se debe mantener el estado iniciado
 */
function desmarcarOtrosEstadosPersonalizado(idPedido, tipoActual, mantenerIniciado = false) {
    const estados = ['es_iniciado_personalizado', 'es_terminado_personalizado', 'es_cancelado_personalizado'];
    
    estados.forEach(tipo => {
        if (tipo !== tipoActual && !(mantenerIniciado && tipo === 'es_iniciado_personalizado')) {
            // Desmarcar checkbox UI
            const checkboxUI = document.querySelector(`input[name="${tipo}[${idPedido}]"]`);
            if (checkboxUI) {
                checkboxUI.checked = false;
                logAction(`Estado desmarcado - ${tipo}`, {
                    pedido_id: idPedido,
                    estado: tipo
                });
            }
            
            // Actualizar campo oculto
            const nombreCampoOculto = tipo.replace('_ui', '');
            const hiddenInput = document.querySelector(`input[type="hidden"][name="${nombreCampoOculto}[${idPedido}]"]`);
            if (hiddenInput) {
                hiddenInput.value = '0';
                logAction(`Campo oculto actualizado - ${hiddenInput.name}: 0`, {
                    pedido_id: idPedido,
                    campo: hiddenInput.name
                });
            }
        }
    });
}

/**
 * Valida un pedido personalizado antes de marcarlo como terminado
 * @param {HTMLInputElement} checkbox - Checkbox que disparó el evento
 * @param {string|number} idPedido - ID del pedido
 * @param {string|number} idReceta - ID de la receta padre
 * @returns {boolean} True si es válido, false si no
 */
function validarTerminadoPersonalizado(checkbox, idPedido, idReceta) {
    // Validar que esté iniciado primero usando el campo oculto
    const iniciadoHidden = document.querySelector(`input[type="hidden"][name="es_iniciado_personalizado[${idPedido}]"]`);
    if (!iniciadoHidden || iniciadoHidden.value !== '1') {
        alert('Debe iniciar el pedido personalizado antes de terminarlo.');
        logAction('Validación fallida - No está iniciado', {
            pedido_id: idPedido,
            receta_id: idReceta,
            valor_campo_oculto: iniciadoHidden?.value
        });
        return false;
    }
    
    // Validar cantidad producida
    const cantidadInput = document.querySelector(`input[name="cantidad_producida_real_personalizado[${idPedido}]"]`);
    if (!cantidadInput || !cantidadInput.value || parseFloat(cantidadInput.value) <= 0) {
        alert('Debe ingresar una cantidad producida válida para este pedido personalizado.');
        logAction('Validación fallida - Cantidad inválida', {
            pedido_id: idPedido,
            receta_id: idReceta,
            cantidad: cantidadInput?.value
        });
        return false;
    }
    
    // Validar costo diseño
    const costoInput = document.querySelector(`input[name="costo_diseño[${idPedido}]"]`);
    if (!costoInput || !costoInput.value || parseFloat(costoInput.value) <= 0) {
        alert('Debe ingresar un costo de diseño válido (mayor que cero) para este pedido personalizado.');
        logAction('Validación fallida - Costo diseño inválido', {
            pedido_id: idPedido,
            receta_id: idReceta,
            costo: costoInput?.value
        });
        return false;
    }
    
    logAction('Validación para terminar pedido personalizado exitosa', { 
        pedido_id: idPedido,
        receta_id: idReceta 
    });
    return true;
}

/**
 * Carga el instructivo de una receta via AJAX
 * @param {string|number} idReceta - ID de la receta
 * @param {string} estado - Estado actual de la receta
 * @param {string|number} [idPedido] - ID del pedido (opcional)
 */
function cargarInstructivo(idReceta, estado, idPedido = null) {
    const modal = $('#instructivoModal');
    
    $('#instructivoContent').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Cargando...</span>
            </div>
            <p class="mt-3">Cargando instructivo...</p>
        </div>
    `);
    
    modal.modal('show');

    // Obtener datos específicos del pedido o receta
    let cantidadPedido = 0;
    let cantidadEsperada = 0;

    if (idPedido) {
        // Si es un pedido personalizado
        const row = document.querySelector(`tr.pedido-personalizado[data-pedido-id="${idPedido}"]`);
        if (row) {
            cantidadPedido = parseFloat(row.querySelector('td:nth-child(3)').textContent.trim()) || 0;
            cantidadEsperada = parseFloat(row.querySelector('td:nth-child(5)').textContent.trim()) || 0;
        }
    } else {
        // Si es una receta normal
        const row = document.querySelector(`tr#row-${idReceta}`);
        if (row) {
            cantidadPedido = parseFloat(row.querySelector('td:nth-child(3)').textContent.trim()) || 0;
            cantidadEsperada = parseFloat(row.querySelector('td:nth-child(5)').textContent.trim()) || 0;
        }
    }

    $.ajax({
        url: "{{ route('recetas.show-instructivo') }}",
        type: 'GET',
        data: { 
            id_receta: idReceta, 
            estado: estado || 'pendiente',
            id_pedido: idPedido,
            cantidad_pedido: cantidadPedido,
            cantidad_esperada: cantidadEsperada
        },
        success: function(data) {
            $('#instructivoContent').html(data);
        },
        error: function(xhr) {
            logAction('Error al cargar instructivo', {
                receta_id: idReceta,
                pedido_id: idPedido,
                cantidad_pedido: cantidadPedido,
                cantidad_esperada: cantidadEsperada,
                error: xhr.responseText
            });
            $('#instructivoContent').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> Error al cargar el instructivo
                </div>
            `);
        }
    });
}

/**
 * Muestra detalles de pedidos personalizados en un modal
 * @param {string|number} idReceta - ID de la receta
 */
function mostrarDetallesPersonales(idReceta) {
    const recetaData = {!! json_encode($recetasAgrupadas) !!}[idReceta];
    
    if (!recetaData) {
        logAction('No se encontraron datos para la receta', { receta_id: idReceta });
        return;
    }

    // Filtrar solo pedidos personalizados
    const pedidosPersonalizados = recetaData.pedidos.filter(p => p.es_personalizado);
    
    if (pedidosPersonalizados.length === 0) {
        logAction('No hay pedidos personalizados para esta receta', { receta_id: idReceta });
        return;
    }

    let html = '<div class="row">';
    
    pedidosPersonalizados.forEach((pedido, index) => {
        const imagenUrl = pedido.foto_referencial 
            ? '{{ asset("storage") }}/' + pedido.foto_referencial.replace('pedidos/', 'pedidos/')
            : null;
        
        html += `
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Pedido #${pedido.id_pedidos_det}</h5>
                    </div>
                    <div class="card-body">
                        <h6>Descripción:</h6>
                        <p class="text-muted">${pedido.descripcion || 'Sin descripción'}</p>
                        
                        ${imagenUrl ? `
                        <div class="mt-3">
                            <h6>Imagen de referencia:</h6>
                            <img src="${imagenUrl}" class="img-fluid rounded border" alt="Imagen de referencia" style="max-height: 200px; cursor: pointer;"
                                 onclick="mostrarImagenModal('${imagenUrl}')">
                        </div>
                        ` : ''}
                    </div>
                    <div class="card-footer bg-white">
                        <small class="text-muted">Cantidad: ${pedido.cantidad}</small>
                    </div>
                </div>
            </div>
        `;
        
        // Cerrar fila cada 2 elementos
        if ((index + 1) % 2 === 0) {
            html += '</div><div class="row">';
        }
    });
    
    html += '</div>';
    document.getElementById('detallesModalContent').innerHTML = html;
}

/**
 * Muestra una imagen en un modal
 * @param {string} imageUrl - URL de la imagen a mostrar
 */
function mostrarImagenModal(imageUrl) {
    $('#modalImage').attr('src', imageUrl);
    $('#imageModal').modal('show');
}

/**
 * Actualiza los totales cuando cambia el costo de diseño
 * @param {string|number} idReceta - ID de la receta
 */
function actualizarTotales(idReceta) {
    let totalCostoDiseno = 0;
    let subtotalPersonalizados = 0;
    let totalHarinaPersonalizados = 0;
    let totalCantidadProducida = 0;
    let subtotalNoPersonalizado = 0;
    let totalHarinaNoPersonalizado = 0;
    
    // Sumar todas las cantidades producidas (no personalizadas y personalizadas)
    document.querySelectorAll(`.cantidad-no-personalizada[data-recid="${idReceta}"]`).forEach(input => {
        totalCantidadProducida += parseFloat(input.value) || 0;
    });
    document.querySelectorAll(`.cantidad-personalizada[data-recid="${idReceta}"]`).forEach(input => {
        totalCantidadProducida += parseFloat(input.value) || 0;
    });
    // Obtener subtotal no personalizado
    const row = document.querySelector(`tr#row-${idReceta}`);
    if (row) {
        const subtotalText = row.querySelector('.subtotal-receta').textContent;
        const subtotalBase = parseFloat(subtotalText.replace('S/ ', '').replace(',', '')) || 0;
        subtotalNoPersonalizado += subtotalBase;
        const harinaText = row.querySelector('td:nth-child(12)').textContent;
        const harinaBase = parseFloat(harinaText.replace('g', '').replace(',', '').trim()) || 0;
        totalHarinaNoPersonalizado += harinaBase;
    }
    // Sumar personalizados
    document.querySelectorAll(`.costo-diseno-personalizado[data-recid="${idReceta}"]`).forEach(input => {
        totalCostoDiseno += parseFloat(input.value) || 0;
        // Buscar la fila del pedido personalizado
        const row = input.closest('tr');
        // Subtotal personalizado
        const subtotalText = row.cells[7].textContent;
            const subtotal = parseFloat(subtotalText.replace('S/ ', '').replace(',', '')) || 0;
            subtotalPersonalizados += subtotal;
        // Harina personalizada
        const harinaText = row.cells[10].textContent;
        const harina = parseFloat(harinaText.replace('g', '').replace(',', '').trim()) || 0;
        totalHarinaPersonalizados += harina;
            // Actualizar total para esta fila
        const totalFila = subtotal + (parseFloat(input.value) || 0);
        row.cells[9].textContent = 'S/ ' + totalFila.toFixed(2);
    });
    // Calcular total general
    const totalGeneral = subtotalNoPersonalizado + subtotalPersonalizados + totalCostoDiseno;
    const harinaTotal = totalHarinaNoPersonalizado + totalHarinaPersonalizados;
    // Actualizar displays en la fila de totales
    const totalesRow = document.querySelector(`tr.total-receta-agrupada[data-recid="${idReceta}"]`);
    if (totalesRow) {
        totalesRow.cells[4].textContent = number_format(totalCantidadProducida, 2);
        totalesRow.cells[7].textContent = 'S/ ' + number_format(subtotalNoPersonalizado + subtotalPersonalizados, 2);
        const celdaCostoDiseno = totalesRow.querySelector(`#total-costo-diseno-${idReceta}`);
        if (celdaCostoDiseno) {
            celdaCostoDiseno.textContent = 'S/ ' + number_format(totalCostoDiseno, 2);
        } else if (totalesRow.cells[8]) {
            totalesRow.cells[8].textContent = 'S/ ' + number_format(totalCostoDiseno, 2);
        }
        totalesRow.cells[9].textContent = 'S/ ' + number_format(totalGeneral, 2);
        totalesRow.cells[10].textContent = number_format(harinaTotal, 2) + ' g';
    }
    if (totalesRow) {
        const cells = totalesRow.querySelectorAll('td');
        cells.forEach(cell => {
            cell.classList.add('highlight');
            setTimeout(() => cell.classList.remove('highlight'), 1000);
        });
    }
}

// Función auxiliar para formatear números
function number_format(number, decimals) {
    return parseFloat(number).toFixed(decimals);
}

// Validación del formulario al enviar
document.getElementById('produccionForm').addEventListener('submit', function(e) {
    // Verificar recetas normales
    const terminados = document.querySelectorAll('input[name^="es_terminado"]:checked');
    const cancelados = document.querySelectorAll('input[name^="es_cancelado"]:checked');
    
    // Verificar pedidos personalizados
    const terminadosPersonalizados = document.querySelectorAll('input[name^="es_terminado_personalizado"]:checked');
    const canceladosPersonalizados = document.querySelectorAll('input[name^="es_cancelado_personalizado"]:checked');
    
    // Verificar campos ocultos de pedidos personalizados
    const terminadosPersonalizadosHidden = document.querySelectorAll('input[type="hidden"][name^="es_terminado_personalizado"][value="1"]');
    const canceladosPersonalizadosHidden = document.querySelectorAll('input[type="hidden"][name^="es_cancelado_personalizado"][value="1"]');
    
    logAction('Validación de formulario', {
        terminados: terminados.length,
        cancelados: cancelados.length,
        terminadosPersonalizados: terminadosPersonalizados.length,
        canceladosPersonalizados: canceladosPersonalizados.length,
        terminadosPersonalizadosHidden: terminadosPersonalizadosHidden.length,
        canceladosPersonalizadosHidden: canceladosPersonalizadosHidden.length
    });
    
    if (terminados.length === 0 && cancelados.length === 0 && 
        terminadosPersonalizados.length === 0 && canceladosPersonalizados.length === 0 &&
        terminadosPersonalizadosHidden.length === 0 && canceladosPersonalizadosHidden.length === 0) {
        e.preventDefault();
        alert('Debes marcar al menos una receta o pedido personalizado como terminado o cancelado para guardar.');
        return false;
    }
    
     // Debug: mostrar todos los inputs ocultos de observación personalizados
     document.querySelectorAll('input[name^="observaciones_personalizado["]').forEach(input => {
        console.log('Input oculto:', input.name, 'Valor:', input.value);
    });

    // Verificar cancelados sin observación
    let canceladosSinObservacion = [];

    // Para pedidos personalizados
    canceladosPersonalizados.forEach(checkbox => {
        const name = checkbox.name;
        const idPedido = name.match(/\[(.*?)\]/)[1];
        // Busca el input oculto DENTRO del form
        const observacion = document.querySelector(`#produccionForm input[name="observaciones_personalizado[${idPedido}]"]`)?.value;
        if (!observacion) {
            canceladosSinObservacion.push(`Pedido personalizado ${idPedido}`);
        }
    });
    
    // Para recetas normales
    cancelados.forEach(checkbox => {
        const name = checkbox.name;
        const idReceta = name.match(/\[(.*?)\]/)[1];
        const observacion = document.querySelector(`input[name="observaciones[${idReceta}]"]`)?.value;
        
        if (!observacion) {
            canceladosSinObservacion.push(`Receta ${idReceta}`);
        }
    });
    
    // Para pedidos personalizados
canceladosPersonalizados.forEach(checkbox => {
    const name = checkbox.name;
    const idPedido = name.match(/\[(.*?)\]/)[1];
    // Busca el input oculto DENTRO del form
    const observacion = document.querySelector(`#produccionForm input[name="observaciones_personalizado[${idPedido}]"]`)?.value;
    // Busca el input oculto de cancelado
    const canceladoHidden = document.querySelector(`#produccionForm input[type="hidden"][name="es_cancelado_personalizado[${idPedido}]"]`);
    // Si el checkbox o el input oculto está marcado, debe tener observación
    if ((checkbox.checked || (canceladoHidden && canceladoHidden.value === '1')) && !observacion) {
        canceladosSinObservacion.push(`Pedido personalizado ${idPedido}`);
    }
});
    
    // Verificar también los campos ocultos de pedidos personalizados cancelados
    canceladosPersonalizadosHidden.forEach(hidden => {
        const name = hidden.name;
        const idPedido = name.match(/\[(.*?)\]/)[1];
        const observacion = document.querySelector(`input[name="observaciones_personalizado[${idPedido}]"]`)?.value;
        
        if (!observacion) {
            canceladosSinObservacion.push(`Pedido personalizado ${idPedido}`);
        }
    });

     // Debug: mostrar todas las observaciones de pedidos personalizados cancelados
     canceladosPersonalizados.forEach(checkbox => {
        const name = checkbox.name;
        const idPedido = name.match(/\[(.*?)\]/)[1];
        const observacion = document.querySelector(`input[name="observaciones_personalizado[${idPedido}]"]`)?.value;
        console.log('Pedido personalizado cancelado:', idPedido, 'Observación:', observacion);
    });
    
    // if (canceladosSinObservacion.length > 0) {
    //     e.preventDefault();
    //     alert('Los siguientes ítems cancelados necesitan una observación:\n' + 
    //           canceladosSinObservacion.join('\n'));
    //     return false;
    // }
    
    return true;
});

// Cerrar notificación de equipo
function closeNotification() {
    document.getElementById('equipoNotification').style.display = 'none';
}

// Inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    logAction('Página cargada - Inicializando seguimiento de estados');
    
    // Configuración de tooltips de Bootstrap
    if (typeof $.fn.tooltip === 'function') {
        $('[data-toggle="tooltip"]').tooltip();
    }
    
    // Delegación de eventos para mejor rendimiento
    document.getElementById('dataTable').addEventListener('change', function(e) {
        if (e.target.matches('input[name^="es_iniciado"], input[name^="es_terminado"], input[name^="es_cancelado"]')) {
            const id = e.target.name.match(/\[(.*?)\]/)[1];
            if (e.target.name.startsWith('es_iniciado_personalizado')) {
                manejarEstadoPersonalizado(e.target, id, e.target.closest('tr').dataset.recid);
            } else if (e.target.name.startsWith('es_terminado_personalizado')) {
                manejarEstadoPersonalizado(e.target, id, e.target.closest('tr').dataset.recid);
            }
        }
    });
    
    // Mostrar imágenes al hacer clic
    $(document).on('click', '.view-image-btn', function() {
        const imageUrl = $(this).data('image-url');
        $('#modalImage').attr('src', imageUrl);
        $('#imageModal').modal('show');
    });
    
                // Mostrar estado inicial de todas las recetas
    document.querySelectorAll('tr.production-item').forEach(row => {
        const idReceta = row.id.split('-')[1];
        if (idReceta) {
            mostrarEstadoActual(idReceta);
        }
    });

    // Mostrar estado completo cada 5 segundos (solo en desarrollo)
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        setInterval(() => {
            logAction('Estado actual del sistema (actualización periódica)');
            document.querySelectorAll('tr.production-item').forEach(row => {
                const idReceta = row.id.split('-')[1];
                if (idReceta) mostrarEstadoActual(idReceta);
            });
        }, 5000);
    }
});

/**
 * Muestra el modal para agregar observación a una receta
 * @param {number|null} idPedido - ID del pedido (null para receta normal)
 * @param {number} idReceta - ID de la receta
 * @param {boolean} esCancelacion - Si es una cancelación
 */
function mostrarModalObservacion(idPedido, idReceta, esCancelacion = false) {
    $('#observacionModalLabel').text(idPedido ? 'Observación para Pedido Personalizado' : 'Observación para Receta');
    $('#observacionTexto').attr('placeholder', esCancelacion ? 
        'Ingrese el motivo de la cancelación...' : 
        'Ingrese observación para esta receta...');
    $('#observacionRecetaId').val(idReceta);
    $('#esCancelacion').val(esCancelacion ? '1' : '0');
    
    // Guardar referencia al pedido si existe
    if (idPedido) {
        $('#observacionModal').data('pedido-id', idPedido);
    } else {
        $('#observacionModal').removeData('pedido-id');
    }
    
    // Cargar observación existente si hay
    const inputName = idPedido 
        ? `observaciones_personalizado[${idPedido}]` 
        : `observaciones[${idReceta}]`;
    
    const observacionExistente = document.querySelector(`input[name="${inputName}"]`)?.value || '';
    $('#observacionTexto').val(observacionExistente);
    
    $('#observacionModal').modal('show');
}

/**
 * Guarda la observación ingresada
 */
function guardarObservacion() {
    const idReceta = $('#observacionRecetaId').val();
    const esCancelacion = $('#esCancelacion').val() === '1';
    const observacion = $('#observacionTexto').val().trim();
    const pedidoId = $('#observacionModal').data('pedido-id');
    
    if (!observacion) {
        alert('Debe ingresar una observación');
        return;
    }
    
    // Crear o actualizar el campo oculto para la observación
    const inputName = pedidoId 
        ? `observaciones_personalizado[${pedidoId}]` 
        : `observaciones[${idReceta}]`;
    
    let inputObservacion = document.querySelector(`input[name="${inputName}"]`);
    
    if (!inputObservacion) {
        inputObservacion = document.createElement('input');
        inputObservacion.type = 'hidden';
        inputObservacion.name = inputName;
        document.getElementById('produccionForm').appendChild(inputObservacion);
    }
    
    inputObservacion.value = observacion;

    // Mostrar mensaje visual y log en consola
    $('#estadoObservacionGuardada').show().delay(1500).fadeOut();
    console.log('Observación guardada:', inputName, '=>', observacion);

    // Si es cancelación, asegurarse que el checkbox de cancelado esté marcado y el campo oculto también
    if (esCancelacion) {
        const checkboxName = pedidoId 
            ? `es_cancelado_personalizado[${pedidoId}]` 
            : `es_cancelado[${idReceta}]`;
        
        // Marcar el checkbox de cancelado
        const checkbox = document.querySelector(`input[name="${checkboxName}"]`);
        if (checkbox) {
            checkbox.checked = true;
        }

        // Forzar el campo oculto de cancelado a 1
        let hiddenInput = document.querySelector(`input[type="hidden"][name="${checkboxName}"]`);
        if (!hiddenInput) {
            hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = checkboxName;
            document.getElementById('produccionForm').appendChild(hiddenInput);
        }
        hiddenInput.value = '1';
    }
    
    $('#observacionModal').modal('hide');
}

// Cierra la notificación de equipo
function closeNotification() {
    document.getElementById('equipoNotification').style.display = 'none';
    logAction('Notificación de equipo cerrada por el usuario');
}

// Exportar funciones para acceso global (solo en desarrollo)
if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
    window.productionDebug = {
        mostrarEstadoActual,
        habilitarControlesReceta,
        validarTerminado,
        logAction
    };
}

// Agregar event listener para monitorear cambios en los checkboxes
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== INICIALIZANDO MONITOREO DE CHECKBOXES ===');
    // Monitorear cambios en checkboxes de pedidos personalizados
    const checkboxes = document.querySelectorAll('input[name^="es_iniciado_personalizado"], input[name^="es_terminado_personalizado"], input[name^="es_cancelado_personalizado"]');
    console.log('Checkboxes encontrados:', checkboxes.length);
    
    checkboxes.forEach(checkbox => {
        console.log('Agregando listener a checkbox:', checkbox.name);
        checkbox.addEventListener('change', function() {
            console.log('Checkbox cambiado:', this.name);
            const idPedido = this.name.match(/\[(.*?)\]/)[1];
            const idReceta = this.closest('tr').dataset.recid;
            console.log('ID Pedido:', idPedido);
            console.log('ID Receta:', idReceta);
            manejarEstadoPersonalizado(this, idPedido, idReceta);
        });
    });

    document.querySelectorAll('tr.pedido-personalizado').forEach(row => {
    const idPedido = row.querySelector('input[name^="es_cancelado_personalizado"]')?.name.match(/\[(.*?)\]/)[1];
    if (idPedido) mostrarEstadoActualPersonalizado(idPedido);
});
    console.log('=== FIN DE INICIALIZACIÓN DE MONITOREO ===');

});

// ... existing code ...
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sincronizar campos de cantidad producida
    const cantidadInputs = document.querySelectorAll('input[name^="cantidad_producida_real["]');
    cantidadInputs.forEach(input => {
        input.addEventListener('input', function() {
            const recetaId = this.name.match(/\[(\d+)\]/)[1];
            const hiddenInput = document.querySelector(`input[name="cantidad_producida_real_hidden[${recetaId}]"]`);
            if (hiddenInput) {
                hiddenInput.value = this.value;
            }
        });
    });

    // Sincronizar campos de cantidad producida personalizada
    const cantidadPersonalizadaInputs = document.querySelectorAll('input[name^="cantidad_producida_real_personalizado["]');
    cantidadPersonalizadaInputs.forEach(input => {
        input.addEventListener('input', function() {
            const pedidoId = this.name.match(/\[(\d+)\]/)[1];
            const hiddenInput = document.querySelector(`input[name="cantidad_producida_real_personalizado_hidden[${pedidoId}]"]`);
            if (hiddenInput) {
                hiddenInput.value = this.value;
            }
        });
    });
});
</script>
@endpush
// ... existing code ...
</script>

<script>
function toggleDetalles(idReceta) {
    var detallesRow = document.getElementById('detalles-' + idReceta);
    var btn = document.querySelector('#row-' + idReceta + ' button[onclick^="toggleDetalles"] i');
    if (detallesRow.style.display === 'none' || detallesRow.style.display === '') {
        detallesRow.style.display = 'table-row';
        if(btn) { btn.classList.remove('fa-chevron-down'); btn.classList.add('fa-chevron-up'); }
    } else {
        detallesRow.style.display = 'none';
        if(btn) { btn.classList.remove('fa-chevron-up'); btn.classList.add('fa-chevron-down'); }
    }
}
</script>

@endsection