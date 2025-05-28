@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Comparativo de Producción y Mermas</h1>
        <div>
            <a href="{{ route('produccion.comparativo.pdf', ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin]) }}" 
               class="btn btn-sm btn-secondary shadow-sm mr-2" target="_blank">
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
                            <th class="text-center">CANT. VENDIDA</th>
                            <th class="text-center">MERMA</th>
                            <th class="text-center">DIFERENCIA</th>
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
                                <td class="text-right">{{ number_format($resultado['cantidad_producida'], 2) }} KG</td>
                                <td class="text-right">{{ number_format($resultado['cantidad_vendida'], 2) }} KG</td>
                                <td class="text-right">{{ number_format($resultado['cantidad_merma'], 2) }} KG</td>
                                <td class="text-right">{{ number_format($resultado['diferencia'], 2) }} KG</td>
                                <td class="text-right">S/ {{ number_format($resultado['utilidad_bruta'], 2) }}</td>
                                <td class="text-right">S/ {{ number_format($resultado['ventas'], 2) }}</td>
                                <td class="text-right">S/ {{ number_format($resultado['costo_merma'], 2) }}</td>
                                <td class="text-right">S/ {{ number_format($resultado['costo_diferencia'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center">No hay datos disponibles</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-light font-weight-bold">
                        <tr>
                            <td colspan="3" class="text-right">TOTALES:</td>
                            <td class="text-right">{{ number_format($totales['produccion'], 2) }} KG</td>
                            <td class="text-right">{{ number_format($totales['venta'], 2) }} KG</td>
                            <td class="text-right">{{ number_format($totales['merma'], 2) }} KG</td>
                            <td class="text-right">{{ number_format($totales['diferencia'], 2) }} KG</td>
                            <td class="text-right">S/ {{ number_format($totales['utilidad_bruta'], 2) }}</td>
                            <td class="text-right">S/ {{ number_format($totales['ventas'], 2) }}</td>
                            <td class="text-right">S/ {{ number_format($totales['costo_merma'], 2) }}</td>
                            <td class="text-right">S/ {{ number_format($totales['costo_diferencia'], 2) }}</td>
                        </tr>
                    </tfoot>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        // Inicializar DataTable
        $('#dataTable').DataTable({
            "ordering": true,
            "language": {
                "search": "Buscar:",
                "lengthMenu": "Mostrar _MENU_ registros por página",
                "zeroRecords": "No se encontraron registros",
                "info": "Mostrando página _PAGE_ de _PAGES_",
                "infoEmpty": "No hay registros disponibles",
                "infoFiltered": "(filtrado de _MAX_ registros totales)",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            "dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                  "<'row'<'col-sm-12'tr>>" +
                  "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            "buttons": [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ]
        });
        
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
            labels: ['Ventas', 'Costo Merma', 'Costo Diferencia'],
            datasets: [{
                label: 'Distribución de costos',
                data: [
                    {{ $totales['ventas'] }},
                    {{ $totales['costo_merma'] }},
                    {{ $totales['costo_diferencia'] }}
                ],
                backgroundColor: [
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(255, 206, 86, 0.7)'
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)',
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
                        label += parseFloat(context.raw).toFixed(2) + ' KG';
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
        const cantidadesChart = new Chart(ctxCantidades, {
            type: 'pie',
            data: cantidadesData,
            options: {
                ...chartOptions,
                plugins: {
                    ...chartOptions.plugins,
                    tooltip: cantidadesTooltipCallback
                }
            }
        });
        
        // Crear gráfico de costos
        const ctxCostos = document.getElementById('costosChart').getContext('2d');
        const costosChart = new Chart(ctxCostos, {
            type: 'pie',
            data: costosData,
            options: {
                ...chartOptions,
                plugins: {
                    ...chartOptions.plugins,
                    tooltip: costosTooltipCallback
                }
            }
        });
    });
</script>
@endpush


