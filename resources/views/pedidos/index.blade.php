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
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('pedidos.create') }}" 
               class="btn btn-primary {{ !$dentroDeHoraPermitida ? 'disabled' : '' }}" 
               @if(!$dentroDeHoraPermitida) 
                   title="El tiempo para realizar pedidos ha terminado" 
               @endif>
                <i class="fas fa-plus"></i> Nuevo Pedido
                @if(!$dentroDeHoraPermitida)
                    <span class="badge bg-danger ms-2">Tiempo agotado</span>
                @endif
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
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
                           class="btn btn-outline-primary {{ $filter == 'today' ? 'active' : '' }}">
                            Hoy
                        </a>
                        <a href="{{ route('pedidos.index', ['filter' => 'yesterday']) }}" 
                           class="btn btn-outline-primary {{ $filter == 'yesterday' ? 'active' : '' }}">
                            Ayer
                        </a>
                        <a href="{{ route('pedidos.index', ['filter' => 'week']) }}" 
                           class="btn btn-outline-primary {{ $filter == 'week' ? 'active' : '' }}">
                            Última Semana
                        </a>
                    </div>
                </div>
                <div class="col-md-4 mt-2 mt-md-0">
                    <form action="{{ route('pedidos.index') }}" method="GET" class="d-flex">
                        <input type="hidden" name="filter" value="custom">
                        <input type="date" class="form-control form-control-sm me-2" name="custom_date" 
                               value="{{ request()->custom_date ?? date('Y-m-d') }}">
                        <button class="btn btn-primary btn-sm" type="submit">
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
                    <th>ID</th>
                    <th>Documento</th>
                    <th>Usuario</th>
                    <th>Tienda</th>
                    <th>Fecha/Hora</th>
                    <th>Hora Límite</th>
                    <th>Detalles</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pedidos as $pedido)
                @php
                    $fechaActualizacion = Carbon\Carbon::parse($pedido->fecha_last_update);
                    $horaActualizacion = Carbon\Carbon::parse($pedido->hora_last_update);
                @endphp
                
                <tr>
                    <td class="align-middle">{{ $pedido->id_pedidos_cab }}</td>
                    <td class="align-middle">{{ $pedido->doc_interno }}</td>
                    <td class="align-middle">{{ $pedido->usuario->nombre_personal }}</td>
                    <td class="align-middle">{{ $pedido->tienda->nombre }}</td>
                    <td class="align-middle">
                        <small>
                            {{ $fechaActualizacion->format('d/m/Y') }}<br>
                            {{ $horaActualizacion->format('H:i:s') }}
                        </small>
                    </td>
                    <td class="align-middle">
                        <small>
                            {{ $pedido->hora_limite }}
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
                                                    <td><small>{{ $detalle->cantidad }}</small></td>
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
                               title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </a>
                            
                            @if($pedido->esta_dentro_de_hora)
                                <a href="{{ route('pedidos.edit', $pedido->id_pedidos_cab) }}" 
                                   class="btn btn-sm btn-warning" 
                                   title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <form action="{{ route('pedidos.destroy', $pedido->id_pedidos_cab) }}" 
                                      method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="btn btn-sm btn-danger" 
                                            title="Eliminar" 
                                            onclick="return confirm('¿Estás seguro de eliminar este pedido?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @else
                                <button class="btn btn-sm btn-secondary" 
                                        disabled 
                                        title="Edición no permitida">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-secondary" 
                                        disabled 
                                        title="Eliminación no permitida">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4">No se encontraron pedidos</td>
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
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Actualizar automáticamente la página cada 5 minutos
        setInterval(() => {
            window.location.reload();
        }, 300000);
    });
</script>
@endsection