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
                        @if(request()->has('start_date') && request()->has('end_date'))
                            Mostrando pedidos desde {{ date('d/m/Y', strtotime(request()->start_date)) }} hasta {{ date('d/m/Y', strtotime(request()->end_date)) }}
                        @else
                            Mostrando pedidos actualizados en la última semana
                        @endif
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
                
                <a href="#" 
                   id="btn-nuevo-pedido"
                   class="btn btn-primary {{ !$dentroDeHoraPermitida ? 'disabled' : '' }}" 
                   @if(!$dentroDeHoraPermitida) 
                       data-bs-toggle="tooltip" 
                       title="Los pedidos solo se pueden crear entre {{ $horaInicioPedidos->format('H:i') }} y {{ $horaFinPedidos->format('H:i') }}" 
                   @else
                       data-bs-toggle="tooltip" 
                       title="Crear nuevo pedido (borrará cualquier borrador guardado)"
                   @endif>
                    <i class="fas fa-plus"></i> Nuevo Pedido
                    @if(!$dentroDeHoraPermitida)
                        <span class="badge bg-danger ms-2">Fuera de horario</span>
                    @endif
                </a>
                
                <!-- Botón Continuar Pedido - solo visible si hay datos guardados temporalmente -->
                <a href="{{ route('pedidos.create') }}?mode=continue" 
                   id="btn-continuar-pedido"
                   class="btn btn-success {{ !$dentroDeHoraPermitida ? 'disabled' : '' }}" 
                   style="display: none;"
                   @if(!$dentroDeHoraPermitida) 
                       data-bs-toggle="tooltip" 
                       title="Los pedidos solo se pueden continuar entre {{ $horaInicioPedidos->format('H:i') }} y {{ $horaFinPedidos->format('H:i') }}" 
                   @else
                       data-bs-toggle="tooltip" 
                       title="Continuar con el pedido guardado temporalmente"
                   @endif>
                    <i class="fas fa-clipboard-list"></i> Continuar Pedido
                    <span class="badge bg-warning text-dark ms-2">Borrador</span>
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
                           title="Filtrar pedidos por rango de fechas personalizado">
                            Rango de Fechas
                        </a>
                    </div>
                </div>
                <div class="col-md-4 mt-2 mt-md-0">
                    @if($filter == 'week')
                    <form action="{{ route('pedidos.index') }}" method="GET" class="d-flex">
                        <input type="hidden" name="filter" value="week">
                        <div class="d-flex flex-column flex-md-row gap-2">
                            <div class="d-flex align-items-center">
                                <input type="date" class="form-control form-control-sm me-2" name="start_date" 
                                       value="{{ request()->start_date ?? date('Y-m-d', strtotime('-7 days')) }}"
                                       max="{{ date('Y-m-d') }}" placeholder="Fecha inicio">
                                <span class="me-2">a</span>
                                <input type="date" class="form-control form-control-sm me-2" name="end_date" 
                                       value="{{ request()->end_date ?? date('Y-m-d') }}"
                                       max="{{ date('Y-m-d') }}" placeholder="Fecha fin">
                            </div>
                            <button class="btn btn-primary btn-sm" type="submit"
                                    data-bs-toggle="tooltip"
                                    title="Buscar por rango de fechas">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                    @else
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
                    @endif
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
                    <th width="15%">Estado</th>
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
                        <button class="btn btn-sm btn-outline-primary detail-toggle" 
                                type="button" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapse-{{ $pedido->id_pedidos_cab }}" 
                                aria-expanded="false" 
                                aria-controls="collapse-{{ $pedido->id_pedidos_cab }}">
                            <small><i class="fas fa-chevron-down me-1"></i> Ver detalles ({{ $pedido->pedidosDetalle->count() }})</small>
                        </button>
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
                <!-- Fila expandible para detalles del pedido -->
                <tr class="detail-row">
                    <td colspan="8" class="p-0">
                        <div id="collapse-{{ $pedido->id_pedidos_cab }}" 
                            class="collapse border-top border-bottom border-light" 
                            aria-labelledby="heading-{{ $pedido->id_pedidos_cab }}">
                            <div class="p-3 bg-light">
                                <h6 class="mb-3 text-primary"><i class="fas fa-clipboard-list me-2"></i>Detalles del Pedido #{{ $pedido->id_pedidos_cab }}</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover mb-0">
                                        <thead class="table-secondary">
                                            <tr>
                                                <th width="15%">Área</th>
                                                <th width="35%">Producto/Receta</th>
                                                <th width="10%">Cantidad</th>
                                                <th width="15%">Unidad</th>
                                                <th width="25%">Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pedido->pedidosDetalle as $detalle)
                                            <tr>
                                                <td>{{ $detalle->area->nombre ?? 'N/A' }}</td>
                                                <td>
                                                    @if($detalle->receta)
                                                        {{ $detalle->receta->nombre }}
                                                    @else
                                                        {{ $detalle->descripcion ?? 'Personalizado' }}
                                                    @endif
                                                </td>
                                                <td class="text-end">{{ $detalle->cantidad }}</td>
                                                <td>{{ $detalle->uMedida->nombre ?? 'N/A' }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $detalle->estado->color ?? 'secondary' }} px-3 py-2">
                                                        {{ $detalle->estado->nombre ?? 'N/A' }}
                                                    </span>
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
    /* Estilos para los detalles de pedido */
    .detail-toggle {
        transition: all 0.2s ease;
    }
    
    .detail-toggle[aria-expanded="true"] {
        background-color: #033988;
        color: white;
    }
    
    .detail-toggle[aria-expanded="true"] i {
        transform: rotate(180deg);
        transition: transform 0.2s;
    }
    
    .detail-row {
        background-color: transparent !important;
    }
    
    .detail-row td {
        border-top: none;
    }
    
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
        // Verificar si venimos de una redirección de limpieza
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('clean') === '1') {
            console.log('Detectada página de limpieza de localStorage');
            // Limpiar localStorage con múltiples métodos para garantizar limpieza
            try {
                // Primer método: clear completo
                localStorage.clear();
                
                // Segundo método: eliminar los items específicos
                localStorage.removeItem('pedido_temp_data');
                localStorage.removeItem('pedido_temp_count');
                
                // Tercer método: sobrescribir con datos vacíos
                localStorage.setItem('pedido_temp_data', '');
                localStorage.setItem('pedido_temp_count', '0');
                localStorage.removeItem('pedido_temp_data');
                localStorage.removeItem('pedido_temp_count');
                
                console.log('localStorage limpiado completamente');
                
                // Limpiar la URL para evitar problemas si se recarga la página
                const cleanUrl = window.location.pathname;
                window.history.replaceState({}, document.title, cleanUrl);
            } catch (e) {
                console.error('Error al limpiar localStorage:', e);
            }
        }
        
        // Inicializar tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Verificar si hay un pedido guardado temporalmente
        const verificarPedidoTemporal = function() {
            // Limpiar cualquier localStorage inválido o vacío
            const limpiarStorage = () => {
                console.log('Limpiando localStorage inválido');
                localStorage.removeItem('pedido_temp_data');
                localStorage.removeItem('pedido_temp_count');
            };
            
            // Obtener datos guardados
            const pedidoTempData = localStorage.getItem('pedido_temp_data');
            if (!pedidoTempData) return;
            
            try {
                // Verificar si hay al menos un pedido en la lista
                const formData = JSON.parse(pedidoTempData);
                
                // Validar la estructura de los datos
                if (!formData || !formData.pedidos || !Array.isArray(formData.pedidos)) {
                    limpiarStorage();
                    return;
                }
                
                // Verificar que haya al menos un pedido en la lista
                if (formData.pedidos.length === 0) {
                    limpiarStorage();
                    return;
                }
                
                // Verificar que los pedidos tengan la estructura correcta
                const pedidoValido = formData.pedidos.some(p => {
                    return p && p.id_area && p.cantidad && p.id_u_medida;
                });
                
                if (!pedidoValido) {
                    limpiarStorage();
                    return;
                }
                
                // Si llegamos aquí, hay pedidos válidos guardados
                console.log(`Pedido temporal válido encontrado con ${formData.pedidos.length} items`);
                document.getElementById('btn-continuar-pedido').style.display = 'inline-block';
            } catch (e) {
                console.error('Error al verificar pedido temporal:', e);
                limpiarStorage();
            }
        };
        
        // Ejecutar verificación al cargar la página
        verificarPedidoTemporal();
        
        // Configurar el botón de "Nuevo Pedido" para que borre el localStorage
        document.getElementById('btn-nuevo-pedido').addEventListener('click', function(e) {
            if (@json($dentroDeHoraPermitida)) {
                // Solo si está dentro del horario permitido
                e.preventDefault();
                
                // Verificar si hay datos guardados
                const hayDatosGuardados = localStorage.getItem('pedido_temp_data');
                
                if (hayDatosGuardados) {
                    // Preguntar si desea borrar el borrador existente
                    Swal.fire({
                        title: '¿Crear nuevo pedido?',
                        text: 'Tienes un borrador guardado. Si continúas, se perderá ese borrador.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, crear nuevo',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Eliminar datos guardados
                            localStorage.removeItem('pedido_temp_data');
                            localStorage.removeItem('pedido_temp_count');
                            
                            // Redireccionar a crear nuevo pedido
                            window.location.href = '{{ route("pedidos.create") }}?mode=new';
                        }
                    });
                } else {
                    // No hay datos guardados, simplemente redireccionar
                    window.location.href = '{{ route("pedidos.create") }}?mode=new';
                }
            }
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

