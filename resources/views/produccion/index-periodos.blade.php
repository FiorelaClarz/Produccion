@extends('layouts.app')

@section('content')
<div class="container-fluid">
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

    <!-- Filtros de fecha -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-filter mr-2"></i>Filtros de Búsqueda
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('produccion.periodos') }}" method="GET" class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="fecha_inicio">Fecha Inicio</label>
                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                               value="{{ request('fecha_inicio', $fechaInicio->format('Y-m-d')) }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="fecha_fin">Fecha Fin</label>
                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" 
                               value="{{ request('fecha_fin', $fechaFin->format('Y-m-d')) }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <select class="form-control" id="estado" name="estado">
                            <option value="todos" {{ request('estado') == 'todos' ? 'selected' : '' }}>Todos</option>
                            <option value="pendientes" {{ request('estado') == 'pendientes' ? 'selected' : '' }}>Pendientes</option>
                            <option value="terminados" {{ request('estado') == 'terminados' ? 'selected' : '' }}>Terminados</option>
                            <option value="cancelados" {{ request('estado') == 'cancelados' ? 'selected' : '' }}>Cancelados</option>
                        </select>
                    </div>
                </div>
                <div class="col-12 text-right">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search mr-2"></i>Buscar
                    </button>
                    <a href="{{ route('produccion.periodos') }}" class="btn btn-secondary">
                        <i class="fas fa-redo mr-2"></i>Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Resumen de producción -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Producciones</h5>
                    <h2 class="mb-0">{{ $totalProducciones }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Terminadas</h5>
                    <h2 class="mb-0">{{ $totalTerminadas }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Pendientes</h5>
                    <h2 class="mb-0">{{ $totalPendientes }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title">Canceladas</h5>
                    <h2 class="mb-0">{{ $totalCanceladas }}</h2>
                </div>
            </div>
        </div>
    </div>


    <!-- Tabla de producción -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-clipboard-list mr-2"></i>Detalle de Producciones
                </h6>
                <div>
                    <button class="btn btn-sm btn-success" onclick="exportarExcel()">
                        <i class="fas fa-file-excel mr-2"></i>Exportar Excel
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="exportarPDF()">
                        <i class="fas fa-file-pdf mr-2"></i>Exportar PDF
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr class="text-center">
                            <th>#</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Producto</th>
                            <th>Receta</th>
                            <th>Área</th>
                            <th>Usuario Responsable</th>
                            <th class="text-center">Cant. Pedido</th>
                            <th class="text-center">Cant. Producida</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Subtotal</th>
                            <th class="text-center">Costo Diseño</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($producciones as $produccion)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    {{ \Carbon\Carbon::parse($produccion->fecha)->format('d/m/Y') }}
                                </td>
                                <td>
                                    @if($produccion->updated_at)
                                        {{ \Carbon\Carbon::parse($produccion->updated_at)->format('H:i:s') }}
                                    @else
                                        {{ $produccion->hora }}
                                    @endif
                                </td>
                                <td>{{ $produccion->recetaCabecera->producto->nombre ?? 'N/A' }}</td>
                                <td class="text-center">
                                    @if($produccion->recetaCabecera && $produccion->recetaCabecera->instructivo)
                                        <button type="button" class="btn btn-sm btn-primary" 
                                            onclick="verInstructivo({{ $produccion->recetaCabecera->id_recetas }}, {{ $produccion->cantidad_esperada }})" 
                                            data-toggle="tooltip" 
                                            title="Ver instructivo de {{ $produccion->recetaCabecera->nombre }}">
                                            <i class="fas fa-book-open"></i> Ver instructivo
                                        </button>
                                    @else
                                        <span class="text-muted">{{ $produccion->recetaCabecera->nombre ?? 'N/A' }}</span>
                                    @endif
                                </td>
                                <td>{{ $produccion->area->nombre ?? 'N/A' }}</td>
                                <td>{{ $produccion->produccionCabecera->usuario->nombre_personal ?? 'N/A' }}</td>
                                <td class="text-center">{{ number_format($produccion->cantidad_pedido, 2) }}</td>
                                <td class="text-center">{{ number_format($produccion->cantidad_producida_real, 2) }}</td>
                                <td class="text-center" >
                                    <span class="badge badge-{{ $produccion->es_terminado ? 'success' : ($produccion->es_cancelado ? 'danger ' : 'warning') }}" style="color: black;">
                                        {{ $produccion->es_terminado ? 'Terminado' : ($produccion->es_cancelado ? 'Cancelado' : 'Pendiente') }}
                                    </span>
                                </td>
                                <td class="text-center">S/ {{ number_format($produccion->subtotal_receta, 2) }}</td>
                                <td class="text-center">S/ {{ number_format($produccion->costo_diseño, 2) }}</td>
                                <td class="text-center">S/ {{ number_format($produccion->total_receta, 2) }}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-primary" onclick="verDetallesPedidos({{ htmlspecialchars(json_encode($produccion->pedidos_ids), ENT_QUOTES, 'UTF-8') }})" title="Ver Detalles de Pedidos">
                                        <i class="fas fa-list"></i>
                                    </button>
                                    @if($produccion->observaciones)
                                        <button type="button" class="btn btn-sm btn-warning" onclick="verObservaciones('{{ $produccion->observaciones }}')" title="Ver Observaciones">
                                            <i class="fas fa-comment"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="14" class="text-center py-4">
                                    <i class="fas fa-info-circle fa-3x mb-3"></i>
                                    <h4>No hay producciones registradas</h4>
                                    <p class="text-muted">No se encontraron producciones en el período seleccionado.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Observaciones -->
<div class="modal fade" id="observacionesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Observaciones</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="observacionesTexto"></p>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Instructivo -->
<div class="modal fade" id="instructivoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Instructivo de Producción</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="instructivoContenido">
                <!-- El contenido se cargará vía AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Modal para Detalles de Pedidos -->
<div class="modal fade" id="detallesPedidosModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles de Pedidos</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detallesPedidosContent">
                <!-- El contenido se llenará por JS -->
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/2.3.1/css/dataTables.dataTables.min.css">
<style>
    .container-fluid {
        padding: 0 15px;
    }
    
    .table-responsive {
        margin: 0;
        padding: 0;
    }
    
    .dataTables_wrapper {
        width: 100%;
        margin: 0;
        padding: 0;
    }
    
    .table {
        width: 100% !important;
        margin: 0;
    }
    
    .card {
        margin-bottom: 1rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    /* Estilos para la paginación */
    .pagination-container {
        max-width: 400px;
        margin: 0 auto;
    }
    
    .pagination {
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    
    .pagination .page-link {
        padding: 0.15rem 0.4rem;
        font-size: 0.8rem;
        line-height: 1.2;
        height: 24px;
        min-width: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 3px;
    }
    
    .pagination .page-item {
        margin: 0 1px;
    }
    
    .pagination .page-item.active .page-link {
        background-color: #007bff;
        border-color: #007bff;
    }
    
    .pagination .page-link:hover {
        background-color: #e9ecef;
    }
    
    .pagination .page-link svg {
        height: 12px;
        width: 12px;
    }
    
    svg {
        height: 10px !important;
    }
    
    .pagination-container svg {
        height: 20px !important;
        max-height: 20px !important;
        width: auto;
    }
    
    /* Estilos para el buscador */
    .dataTables_filter input {
        border: 2px solid #4e73df !important;
        border-radius: 50px !important;
        padding: 10px 15px 10px 40px !important;
        margin-left: 8px !important;
        box-shadow: 0 4px 10px rgba(78, 115, 223, 0.15) !important;
        transition: all 0.3s !important;
        width: 280px !important;
        background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path fill="%234e73df" d="M15.7 14.3l-4.2-4.2c1-1.2 1.5-2.7 1.5-4.3 0-3.6-2.9-6.5-6.5-6.5S0 2.2 0 5.8s2.9 6.5 6.5 6.5c1.6 0 3.1-.6 4.3-1.5l4.2 4.2c.2.2.5.3.7.3s.5-.1.7-.3c.4-.4.4-1 0-1.4zM6.5 10.8c-2.8 0-5-2.2-5-5s2.2-5 5-5 5 2.2 5 5-2.2 5-5 5z"/></svg>') !important;
        background-repeat: no-repeat !important;
        background-position: 15px center !important;
        background-size: 16px !important;
    }
    
    .dataTables_filter input:focus {
        border-color: #36b9cc !important;
        box-shadow: 0 0 15px rgba(54, 185, 204, 0.3) !important;
        outline: none !important;
        background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path fill="%2336b9cc" d="M15.7 14.3l-4.2-4.2c1-1.2 1.5-2.7 1.5-4.3 0-3.6-2.9-6.5-6.5-6.5S0 2.2 0 5.8s2.9 6.5 6.5 6.5c1.6 0 3.1-.6 4.3-1.5l4.2 4.2c.2.2.5.3.7.3s.5-.1.7-.3c.4-.4.4-1 0-1.4zM6.5 10.8c-2.8 0-5-2.2-5-5s2.2-5 5-5 5 2.2 5 5-2.2 5-5 5z"/></svg>') !important;
    }
    
    .dataTables_filter label {
        font-weight: 700;
        color: #4e73df;
        display: flex;
        align-items: center;
        position: relative;
    }
    
    .dataTables_filter label:before {
        content: '';
        display: inline-block;
        height: 2px;
        width: 30px;
        background: linear-gradient(90deg, #4e73df, transparent);
        margin-right: 10px;
    }
    
    /* Estilos para el selector de registros por página */
    .dataTables_length select {
        border: 2px solid #1cc88a !important;
        border-radius: 50px !important;
        padding: 8px 35px 8px 15px !important;
        margin: 0 8px !important;
        box-shadow: 0 4px 10px rgba(28, 200, 138, 0.15) !important;
        appearance: none !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14"><path fill="%231cc88a" d="M7 10.5c-.3 0-.5-.1-.7-.3l-6-6c-.4-.4-.4-1 0-1.4.4-.4 1-.4 1.4 0L7 8.1l5.3-5.3c.4-.4 1-.4 1.4 0 .4.4.4 1 0 1.4l-6 6c-.2.2-.4.3-.7.3z"/></svg>') !important;
        background-repeat: no-repeat !important;
        background-position: right 15px center !important;
        background-size: 14px !important;
        font-weight: 500 !important;
        color: #3a3b45 !important;
        transition: all 0.3s !important;
        background-color: rgba(28, 200, 138, 0.05) !important;
    }
    
    .dataTables_length select:focus {
        border-color: #36b9cc;
        box-shadow: 0 0 15px rgba(28, 200, 138, 0.25);
        outline: none;
        background-color: white;
    }
    
    .dataTables_length select option {
        font-weight: 500;
        padding: 10px;
    }
    
    .dataTables_length label {
        font-weight: 700;
        color: #1cc88a;
        display: flex;
        align-items: center;
        position: relative;
    }
    
    .dataTables_length label:after {
        content: '';
        display: inline-block;
        height: 2px;
        width: 30px;
        background: linear-gradient(90deg, transparent, #1cc88a);
        margin-left: 10px;
    }
    
    /* Estilos para la paginación */
    .dataTables_paginate {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        margin-top: 5px !important;
    }
    
    .dataTables_paginate .paginate_button {
        padding: 8px 14px !important;
        margin: 0 3px !important;
        border-radius: 50px !important;
        border: none !important;
        background: linear-gradient(135deg, #e74a3b 0%, #fd7e14 100%) !important;
        color: #fff !important;
        cursor: pointer !important;
        transition: all 0.3s !important;
        font-weight: 600 !important;
        box-shadow: 0 4px 10px rgba(231, 74, 59, 0.2) !important;
        position: relative !important;
        overflow: hidden !important;
        z-index: 1 !important;
    }
    
    .dataTables_paginate .paginate_button::before {
        content: '' !important;
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        height: 100% !important;
        background: linear-gradient(135deg, #fd7e14 0%, #e74a3b 100%) !important;
        opacity: 0 !important;
        transition: opacity 0.3s !important;
        z-index: -1 !important;
    }
    
    .dataTables_paginate .paginate_button:hover::before {
        opacity: 1 !important;
    }
    
    .dataTables_paginate .paginate_button.current {
        background: linear-gradient(135deg, #36b9cc 0%, #1cc88a 100%) !important;
        box-shadow: 0 4px 10px rgba(28, 200, 138, 0.3) !important;
        transform: scale(1.05) !important;
    }
    
    .dataTables_paginate .paginate_button.current::before {
        display: none !important;
    }
    
    .dataTables_paginate .paginate_button.disabled {
        background: #e9ecef !important;
        color: #adb5bd !important;
        cursor: not-allowed !important;
        box-shadow: none !important;
    }
    
    .dataTables_paginate .paginate_button.disabled::before {
        display: none !important;
    }
    
    .dataTables_paginate .ellipsis {
        color: #4e73df !important;
        font-weight: bold !important;
        margin: 0 5px !important;
    }
    
    /* Estilos para la información */
    .dataTables_info {
        color: #4e73df !important;
        font-weight: 600 !important;
        padding: 10px 15px !important;
        background-color: rgba(78, 115, 223, 0.05) !important;
        border-radius: 8px !important;
        border-left: 4px solid #4e73df !important;
        box-shadow: 0 2px 5px rgba(78, 115, 223, 0.1) !important;
    }
    
    /* Estilos adicionales para la tabla */
    .dataTable {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        width: 100% !important;
        margin-bottom: 1rem !important;
    }
    
    .dataTable thead th {
        background: linear-gradient(to right, #4e73df, #224abe) !important;
        color: white !important;
        font-weight: 600 !important;
        padding: 12px 15px !important;
        border: none !important;
        text-align: left !important;
        font-size: 14px !important;
        letter-spacing: 0.5px !important;
        text-transform: uppercase !important;
    }
    
    .dataTable thead th:first-child {
        border-radius: 10px 0 0 10px !important;
    }
    
    .dataTable thead th:last-child {
        border-radius: 0 10px 10px 0 !important;
    }
    
    .dataTable tbody tr {
        transition: all 0.3s !important;
    }
    
    .dataTable tbody tr:hover {
        background-color: rgba(78, 115, 223, 0.05) !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 5px 10px rgba(0, 0, 0, 0.05) !important;
    }
    
    .dataTable tbody td {
        padding: 12px 15px !important;
        border-bottom: 1px solid #e3e6f0 !important;
        vertical-align: middle !important;
    }
    
    .dataTable tbody tr:last-child td {
        border-bottom: none !important;
    }
</style>
@endpush

@push('scripts')
<script type="text/javascript" src="//cdn.datatables.net/2.3.1/js/dataTables.min.js"></script>
<script>
function verObservaciones(observaciones) {
    $('#observacionesTexto').text(observaciones);
    $('#observacionesModal').modal('show');
}

function verInstructivo(idReceta, cantidadEsperada) {
    $('#instructivoContenido').html('<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Cargando...</span></div><p class="mt-2">Cargando instructivo...</p></div>');
    $('#instructivoModal').modal('show');
    
    // Realizar la solicitud AJAX incluyendo la cantidad esperada
    $.get(`{{ route('recetas.show-instructivo') }}?id_receta=${idReceta}&cantidad=${cantidadEsperada}`, function(data) {
        $('#instructivoContenido').html(data);
    }).fail(function(xhr, status, error) {
        $('#instructivoContenido').html(`<div class="alert alert-danger">Error al cargar el instructivo: ${error}</div>`);
    });
}

function exportarExcel() {
    const params = new URLSearchParams(window.location.search);
    window.location.href = `{{ route('produccion.exportar-excel') }}?${params.toString()}`;
}

function exportarPDF() {
    const params = new URLSearchParams(window.location.search);
    window.location.href = `{{ route('produccion.exportar-pdf') }}?${params.toString()}`;
}

function verDetallesPedidos(pedidosIds) {
    $.ajax({
        url: '{{ route("produccion.detalles-pedidos") }}',
        type: 'POST',
        data: { 
            pedidos_ids: pedidosIds,
            _token: '{{ csrf_token() }}'
        },
        success: function(data) {
            let html = '<table class="table table-bordered"><thead><tr><th>ID Pedido</th><th>Cliente</th><th>Tienda</th><th>Cantidad</th><th>Estado</th><th>Fecha</th></tr></thead><tbody>';
            data.forEach(function(pedido) {
                html += `<tr>
                    <td>${pedido.id_pedidos_det}</td>
                    <td>${pedido.pedido_cabecera.usuario.nombre_personal}</td>
                    <td>${pedido.pedido_cabecera.tienda.nombre}</td>
                    <td>${pedido.cantidad}</td>
                    <td>${pedido.id_estados == 4 ? '<span class="badge badge-success" style="color: black;">Terminado</span>' : 
                         (pedido.id_estados == 5 ? '<span class="badge badge-danger" style="color: black;">Cancelado</span>' : 
                         '<span class="badge badge-warning" style="color: black;">Pendiente</span>')}</td>
                    <td>${new Date(pedido.created_at).toLocaleString()}</td>
                </tr>`;
            });
            html += '</tbody></table>';
            $('#detallesPedidosContent').html(html);
            $('#detallesPedidosModal').modal('show');
        },
        error: function() {
            $('#detallesPedidosContent').html('<div class="alert alert-danger">No se pudieron cargar los detalles.</div>');
            $('#detallesPedidosModal').modal('show');
        }
    });
}

$(document).ready(function() {
    // Inicializar DataTable
    let table = new DataTable('#dataTable', {
        order: [[1, 'desc'], [2, 'desc']], // Cambiado para considerar la nueva columna de numeración
        language: {
            "decimal": "",
            "emptyTable": "No hay datos disponibles",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
            "infoEmpty": "Mostrando 0 a 0 de 0 registros",
            "infoFiltered": "(filtrado de _MAX_ registros totales)",
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": "Mostrar _MENU_ registros",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": "Buscar:",
            "zeroRecords": "No se encontraron registros coincidentes",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            },
            "aria": {
                "sortAscending": ": activar para ordenar columna ascendente",
                "sortDescending": ": activar para ordenar columna descendente"
            }
        },
        pageLength: -1,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "<span class='font-weight-bold'>Todos</span>"]],
        responsive: true,
        dom: '<"row mb-3"<"col-md-6"<"d-flex align-items-center"l>><"col-md-6"<"d-flex justify-content-end"f>>>rt<"row mt-3"<"col-md-6"i><"col-md-6"<"d-flex justify-content-end"p>>>',
        buttons: [
            'copy', 'excel', 'pdf', 'print'
        ],
        columnDefs: [
            { 
                targets: [0], // Columna de numeración
                orderable: false,
                searchable: false
            },
            { 
                targets: [1], // Columna de fecha
                type: 'string',
                render: function(data, type, row) {
                    if (!data) return '';
                    if (type === 'sort') {
                        let parts = data.split('/');
                        if (parts.length === 3) {
                            return parts[2] + '-' + parts[1] + '-' + parts[0];
                        }
                        return data;
                    }
                    return data;
                }
            },
            { 
                targets: [2], // Columna de hora
                type: 'string',
                render: function(data, type, row) {
                    return data || '';
                }
            },
            { 
                targets: [3, 4, 5, 6], // Columnas de texto
                type: 'string',
                render: function(data, type, row) {
                    return data || '';
                }
            },
            { 
                targets: [7, 8, 10, 11, 12], // Columnas numéricas
                type: 'num',
                render: function(data, type, row) {
                    if (!data) return '0';
                    if (type === 'display') {
                        return data;
                    }
                    return parseFloat(data.toString().replace(/[^0-9.-]/g, '')) || 0;
                }
            },
            { 
                targets: [9], // Columna de estado
                type: 'string',
                render: function(data, type, row) {
                    return data || '';
                }
            },
            { 
                targets: [13], // Columna de acciones
                orderable: false,
                searchable: false
            }
        ],
        initComplete: function() {
            // Actualizar contadores
            updateCounters();
            
            // Escuchar cambios en la tabla
            this.api().on('draw.dt', function() {
                updateCounters();
            });
        }
    });
    
});
</script>
@endpush

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
            <div class="modal-body" id="instructivoContenido">
                <!-- Contenido cargado via AJAX -->
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando instructivo...</p>
                </div>
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

@endsection