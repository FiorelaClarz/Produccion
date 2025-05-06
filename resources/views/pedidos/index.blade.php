@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-between mb-4">
        <div class="col-md-6">
            <h2>Listado de Pedidos</h2>
            <p class="text-muted">
                @switch($filter)
                    @case('today')
                        Mostrando pedidos actualizados hoy
                        @break
                    @case('yesterday')
                        Mostrando pedidos actualizados ayer
                        @break
                    @case('week')
                        Mostrando pedidos actualizados en la última semana
                        @break
                    @case('custom')
                        Mostrando pedidos actualizados en la fecha seleccionada
                        @break
                @endswitch
            </p>
            
            @if($horaLimiteActual)
                <div class="alert alert-info py-2 mb-3">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                        <div>
                            <strong>Turno Actual:</strong> 
                            @if($horaLimiteActual->hora_limite >= '07:00:00' && $horaLimiteActual->hora_limite <= '12:00:00')
    Mañana ({{ substr($horaLimiteActual->hora_limite, 0, 5) }})
@elseif($horaLimiteActual->hora_limite >= '12:01:00' && $horaLimiteActual->hora_limite <= '18:59:00')
    Tarde ({{ substr($horaLimiteActual->hora_limite, 0, 5) }})
@else
    Noche ({{ substr($horaLimiteActual->hora_limite, 0, 5) }})
@endif
                            <br>
                            <strong>Ventana de Pedidos:</strong> 
                            {{ Carbon\Carbon::parse($horaLimiteActual->hora_limite)->subHour()->format('H:i') }} - {{ $horaLimiteActual->hora_limite }}
                            <br>
                            <strong>Estado Actual:</strong> 
                            @if($dentroDeHoraPermitida)
                                <span class="text-success">Puede crear/editar pedidos</span>
                            @else
                                <span class="text-danger">No puede crear/editar pedidos</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
        
        <div class="col-md-6 text-end">
            <div class="d-flex justify-content-end gap-2">
                @if($filter == 'today')
                <a href="{{ route('pedidos.consolidado.pdf') }}" 
                   class="btn btn-success" 
                   target="_blank"
                   data-bs-toggle="tooltip" 
                   title="Generar PDF consolidado de todos los pedidos de hoy">
                    <i class="fas fa-file-pdf"></i> Consolidado
                </a>
                @endif
                
                <a href="{{ route('pedidos.create') }}" 
                   class="btn btn-primary {{ !$dentroDeHoraPermitida ? 'disabled' : '' }}" 
                   @if(!$dentroDeHoraPermitida) 
                       data-bs-toggle="tooltip" 
                       title="Los pedidos solo se pueden crear entre {{ $horaInicioPedidos->format('H:i') }} y {{ $horaFinPedidos->format('H:i') }}" 
                   @else
                       data-bs-toggle="tooltip" 
                       title="Crear nuevo pedido"
                   @endif>
                    <i class="fas fa-plus"></i> Nuevo Pedido
                    @if(!$dentroDeHoraPermitida)
                        <span class="badge bg-danger ms-2">Fuera de horario</span>
                    @endif
                </a>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="btn-group btn-group-sm" role="group">
                        <a href="{{ route('pedidos.index', ['filter' => 'today']) }}" 
                           class="btn btn-outline-primary {{ $filter == 'today' ? 'active' : '' }}"
                           data-bs-toggle="tooltip"
                           title="Mostrar pedidos de hoy">
                            Hoy
                        </a>
                        <a href="{{ route('pedidos.index', ['filter' => 'yesterday']) }}" 
                           class="btn btn-outline-primary {{ $filter == 'yesterday' ? 'active' : '' }}"
                           data-bs-toggle="tooltip"
                           title="Mostrar pedidos de ayer">
                            Ayer
                        </a>
                        <a href="{{ route('pedidos.index', ['filter' => 'week']) }}" 
                           class="btn btn-outline-primary {{ $filter == 'week' ? 'active' : '' }}"
                           data-bs-toggle="tooltip"
                           title="Mostrar pedidos de la última semana">
                            Última Semana
                        </a>
                    </div>
                </div>
                <div class="col-md-4 mt-2 mt-md-0">
                    <form action="{{ route('pedidos.index') }}" method="GET" class="d-flex">
                        <input type="hidden" name="filter" value="custom">
                        <input type="date" class="form-control form-control-sm me-2" name="custom_date" 
                               value="{{ request()->custom_date ?? date('Y-m-d') }}"
                               max="{{ date('Y-m-d') }}">
                        <button class="btn btn-primary btn-sm" type="submit"
                                data-bs-toggle="tooltip"
                                title="Buscar por fecha específica">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover table-sm">
            <thead class="table-dark">
                <tr>
                    <th width="5%">ID</th>
                    <th width="15%">Documento</th>
                    <th width="15%">Usuario</th>
                    <th width="15%">Tienda</th>
                    <th width="10%">Fecha/Hora</th>
                    <th width="10%">Hora Límite</th>
                    <th width="15%">Detalles</th>
                    <th width="15%">Acciones</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($pedidos as $pedido)
                @php
                    $fechaActualizacion = Carbon\Carbon::parse($pedido->fecha_last_update);
                    $horaActualizacion = Carbon\Carbon::parse($pedido->hora_last_update);
                    $horaLimitePedido = Carbon\Carbon::parse($pedido->hora_limite);
                    $horaInicioEdicion = $horaLimitePedido->copy()->subHour();
                    
                    // Verificar si estamos dentro del período permitido (1 hora antes de la hora límite)
                    $puedeEditarEliminar = now()->between($horaInicioEdicion, $horaLimitePedido);
                    
                    // Verificar si el pedido fue creado en el período actual
                    $pedidoCreadoEnPeriodoActual = $pedido->esta_dentro_de_hora && 
                                                  $pedido->hora_limite == $horaLimiteActual->hora_limite;
                @endphp
                <tr>
                    <td class="align-middle">{{ $pedido->id_pedidos_cab }}</td>
                    <td class="align-middle">
                        <span class="badge bg-primary">{{ $pedido->doc_interno }}</span>
                    </td>
                    <td class="align-middle">{{ $pedido->usuario->nombre_personal }}</td>
                    <td class="align-middle">{{ $pedido->tienda->nombre }}</td>
                    <td class="align-middle">
                        <small>
                            <div class="text-nowrap">{{ $fechaActualizacion->format('d/m/Y') }}</div>
                            <div class="text-nowrap">{{ $horaActualizacion->format('H:i') }}</div>
                        </small>
                    </td>
                    <td class="align-middle">
                        <small>
                            <div class="text-nowrap">
                                @if($pedido->hora_limite >= '07:00:00' && $pedido->hora_limite <= '12:00:00')
                                <div class="text-nowrap">{{  $pedido->hora_limite }}</div>
                                @elseif($pedido->hora_limite  >= '12:01:00' && $pedido->hora_limite <= '18:59:00')
                                <div class="text-nowrap">{{    $pedido->hora_limite }}</div>
                                @else
                                <div class="text-nowrap">{{    $pedido->hora_limite }}</div>
                                @endif
                            </div>
                            @if (!$pedido->esta_dentro_de_hora)
                                <span class="badge bg-danger">Fuera de horario</span>
                            @endif
                        </small>
                    </td>
                    <td class="align-middle">
                        <div class="accordion accordion-custom" id="accordion-{{ $pedido->id_pedidos_cab }}">
                            <div class="accordion-item border-0 bg-transparent">
                                <h2 class="accordion-header" id="heading-{{ $pedido->id_pedidos_cab }}">
                                    <button class="accordion-button collapsed py-1 px-2 shadow-none" 
                                            type="button" 
                                            data-bs-toggle="collapse" 
                                            data-bs-target="#collapse-{{ $pedido->id_pedidos_cab }}" 
                                            aria-expanded="false" 
                                            aria-controls="collapse-{{ $pedido->id_pedidos_cab }}">
                                        <small>Ver detalles ({{ $pedido->pedidosDetalle->count() }})</small>
                                    </button>
                                </h2>
                                <div id="collapse-{{ $pedido->id_pedidos_cab }}" 
                                     class="accordion-collapse collapse" 
                                     aria-labelledby="heading-{{ $pedido->id_pedidos_cab }}" 
                                     data-bs-parent="#accordion-{{ $pedido->id_pedidos_cab }}">
                                    <div class="accordion-body p-0">
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="20%">Área</th>
                                                    <th width="30%">Producto/Receta</th>
                                                    <th width="10%">Cantidad</th>
                                                    <th width="15%">Unidad</th>
                                                    <th width="25%">Estado</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($pedido->pedidosDetalle as $detalle)
                                                <tr>
                                                    <td><small>{{ $detalle->area->nombre ?? 'N/A' }}</small></td>
                                                    <td>
                                                        <small>
                                                            @if($detalle->receta)
                                                                {{ $detalle->receta->nombre }}
                                                            @else
                                                                {{ $detalle->descripcion ?? 'Personalizado' }}
                                                            @endif
                                                        </small>
                                                    </td>
                                                    <td class="text-end"><small>{{ $detalle->cantidad }}</small></td>
                                                    <td><small>{{ $detalle->uMedida->nombre ?? 'N/A' }}</small></td>
                                                    <td>
                                                        <span class="badge bg-{{ $detalle->estado->color ?? 'secondary' }}">
                                                            <small>{{ $detalle->estado->nombre ?? 'N/A' }}</small>
                                                        </span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="align-middle">
                        <div class="d-flex flex-column flex-sm-row gap-1">
                            <a href="{{ route('pedidos.show', $pedido->id_pedidos_cab) }}" 
                               class="btn btn-sm btn-info" 
                               data-bs-toggle="tooltip"
                               title="Ver detalles completos">
                                <i class="fas fa-eye"></i>
                            </a>
                            
                            <a href="{{ route('pedidos.pdf', $pedido->id_pedidos_cab) }}" 
                               class="btn btn-sm btn-secondary" 
                               data-bs-toggle="tooltip"
                               title="Generar PDF individual"
                               target="_blank">
                                <i class="fas fa-file-pdf"></i>
                            </a>

                            @if($puedeEditarEliminar && $pedidoCreadoEnPeriodoActual)
                                <a href="{{ route('pedidos.edit', $pedido->id_pedidos_cab) }}" 
                                   class="btn btn-sm btn-warning" 
                                   data-bs-toggle="tooltip"
                                   title="Editar pedido (Permitido hasta {{ $horaLimitePedido->format('H:i') }})">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <form action="{{ route('pedidos.destroy', $pedido->id_pedidos_cab) }}" 
                                      method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="btn btn-sm btn-danger" 
                                            data-bs-toggle="tooltip"
                                            title="Eliminar pedido (Permitido hasta {{ $horaLimitePedido->format('H:i') }})"
                                            onclick="return confirm('¿Estás seguro de eliminar este pedido?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @else
                                <button class="btn btn-sm btn-outline-secondary" 
                                        disabled 
                                        data-bs-toggle="tooltip"
                                        title="Edición permitida desde {{ $horaInicioEdicion->format('H:i') }} hasta {{ $horaLimitePedido->format('H:i') }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" 
                                        disabled 
                                        data-bs-toggle="tooltip"
                                        title="Eliminación permitida desde {{ $horaInicioEdicion->format('H:i') }} hasta {{ $horaLimitePedido->format('H:i') }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No se encontraron pedidos</p>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    

    <div class="d-flex justify-content-center mt-3">
        <nav aria-label="Page navigation">
            <ul class="pagination pagination-sm">
                {{ $pedidos->appends(request()->query())->onEachSide(1)->links('pagination::bootstrap-5') }}
            </ul>
        </nav>
    </div>
</div>
@endsection

@section('styles')
<style>
    /* Estilos personalizados para el acordeón */
    .accordion-custom .accordion-button {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        background-color: transparent;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
    }
    
    .accordion-custom .accordion-button:not(.collapsed) {
        background-color: #f8f9fa;
        color: #0d6efd;
        box-shadow: none;
    }
    
    .accordion-custom .accordion-button::after {
        background-size: 0.8rem;
        width: 0.8rem;
        height: 0.8rem;
    }
    
    .accordion-custom .accordion-body {
        padding: 0;
    }
    
    /* Estilos para la paginación */
    .pagination .page-item .page-link {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    
    .pagination .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
    
    /* Mejoras generales */
    .table-sm th, .table-sm td {
        padding: 0.3rem;
    }
    
    .badge {
        font-size: 0.75em;
    }
    
    /* Espaciado entre botones */
    .gap-1 {
        gap: 0.25rem;
    }
    .gap-2 {
        gap: 0.5rem;
    }
    
    /* Mejora en la visualización de la tabla */
    .table-responsive {
        border-radius: 0.25rem;
        border: 1px solid #dee2e6;
    }
    
    /* Estilo para filas hover */
    .table-hover tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.05);
    }
    
    /* Estilo para el mensaje de no resultados */
    .text-muted i {
        opacity: 0.5;
    }

    /* Estilos para botones habilitados/deshabilitados */
    .btn-action-enabled {
        opacity: 1;
        transition: all 0.3s ease;
    }
    
    .btn-action-disabled {
        opacity: 0.65;
        cursor: not-allowed;
    }
    
    .btn-action-disabled:hover {
        opacity: 0.65;
    }
    
    /* Estilo para tooltips */
    .tooltip-inner {
        max-width: 300px;
        padding: 0.5rem 1rem;
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Actualizar automáticamente la página cada 5 minutos
        setInterval(() => {
            window.location.reload();
        }, 300000);
        
        // Mostrar alerta si estamos cerca del límite de tiempo
        @if(isset($dentroDeHoraPermitida) && isset($horaLimiteActual))
            const horaLimiteActual = @json($horaLimiteActual->hora_limite);
            
            if(@json($dentroDeHoraPermitida) && horaLimiteActual){
                const horaFin = new Date('{{ today()->format("Y-m-d") }}T' + horaLimiteActual);
                const diferencia = horaFin - new Date();
                
                if (diferencia < (15 * 60 * 1000)) {
                    const minutosRestantes = Math.floor(diferencia / (1000 * 60));
                    
                    Swal.fire({
                        title: '¡Atención!',
                        html: `Quedan <strong>${minutosRestantes} minutos</strong> para realizar pedidos en este turno.<br><br>
                              <i class="fas fa-clock"></i> Hora límite: ${horaLimiteActual}`,
                        icon: 'warning',
                        confirmButtonText: 'Entendido',
                        timer: 10000,
                        timerProgressBar: true
                    });
                }
            }
        @endif
    });
</script>
@endsection