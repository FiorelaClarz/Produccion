@extends('layouts.app')

@push('styles')
<!-- DataTables CSS -->
<link href="//cdn.datatables.net/2.3.1/css/dataTables.dataTables.min.css" rel="stylesheet">
@endpush

@section('content')
@php
    // Obtener todas las unidades de medida para usar en la vista
    $unidadesMedida = \App\Models\UMedida::pluck('nombre', 'id_u_medidas');
@endphp
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Comparativo de Producción y Mermas</h1>
        <div>
            <a href="{{ route('produccion.comparativo.excel', ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin]) }}" 
               class="btn btn-sm btn-success shadow-sm mr-2">
                <i class="fas fa-file-excel fa-sm text-white-50"></i> Exportar Excel
            </a>
            <a href="{{ route('produccion.comparativo.pdf', ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin]) }}" 
               class="btn btn-sm btn-secondary shadow-sm" target="_blank">
                <i class="fas fa-file-pdf fa-sm text-white-50"></i> Exportar PDF
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Filtros</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('produccion.comparativo') }}" method="GET" class="form-inline">
                <div class="form-group mb-2 mr-2">
                    <label for="fecha_inicio" class="mr-2">Fecha de Inicio:</label>
                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="{{ $fechaInicio }}">
                </div>
                <div class="form-group mb-2 mr-2">
                    <label for="fecha_fin" class="mr-2">Fecha de Fin:</label>
                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="{{ $fechaFin }}">
                </div>
                <button type="submit" class="btn btn-primary mb-2">
                    <i class="fas fa-filter mr-1"></i> Filtrar
                </button>
            </form>
        </div>
    </div>

    <!-- Tabla de Comparación -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Datos del {{ $fechaInicio == $fechaFin ? Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') : Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') . ' al ' . Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="dataTable" width="100%" cellspacing="0">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>FECHA</th>
                            <th>ÁREA</th>
                            <th>PRODUCTO</th>
                            <th class="text-center">CANT. PRODUCIDA</th>
                            <th class="text-center">UM</th>
                            <th class="text-center">CANT. VENDIDA</th>
                            <th class="text-center">MERMA</th>
                            <th class="text-center">DIFERENCIA</th>
                            <th class="text-center">COSTO PRODUCCIÓN</th>
                            <th class="text-center">UTILIDAD BRUTA</th>
                            <th class="text-center">VENTAS</th>
                            <th class="text-center">COSTO MERMA</th>
                            <th class="text-center">COSTO DIF.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($resultados as $resultado)
                            <tr>
                                <td>{{ $resultado['fecha'] }}</td>
                                <td>{{ $resultado['area'] }}</td>
                                <td>{{ $resultado['producto'] }}</td>
                                <td class="text-right">{{ number_format($resultado['cantidad_producida'] ?? 0, 2) }}</td>
                                <td class="text-center">
                                    @if(isset($resultado['id_u_medidas_prodcc']) && isset($unidadesMedida[$resultado['id_u_medidas_prodcc']]))
                                        {{ $unidadesMedida[$resultado['id_u_medidas_prodcc']] }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-right">{{ number_format($resultado['cantidad_vendida'] ?? 0, 2) }}</td>
                                <td class="text-right">{{ number_format($resultado['cantidad_merma'] ?? 0, 2) }}</td>
                                <td class="text-right">{{ number_format($resultado['diferencia'] ?? 0, 2) }}</td>
                                <td class="text-right">S/ {{ number_format($resultado['costo_produccion'] ?? 0, 2) }}</td>
                                @php
                                    $utilidadBruta = ($resultado['ventas'] ?? 0) - ($resultado['costo_produccion'] ?? 0);
                                    $colorUtilidad = $utilidadBruta < 0 ? 'text-danger' : '';
                                @endphp
                                <td class="text-right {{ $colorUtilidad }}">S/ {{ number_format($utilidadBruta, 2) }}</td>
                                <td class="text-right">S/ {{ number_format($resultado['ventas'] ?? 0, 2) }}</td>
                                <td class="text-right">S/ {{ number_format($resultado['costo_merma'] ?? 0, 2) }}</td>
                                <td class="text-right">S/ {{ number_format($resultado['costo_diferencia'] ?? 0, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="text-center">No hay datos disponibles</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Resumen de Datos Filtrados -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Resumen de Datos Filtrados</h6>
            <div class="small text-muted">
                <i class="fas fa-info-circle mr-1"></i>Los totales reflejan solo los datos filtrados actualmente
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-center">CANT. PRODUCIDA</th>
                            <th class="text-center">UM</th>
                            <th class="text-center">CANT. VENDIDA</th>
                            <th class="text-center">MERMA</th>
                            <th class="text-center">DIFERENCIA</th>
                            <th class="text-center">COSTO PRODUCCIÓN</th>
                            <th class="text-center">UTILIDAD BRUTA</th>
                            <th class="text-center">VENTAS</th>
                            <th class="text-center">COSTO MERMA</th>
                            <th class="text-center">COSTO DIF.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="font-weight-bold">
                            <td class="text-right"><span id="total-produccion">{{ number_format($totales['produccion'], 2) }}</span></td>
                            <td class="text-center">Varias UM</td>
                            <td class="text-right"><span id="total-venta">{{ number_format($totales['venta'], 2) }}</span></td>
                            <td class="text-right"><span id="total-merma">{{ number_format($totales['merma'], 2) }}</span></td>
                            <td class="text-right"><span id="total-diferencia">{{ number_format($totales['diferencia'], 2) }}</span></td>
                            <td class="text-right"><span id="total-costo-produccion">S/ {{ number_format($totales['costo_produccion'] ?? 0, 2) }}</span></td>
                            @php
                                $colorUtilidadTotal = $totales['utilidad_bruta'] < 0 ? 'text-danger' : '';
                            @endphp
                            <td class="text-right"><span id="total-utilidad" class="{{ $colorUtilidadTotal }}">S/ {{ number_format($totales['utilidad_bruta'], 2) }}</span></td>
                            <td class="text-right"><span id="total-ventas">S/ {{ number_format($totales['ventas'], 2) }}</span></td>
                            <td class="text-right"><span id="total-costo-merma">S/ {{ number_format($totales['costo_merma'], 2) }}</span></td>
                            <td class="text-right"><span id="total-costo-diferencia">S/ {{ number_format($totales['costo_diferencia'], 2) }}</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Gráficos y estadísticas -->
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Distribución de Cantidades</h6>
                </div>
                <div class="card-body">
                    <canvas id="cantidadesChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Distribución de Costos</h6>
                </div>
                <div class="card-body">
                    <canvas id="costosChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- DataTables JS -->
<script src="//cdn.datatables.net/2.3.1/js/dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        // Variables para almacenar los gráficos y tabla
        let cantidadesChart;
        let costosChart;
        let table;
        
        // Inicializar DataTable con la nueva sintaxis de la versión 2.3.1
        table = new DataTable('#dataTable', {
            ordering: true,
            language: {
                search: "Buscar:",
                lengthMenu: "Mostrar _MENU_ registros por página",
                zeroRecords: "No se encontraron registros",
                info: "Mostrando página _PAGE_ de _PAGES_",
                infoEmpty: "No hay registros disponibles",
                infoFiltered: "(filtrado de _MAX_ registros totales)",
                paginate: {
                    first: "Primero",
                    last: "Último",
                    next: "Siguiente",
                    previous: "Anterior"
                }
            },
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ],
            responsive: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
            drawCallback: function() {
                // La tabla ya está inicializada en este punto
                setTimeout(actualizarTotales, 50);
            }
        });
        
        // Función para actualizar los gráficos con los nuevos totales
        function actualizarGraficos(totales) {
            console.log('Actualizando gráficos con nuevos totales');
            
            // Actualizar datos del gráfico de cantidades
            if (cantidadesChart) {
                cantidadesChart.data.datasets[0].data = [
                    totales.venta,
                    totales.merma,
                    totales.diferencia
                ];
                cantidadesChart.update();
            }
            
            // Actualizar datos del gráfico de costos
            if (costosChart) {
                costosChart.data.datasets[0].data = [
                    totales.ventas,
                    totales.costo_produccion,
                    totales.costo_merma,
                    totales.costo_diferencia
                ];
                costosChart.update();
            }
        }
        
        // Función para actualizar los totales en base a las filas filtradas visibles
        function actualizarTotales() {
            console.log('Actualizando totales de filas visibles...');
            
            let totales = {
                produccion: 0,
                venta: 0,
                merma: 0,
                diferencia: 0,
                costo_produccion: 0,
                utilidad_bruta: 0,
                ventas: 0,
                costo_merma: 0,
                costo_diferencia: 0
            };
            
            // Usar las filas que coinciden con los filtros actuales
            // search:'applied' garantiza que solo se tomen las filas que pasan el filtrado
            const filasVisibles = table.rows({search:'applied'}).nodes();
            console.log('Filas filtradas encontradas:', filasVisibles.length);
            
            // Iterar sobre las filas filtradas visibles
            $(filasVisibles).each(function() {
                const cells = $(this).find('td');
                
                // Extraer valores numéricos directamente de las celdas DOM
                const produccion = parseFloat($(cells[3]).text().replace(/[^\d.-]/g, '')) || 0;
                const venta = parseFloat($(cells[5]).text().replace(/[^\d.-]/g, '')) || 0;
                const merma = parseFloat($(cells[6]).text().replace(/[^\d.-]/g, '')) || 0;
                const diferencia = parseFloat($(cells[7]).text().replace(/[^\d.-]/g, '')) || 0;
                const costoProduccion = parseFloat($(cells[8]).text().replace(/[^\d.-]/g, '')) || 0;
                const utilidad = parseFloat($(cells[9]).text().replace(/[^\d.-]/g, '')) || 0;
                const ventas = parseFloat($(cells[10]).text().replace(/[^\d.-]/g, '')) || 0;
                const costoMerma = parseFloat($(cells[11]).text().replace(/[^\d.-]/g, '')) || 0;
                const costoDif = parseFloat($(cells[12]).text().replace(/[^\d.-]/g, '')) || 0;
                
                // Sumar a los totales
                totales.produccion += produccion;
                totales.venta += venta;
                totales.merma += merma;
                totales.diferencia += diferencia;
                totales.costo_produccion += costoProduccion;
                totales.utilidad_bruta += utilidad;
                totales.ventas += ventas;
                totales.costo_merma += costoMerma;
                totales.costo_diferencia += costoDif;
            });
            
            console.log('Totales calculados:', totales);
            
            // Actualizar los totales en la UI
            $('#total-produccion').text(totales.produccion.toFixed(2));
            $('#total-venta').text(totales.venta.toFixed(2));
            $('#total-merma').text(totales.merma.toFixed(2));
            $('#total-diferencia').text(totales.diferencia.toFixed(2));
            $('#total-costo-produccion').text('S/ ' + totales.costo_produccion.toFixed(2));
            
            // Aplicar color rojo a utilidad bruta negativa
            const $totalUtilidad = $('#total-utilidad');
            $totalUtilidad.text('S/ ' + totales.utilidad_bruta.toFixed(2));
            if (totales.utilidad_bruta < 0) {
                $totalUtilidad.addClass('text-danger');
            } else {
                $totalUtilidad.removeClass('text-danger');
            }
            
            $('#total-ventas').text('S/ ' + totales.ventas.toFixed(2));
            $('#total-costo-merma').text('S/ ' + totales.costo_merma.toFixed(2));
            $('#total-costo-diferencia').text('S/ ' + totales.costo_diferencia.toFixed(2));
            
            // Actualizar datos de los gráficos
            actualizarGraficos(totales);
        }
        
        // Eventos para actualizar totales cuando se aplican filtros o cambia la tabla
        table.on('search.dt', function() {
            console.log('Filtro aplicado - actualizando totales');
            setTimeout(actualizarTotales, 100);
        });
        
        table.on('draw.dt', function() {
            console.log('Tabla redibujada - actualizando totales');
            setTimeout(actualizarTotales, 100);
        });
        
        // No necesitamos actualizar en eventos de paginación ya que solo afectan a la visualización
        // pero los filtros siguen siendo los mismos
        
        // Actualizamos al cargar la página
        setTimeout(actualizarTotales, 200);
        
        // Datos para gráficos
        const cantidadesData = {
            labels: ['Vendida', 'Merma', 'Diferencia'],
            datasets: [{
                label: 'Distribución de cantidades',
                data: [
                    {{ $totales['venta'] }},
                    {{ $totales['merma'] }},
                    {{ $totales['diferencia'] }}
                ],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(255, 206, 86, 0.7)'
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(255, 206, 86, 1)'
                ],
                borderWidth: 1
            }]
        };
        
        const costosData = {
            labels: ['Ventas', 'Costo Producción', 'Costo Merma', 'Costo Diferencia'],
            datasets: [{
                label: 'Distribución de costos',
                data: [
                    {{ $totales['ventas'] }},
                    {{ $totales['costo_produccion'] ?? 0 }},
                    {{ $totales['costo_merma'] }},
                    {{ $totales['costo_diferencia'] }}
                ],
                backgroundColor: [
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(46, 134, 193, 0.7)',
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(255, 206, 86, 0.7)'
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)',
                    'rgba(46, 134, 193, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(255, 206, 86, 1)'
                ],
                borderWidth: 1
            }]
        };
        
        const chartOptions = {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        };
        
        // Configuración para tooltips de cantidades
        const cantidadesTooltipCallback = {
            callbacks: {
                label: function(context) {
                    let label = context.label || '';
                    if (label) {
                        label += ': ';
                    }
                    if (context.raw !== null) {
                        label += parseFloat(context.raw).toFixed(2) + ' (Varias UM)';
                    }
                    return label;
                }
            }
        };
        
        // Configuración para tooltips de costos
        const costosTooltipCallback = {
            callbacks: {
                label: function(context) {
                    let label = context.label || '';
                    if (label) {
                        label += ': ';
                    }
                    if (context.raw !== null) {
                        label += 'S/ ' + parseFloat(context.raw).toFixed(2);
                    }
                    return label;
                }
            }
        };
        
        // Crear gráfico de cantidades
        const ctxCantidades = document.getElementById('cantidadesChart').getContext('2d');
        cantidadesChart = new Chart(ctxCantidades, {
            type: 'bar',
            data: cantidadesData,
            options: {
                ...chartOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Cantidad'
                        }
                    }
                },
                plugins: {
                    ...chartOptions.plugins,
                    tooltip: cantidadesTooltipCallback
                }
            }
        });
        
        // Crear gráfico de costos
        const ctxCostos = document.getElementById('costosChart').getContext('2d');
        costosChart = new Chart(ctxCostos, {
            type: 'bar',
            data: costosData,
            options: {
                ...chartOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Monto (S/)',
                            font: {
                                weight: 'bold'
                            }
                        }
                    }
                },
                plugins: {
                    ...chartOptions.plugins,
                    tooltip: costosTooltipCallback
                }
            }
        });
    });
</script>
@endpush


