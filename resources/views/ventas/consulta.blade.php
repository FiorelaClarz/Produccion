@extends('layouts.app')

@section('title', 'Consulta de Ventas por Tienda')

@section('styles')
<!-- DataTables CSS -->
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<!-- DateRangePicker CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
    .card-stats {
        transition: all 0.3s;
    }
    .card-stats:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .chart-container {
        position: relative;
        height: 300px;
        margin-bottom: 20px;
    }
    .date-range-container {
        position: relative;
    }
    .date-range-container .form-control {
        padding-left: 35px;
    }
    .date-range-container i {
        position: absolute;
        left: 10px;
        top: 10px;
        color: #6c757d;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-search me-2"></i>Consulta de Ventas por Tienda
                    </h5>
                </div>
                <div class="card-body">
                    <form id="consultaVentasForm">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="store_code" class="form-label">Código de Tienda</label>
                                <select class="form-select" id="store_code" name="store_code" required>
                                    <option value="">Seleccione una tienda</option>
                                    @foreach($tiendas as $codigo => $nombre)
                                    <option value="{{ $codigo }}">{{ $nombre }} ({{ $codigo }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="id_item" class="form-label">ID de Producto</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="id_item" name="id_item" value="250704" placeholder="Ej: 250704" required>
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Ejemplos</button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @foreach($ejemploProductos as $id => $nombre)
                                        <li><a class="dropdown-item" href="#" onclick="document.getElementById('id_item').value='{{ $id }}'; return false;">{{ $id }} - {{ $nombre }}</a></li>
                                        @endforeach
                                    </ul>
                                </div>
                                <small class="text-muted">Recomendado: 250704 (AJINOMOTO DELI ARROZ)</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="fecha_rango" class="form-label">Rango de Fechas</label>
                                <div class="date-range-container">
                                    <i class="fas fa-calendar-alt"></i>
                                    <input type="text" class="form-control" id="fecha_rango" name="fecha_rango" required>
                                </div>
                                <input type="hidden" id="fecha1" name="fecha1">
                                <input type="hidden" id="fecha2" name="fecha2">
                            </div>
                            <div class="col-md-2 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-1"></i> Consultar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sección para mostrar estadísticas y gráficos (inicialmente oculta) -->
    <div id="resultadosContainer" class="d-none">
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card card-stats bg-primary text-white shadow">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Total Unidades</h5>
                                <h2 class="mb-0" id="totalUnidades">0</h2>
                            </div>
                            <div>
                                <i class="fas fa-boxes fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stats bg-success text-white shadow">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Valor Total</h5>
                                <h2 class="mb-0" id="valorTotal">S/ 0.00</h2>
                            </div>
                            <div>
                                <i class="fas fa-dollar-sign fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stats bg-info text-white shadow">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Días con Ventas</h5>
                                <h2 class="mb-0" id="diasVentas">0</h2>
                            </div>
                            <div>
                                <i class="fas fa-calendar-check fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stats bg-warning text-white shadow">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Promedio Diario</h5>
                                <h2 class="mb-0" id="promedioDiario">0</h2>
                            </div>
                            <div>
                                <i class="fas fa-chart-line fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Gráficos -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-bar me-2"></i>Ventas por Fecha
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="ventasChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>Información del Producto
                        </h5>
                    </div>
                    <div class="card-body">
                        <h4 id="nombreProducto" class="mb-3 text-center">--</h4>
                        <table class="table table-sm">
                            <tbody>
                                <tr>
                                    <th>ID Producto:</th>
                                    <td id="idProductoInfo">--</td>
                                </tr>
                                <tr>
                                    <th>Tienda:</th>
                                    <td id="tiendaInfo">--</td>
                                </tr>
                                <tr>
                                    <th>Costo Unitario:</th>
                                    <td id="costoInfo">--</td>
                                </tr>
                                <tr>
                                    <th>Período:</th>
                                    <td id="periodoInfo">--</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tabla de datos detallados -->
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-table me-2"></i>Detalle de Ventas
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="ventasTable" class="table table-striped table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Tienda</th>
                                        <th>Fecha</th>
                                        <th>ID Producto</th>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Costo</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Los datos se cargarán dinámicamente -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de carga -->
    <div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <h5 class="mb-0">Consultando datos de ventas, por favor espere...</h5>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de error -->
    <div class="modal fade" id="errorModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Error</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="errorMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Moment.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/es.js"></script>

<!-- DateRangePicker -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    $(document).ready(function() {
        // Inicializar DateRangePicker
        $('#fecha_rango').daterangepicker({
            opens: 'left',
            locale: {
                format: 'DD/MM/YYYY',
                applyLabel: 'Aplicar',
                cancelLabel: 'Cancelar',
                fromLabel: 'Desde',
                toLabel: 'Hasta',
                customRangeLabel: 'Rango personalizado',
                daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                firstDay: 1
            },
            // Usar fechas que sabemos que tienen datos (enero a marzo 2025)
            startDate: moment('2025-01-28'),
            endDate: moment('2025-03-28')
        }, function(start, end) {
            // Actualizar los inputs ocultos con las fechas en formato ISO
            $('#fecha1').val(start.format('YYYY-MM-DD'));
            $('#fecha2').val(end.format('YYYY-MM-DD'));
        });
        
        // Trigger inicial para establecer los valores por defecto que sabemos que tienen datos
        $('#fecha1').val('2025-01-28');
        $('#fecha2').val('2025-03-28');
        
        // Inicializar DataTable
        const ventasTable = $('#ventasTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
            },
            order: [[1, 'desc']], // Ordenar por fecha descendente
            columns: [
                { data: 'store_code' },
                { 
                    data: 'sale_date',
                    render: function(data) {
                        return moment(data).format('DD/MM/YYYY');
                    }
                },
                { data: 'id_item' },
                { data: 'nombre' },
                { 
                    data: 'sales_quantity',
                    render: function(data) {
                        return parseFloat(data).toFixed(2);
                    }
                },
                { 
                    data: 'cost',
                    render: function(data) {
                        return 'S/ ' + parseFloat(data).toFixed(2);
                    }
                },
                { 
                    data: null,
                    render: function(data) {
                        const cantidad = parseFloat(data.sales_quantity);
                        const costo = parseFloat(data.cost);
                        return 'S/ ' + (cantidad * costo).toFixed(2);
                    }
                }
            ],
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ]
        });
        
        // Variable para almacenar el gráfico
        let ventasChart;
        
        // Manejar envío del formulario
        $('#consultaVentasForm').on('submit', function(e) {
            e.preventDefault();
            
            // Mostrar modal de carga
            const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
            loadingModal.show();
            
            // Obtener los datos del formulario
            const storeCode = $('#store_code').val();
            const idItem = $('#id_item').val();
            const fecha1 = $('#fecha1').val();
            const fecha2 = $('#fecha2').val();
            
            // Realizar la consulta a través del proxy local
            $.ajax({
                url: '{{ route("ventas.proxy") }}',
                type: 'GET',
                dataType: 'json',
                data: {
                    accion: 'MostrarByIdDate',
                    store_code: storeCode,
                    id_item: idItem,
                    fecha1: fecha1,
                    fecha2: fecha2
                },
                success: function(response) {
                    // Ocultar modal de carga
                    loadingModal.hide();
                    
                    // Variable para almacenar los datos a procesar
                    let salesData = [];
                    let alertMessage = '';
                    
                    // Comprobar si la respuesta tiene la nueva estructura con metadatos
                    if (response && response.meta && response.data) {
                        // Estamos usando los datos por defecto
                        salesData = response.data;
                        alertMessage = response.meta.message;
                        
                        // Mostrar alerta de que estamos usando datos por defecto
                        $('#resultadosContainer').prepend(
                            `<div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Información:</strong> ${alertMessage}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>`
                        );
                    } else if (Array.isArray(response)) {
                        // Respuesta normal sin metadatos
                        salesData = response;
                    } else {
                        // Formato desconocido
                        console.error('Formato de respuesta desconocido:', response);
                        $('#errorMessage').text('Formato de respuesta inesperado');
                        const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                        errorModal.show();
                        $('#resultadosContainer').addClass('d-none');
                        return;
                    }
                    
                    // Procesar los datos recibidos
                    if (salesData.length > 0) {
                        // Mostrar el contenedor de resultados
                        $('#resultadosContainer').removeClass('d-none');
                        
                        // Actualizar la tabla
                        ventasTable.clear().rows.add(salesData).draw();
                        
                        // Calcular estadísticas
                        let totalUnidades = 0;
                        let totalValor = 0;
                        const fechasSet = new Set();
                        let nombreProducto = '';
                        let costoUnitario = 0;
                        
                        salesData.forEach(item => {
                            const cantidad = parseFloat(item.sales_quantity);
                            const costo = parseFloat(item.cost);
                            
                            totalUnidades += cantidad;
                            totalValor += cantidad * costo;
                            fechasSet.add(item.sale_date);
                            
                            // Tomar los datos del primer elemento para la información del producto
                            if (!nombreProducto) {
                                nombreProducto = item.nombre;
                                costoUnitario = costo;
                            }
                        });
                        
                        const diasVentas = fechasSet.size;
                        const promedioDiario = diasVentas > 0 ? totalUnidades / diasVentas : 0;
                        
                        // Actualizar las métricas
                        $('#totalUnidades').text(totalUnidades.toFixed(2));
                        $('#valorTotal').text('S/ ' + totalValor.toFixed(2));
                        $('#diasVentas').text(diasVentas);
                        $('#promedioDiario').text(promedioDiario.toFixed(2));
                        
                        // Actualizar información del producto
                        $('#nombreProducto').text(nombreProducto);
                        $('#idProductoInfo').text(idItem);
                        $('#tiendaInfo').text(storeCode);
                        $('#costoInfo').text('S/ ' + costoUnitario.toFixed(2));
                        $('#periodoInfo').text(moment(fecha1).format('DD/MM/YYYY') + ' - ' + moment(fecha2).format('DD/MM/YYYY'));
                        
                        // Preparar datos para el gráfico
                        const ventasPorFecha = {};
                        salesData.forEach(item => {
                            const fecha = item.sale_date;
                            const cantidad = parseFloat(item.sales_quantity);
                            
                            if (!ventasPorFecha[fecha]) {
                                ventasPorFecha[fecha] = 0;
                            }
                            ventasPorFecha[fecha] += cantidad;
                        });
                        
                        // Ordenar fechas
                        const fechasOrdenadas = Object.keys(ventasPorFecha).sort();
                        const cantidadesOrdenadas = fechasOrdenadas.map(fecha => ventasPorFecha[fecha]);
                        
                        // Formatear fechas para el gráfico
                        const fechasFormateadas = fechasOrdenadas.map(fecha => moment(fecha).format('DD/MM/YYYY'));
                        
                        // Crear o actualizar el gráfico
                        const ctx = document.getElementById('ventasChart').getContext('2d');
                        
                        if (ventasChart) {
                            ventasChart.destroy();
                        }
                        
                        ventasChart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: fechasFormateadas,
                                datasets: [{
                                    label: 'Unidades Vendidas',
                                    data: cantidadesOrdenadas,
                                    backgroundColor: 'rgba(13, 110, 253, 0.7)',
                                    borderColor: 'rgba(13, 110, 253, 1)',
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        title: {
                                            display: true,
                                            text: 'Cantidad'
                                        }
                                    },
                                    x: {
                                        title: {
                                            display: true,
                                            text: 'Fecha'
                                        }
                                    }
                                },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: 'Ventas diarias - ' + nombreProducto
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return 'Unidades: ' + context.raw.toFixed(2);
                                            }
                                        }
                                    }
                                }
                            }
                        });
                        
                    } else {
                        // Mostrar mensaje si no hay datos
                        $('#errorMessage').text('No se encontraron ventas para los criterios seleccionados.');
                        const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                        errorModal.show();
                        
                        // Ocultar resultados si estaban visibles
                        $('#resultadosContainer').addClass('d-none');
                    }
                },
                error: function(xhr, status, error) {
                    // Ocultar modal de carga
                    loadingModal.hide();
                    
                    // Mostrar error
                    $('#errorMessage').text('Error al consultar la API: ' + (xhr.responseJSON?.message || error));
                    const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                    errorModal.show();
                    
                    // Ocultar resultados si estaban visibles
                    $('#resultadosContainer').addClass('d-none');
                }
            });
        });
    });
</script>
@endpush
