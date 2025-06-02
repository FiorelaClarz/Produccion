@extends('layouts.app')

@section('title', 'Gestión de Mermas')

@push('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="//cdn.datatables.net/2.3.1/css/dataTables.dataTables.min.css">

<style>
    /* Estilos personalizados para la paginación */
    .pagination .page-item .page-link {
        padding: 0.2rem 0.4rem;
        font-size: 0.8rem;
        line-height: 1.2;
    }
    
    /* Ajustar tamaño de los iconos de flecha */
    .pagination svg {
        width: 10px;
        height: 10px;
    }
    
    /* Estilos específicos para los botones Previous y Next */
    .pagination .page-item:first-child .page-link,
    .pagination .page-item:last-child .page-link {
        padding: 0.2rem 0.35rem;
    }
    
    /* Asegurar alineación vertical correcta */
    .pagination .page-item {
        display: flex;
        align-items: center;
    }
    
    /* Reducir el tamaño del contenedor de las flechas */
    .pagination nav div:first-child {
        padding: 0;
        display: none !important;
    }
    
    /* Reducir el espacio entre elementos de paginación */
    .pagination .page-item + .page-item {
        margin-left: -1px;
    }
    
    /* Añadir esquinas redondeadas solo en los extremos */
    .pagination .page-item:first-child .page-link {
        border-top-left-radius: 0.2rem;
        border-bottom-left-radius: 0.2rem;
    }
    
    .pagination .page-item:last-child .page-link {
        border-top-right-radius: 0.2rem;
        border-bottom-right-radius: 0.2rem;
    }
    
    /* Estilos mejorados para DataTables */
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 15px;
        text-align: right;
    }
    
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 6px 12px;
        margin-left: 6px;
    }
    
    .dataTables_wrapper .table td, 
    .dataTables_wrapper .table th {
        vertical-align: middle;
    }
    
    /* Destacar filas al pasar el cursor */
    #dataTable tbody tr:hover {
        background-color: rgba(0,123,255,0.1) !important;
    }
    
    /* Mejorar visibilidad de la búsqueda */
    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        outline: 0;
    }
    
    /* Estilo para las filas encontradas en la búsqueda */
    .dataTables_wrapper tbody tr.dt-hasChild {
        background-color: rgba(255, 243, 205, 0.5) !important;
    }
    
    /* Estilos para el cuadro de búsqueda personalizado */
    .custom-search-container {
        margin-bottom: 1rem;
    }
    
    .custom-search-container input {
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    
    .custom-search-container input:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Gestión de Mermas</h1>
        <div>
            <a href="{{ route('mermas.pdf-multiple', ['filter' => $filter, 'custom_date' => request('custom_date')]) }}" class="btn btn-sm btn-secondary shadow-sm mr-2" target="_blank">
                <i class="fas fa-file-pdf fa-sm text-white-50"></i> Exportar Lista PDF
            </a>
            <a href="{{ route('mermas.excel', ['filter' => $filter, 'custom_date' => request('custom_date')]) }}" class="btn btn-sm btn-success shadow-sm mr-2">
                <i class="fas fa-file-excel fa-sm text-white-50"></i> Exportar Excel
            </a>
            <a href="{{ route('mermas.create') }}" class="btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Nueva Merma
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-gradient-light">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter mr-2"></i>Filtros de Búsqueda</h6>
            <div>
                <a href="{{ route('mermas.index', ['filter' => 'today']) }}" class="btn btn-sm {{ $filter == 'today' ? 'btn-primary' : 'btn-outline-primary' }} mr-1">
                    <i class="fas fa-calendar-day mr-1"></i>Hoy
                </a>
                <a href="{{ route('mermas.index', ['filter' => 'yesterday']) }}" class="btn btn-sm {{ $filter == 'yesterday' ? 'btn-primary' : 'btn-outline-primary' }} mr-1">
                    <i class="fas fa-calendar-minus mr-1"></i>Ayer
                </a>
                <a href="{{ route('mermas.index', ['filter' => 'week']) }}" class="btn btn-sm {{ $filter == 'week' ? 'btn-primary' : 'btn-outline-primary' }} mr-1">
                    <i class="fas fa-calendar-week mr-1"></i>Semana
                </a>
                <button class="btn btn-sm {{ $filter == 'custom' ? 'btn-primary' : 'btn-outline-primary' }}" data-toggle="modal" data-target="#customDateModal">
                    <i class="fas fa-calendar-alt mr-1"></i>Rango de Fechas
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="form-group mb-md-0">
                        <div class="d-flex align-items-center">
                            <span class="mr-2"><i class="fas fa-info-circle text-info"></i></span>
                            <div>
                                <strong>Filtro activo:</strong>
                                <div class="badge badge-primary ml-2 px-3 py-2" style="font-size: 0.9rem; color: blue; border-radius: 20px;">
                                    @switch($filter)
                                        @case('today')
                                            <i class="fas fa-calendar-day mr-1"></i> Hoy ({{ date('d/m/Y') }})
                                            @break
                                        @case('yesterday')
                                            <i class="fas fa-calendar-minus mr-1"></i> Ayer ({{ date('d/m/Y', strtotime('-1 day')) }})
                                            @break
                                        @case('week')
                                            <i class="fas fa-calendar-week mr-1"></i> Última semana ({{ date('d/m/Y', strtotime('-7 days')) }} - {{ date('d/m/Y') }})
                                            @break
                                        @case('custom')
                                            <i class="fas fa-calendar-alt mr-1"></i>
                                            @if(request('start_date') && request('end_date'))
                                                {{ date('d/m/Y', strtotime(request('start_date'))) }} - {{ date('d/m/Y', strtotime(request('end_date'))) }}
                                            @else
                                                {{ request('custom_date') ? date('d/m/Y', strtotime(request('custom_date'))) : 'Rango personalizado' }}
                                            @endif
                                        @break
                                    @default
                                        Todos
                                @endswitch
                            </span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Listado de Mermas -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Listado de Mermas</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Usuario</th>
                            <th>Tienda</th>
                            <th>Detalles</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mermas as $merma)
                            <tr>
                                <td>{{ $merma->id_mermas_cab }}</td>
                                <td>{{ \Carbon\Carbon::parse($merma->fecha_registro)->format('d/m/Y') }}</td>
                                <td>{{ $merma->hora_registro }}</td>
                                <td>{{ $merma->usuario->nombre_personal ?? 'N/A' }}</td>
                                <td>{{ $merma->tienda->nombre ?? 'N/A' }}</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-info" 
                                        data-toggle="collapse" 
                                        data-target="#collapse-{{ $merma->id_mermas_cab }}" 
                                        aria-expanded="false">
                                        <i class="fas fa-eye"></i> Ver
                                    </button>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('mermas.show', $merma->id_mermas_cab) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($merma->id_usuarios == Auth::id())
                                            <a href="{{ route('mermas.edit', $merma->id_mermas_cab) }}" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('mermas.pdf', $merma->id_mermas_cab) }}" class="btn btn-sm btn-secondary" target="_blank">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger delete-merma" 
                                                data-id="{{ $merma->id_mermas_cab }}"
                                                data-toggle="modal" 
                                                data-target="#deleteModal">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            <!-- Detalles expandibles -->
                            <tr class="collapse" id="collapse-{{ $merma->id_mermas_cab }}">
                                <td colspan="7" class="p-0">
                                    <div class="p-3 bg-light">
                                        <h6 class="font-weight-bold mb-3">Detalles de la Merma #{{ $merma->id_mermas_cab }}</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0">
                                                <thead class="bg-primary text-white">
                                                    <tr>
                                                        <th>Área</th>
                                                        <th>Receta</th>
                                                        <th>Producto</th>
                                                        <th>Cantidad</th>
                                                        <th>Costo</th>
                                                        <th>Total</th>
                                                        <th>U. Medida</th>
                                                        <th>Observación</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($merma->mermasDetalle as $detalle)
                                                        <tr>
                                                            <td>{{ $detalle->area->nombre ?? 'N/A' }}</td>
                                                            <td>{{ $detalle->receta->nombre ?? 'N/A' }}</td>
                                                            <td>{{ optional($detalle->receta)->id_productos_api ?? 'N/A' }}</td>
                                                            <td class="text-right">{{ number_format($detalle->cantidad, 2) }}</td>
                                                            <td class="text-right">{{ number_format($detalle->costo ?? 0, 2) }}</td>
                                                            <td class="text-right">{{ number_format($detalle->total ?? 0, 2) }}</td>
                                                            <td>{{ $detalle->uMedida->nombre ?? 'N/A' }}</td>
                                                            <td>{{ $detalle->obs ?? '-' }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="8" class="text-center">No hay detalles disponibles</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No hay registros de mermas disponibles</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-end mt-3" style="display: none !important;">
                {{ $mermas->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal de Eliminación -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar esta merma? Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Rango de Fechas -->
<div class="modal fade" id="customDateModal" tabindex="-1" role="dialog" aria-labelledby="customDateModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title" id="customDateModalLabel"><i class="fas fa-calendar-alt mr-2"></i>Seleccionar Rango de Fechas</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="customDateForm" action="{{ route('mermas.index') }}" method="GET">
                    <input type="hidden" name="filter" value="custom">
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>Selecciona un rango de fechas para filtrar las mermas.
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="start_date"><i class="fas fa-calendar mr-1"></i>Fecha de inicio:</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" required value="{{ request('start_date', date('Y-m-d', strtotime('-7 days'))) }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="end_date"><i class="fas fa-calendar-check mr-1"></i>Fecha de fin:</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" required value="{{ request('end_date', date('Y-m-d')) }}">
                        </div>
                    </div>
                    
                    <!-- Botones rápidos para seleccionar rangos comunes -->
                    <div class="mb-3">
                        <small class="text-muted d-block mb-2">Rangos predefinidos:</small>
                        <button type="button" class="btn btn-sm btn-outline-secondary mr-1 quick-range" data-days="7">Última semana</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary mr-1 quick-range" data-days="30">Último mes</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary quick-range" data-days="90">Último trimestre</button>
                    </div>
                    
                    <div class="text-right mt-4">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-filter mr-1"></i>Aplicar Filtro</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<!-- DataTables JS -->
<script src="//cdn.datatables.net/2.3.1/js/dataTables.min.js"></script>

<script>
    $(document).ready(function() {
        // Manejar los botones de rango rápido en el modal de fechas
        $('.quick-range').on('click', function() {
            const days = $(this).data('days');
            const endDate = new Date();
            const startDate = new Date();
            
            // Restar los días al startDate
            startDate.setDate(startDate.getDate() - days);
            
            // Formatear las fechas como YYYY-MM-DD
            const formatDate = (date) => {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            };
            
            // Establecer los valores en los campos de fecha
            $('#start_date').val(formatDate(startDate));
            $('#end_date').val(formatDate(endDate));
            
            // Destacar el botón seleccionado
            $('.quick-range').removeClass('btn-secondary').addClass('btn-outline-secondary');
            $(this).removeClass('btn-outline-secondary').addClass('btn-secondary');
        });

        // Enfoque simplificado que utiliza la tabla HTML existente
        try {
            // Obtener referencias a elementos importantes
            const dataTable = document.getElementById('dataTable');
            
            // Agregar div para la búsqueda manual si no existe DataTables
            if (!document.querySelector('.custom-search-container')) {
                const searchContainer = document.createElement('div');
                searchContainer.className = 'custom-search-container mb-3';
                searchContainer.innerHTML = `
                    <div class="d-flex justify-content-end">
                        <div class="form-group mb-0">
                            <label for="customSearch" class="mr-2">Buscar:</label>
                            <input type="text" id="customSearch" class="form-control form-control-sm" style="display: inline-block; width: auto;">
                        </div>
                    </div>
                `;
                dataTable.parentNode.insertBefore(searchContainer, dataTable);
                
                // Implementar búsqueda manual
                document.getElementById('customSearch').addEventListener('keyup', function() {
                    const searchText = this.value.toLowerCase();
                    const rows = dataTable.querySelectorAll('tbody tr:not(.collapse)');
                    
                    rows.forEach(row => {
                        let found = false;
                        const cells = row.querySelectorAll('td:not(:nth-child(6)):not(:nth-child(7))');
                        
                        cells.forEach(cell => {
                            if (cell.textContent.toLowerCase().includes(searchText)) {
                                found = true;
                            }
                        });
                        
                        row.style.display = found ? '' : 'none';
                        
                        // Manejar las filas de detalles expandibles
                        const detailId = row.getAttribute('id');
                        if (detailId) {
                            const detailRow = document.getElementById(detailId);
                            if (detailRow) {
                                detailRow.style.display = found ? '' : 'none';
                            }
                        }
                    });
                });
            }
            
            // Configurar ordenamiento manual si se hace clic en los encabezados
            const headers = dataTable.querySelectorAll('thead th');
            headers.forEach((header, index) => {
                if (index < 5) { // Solo los primeros 5 encabezados son ordenables
                    header.style.cursor = 'pointer';
                    header.addEventListener('click', function() {
                        sortTable(index);
                    });
                }
            });
            
            // Función para ordenar la tabla
            function sortTable(columnIndex) {
                const tbody = dataTable.querySelector('tbody');
                const rows = Array.from(tbody.querySelectorAll('tr:not(.collapse)'));
                
                // Determinar el orden (ascendente o descendente)
                const currentOrder = headers[columnIndex].getAttribute('data-order') || 'asc';
                const newOrder = currentOrder === 'asc' ? 'desc' : 'asc';
                
                // Actualizar atributo de orden en todos los encabezados
                headers.forEach(h => h.removeAttribute('data-order'));
                headers[columnIndex].setAttribute('data-order', newOrder);
                
                // Ordenar filas
                rows.sort((a, b) => {
                    const aValue = a.cells[columnIndex].textContent.trim();
                    const bValue = b.cells[columnIndex].textContent.trim();
                    
                    // Intentar ordenar como números si es posible
                    const aNum = parseFloat(aValue);
                    const bNum = parseFloat(bValue);
                    
                    if (!isNaN(aNum) && !isNaN(bNum)) {
                        return newOrder === 'asc' ? aNum - bNum : bNum - aNum;
                    } else {
                        return newOrder === 'asc' ? aValue.localeCompare(bValue) : bValue.localeCompare(aValue);
                    }
                });
                
                // Reordenar el DOM
                rows.forEach(row => {
                    tbody.appendChild(row);
                    
                    // Mover también las filas de detalles si existen
                    const id = row.cells[0].textContent.trim();
                    const detailRow = document.getElementById(`collapse-${id}`);
                    if (detailRow) {
                        tbody.appendChild(detailRow);
                    }
                });
            }
            
            console.log('Configuración de tabla completada exitosamente');
        } catch (e) {
            console.error('Error al configurar la tabla:', e);
        }

        // Configurar el modal de eliminación
        $('.delete-merma').click(function() {
            const id = $(this).data('id');
            $('#deleteForm').attr('action', `{{ url('mermas') }}/${id}`);
        });
    });
</script>
@endpush



