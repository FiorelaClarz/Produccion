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
                        <tr>
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
                                <td>{{ $produccion->hora }}</td>
                                <td>{{ $produccion->recetaCabecera->producto->nombre ?? 'N/A' }}</td>
                                <td>{{ $produccion->recetaCabecera->nombre ?? 'N/A' }}</td>
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
</style>
@endpush

@push('scripts')
<script type="text/javascript" src="//cdn.datatables.net/2.3.1/js/dataTables.min.js"></script>
<script>
function verObservaciones(observaciones) {
    $('#observacionesTexto').text(observaciones);
    $('#observacionesModal').modal('show');
}

function verInstructivo(idReceta) {
    $('#instructivoContenido').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');
    $('#instructivoModal').modal('show');
    
    $.get(`{{ route('recetas.show-instructivo') }}?id_receta=${idReceta}`, function(data) {
        $('#instructivoContenido').html(data);
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
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
        responsive: true,
        dom: '<"top"lf>rt<"bottom"ip><"clear">',
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
    
    // Función para actualizar los contadores
    // function updateCounters() {
    //     let total = table.rows().count();
    //     let terminadas = 0;
    //     let pendientes = 0;
    //     let canceladas = 0;

    //     // Recorrer todas las filas visibles
    //     table.rows().every(function() {
    //         let estado = $(this.node()).find('td:eq(9) .badge').text().trim();
    //         if (estado === 'Terminado') {
    //             terminadas++;
    //         } else if (estado === 'Pendiente') {
    //             pendientes++;
    //         } else if (estado === 'Cancelado') {
    //             canceladas++;
    //         }
    //     });
        
    //     $('#totalProducciones').text(total);
    //     $('#totalTerminadas').text(terminadas);
    //     $('#totalPendientes').text(pendientes);
    //     $('#totalCanceladas').text(canceladas);
    // }
});
</script>
@endpush

@endsection 