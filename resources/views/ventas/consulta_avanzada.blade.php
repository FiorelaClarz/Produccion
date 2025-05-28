@extends('layouts.app')

@section('title', 'Consulta Avanzada API de Ventas')

@section('styles')
<!-- DataTables CSS -->
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<style>
    .api-container {
        background-color: #f8f9fa;
        border-radius: 5px;
        padding: 20px;
    }
    .param-row {
        background-color: #fff;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        margin-bottom: 8px;
        padding: 10px;
    }
    .param-row:hover {
        border-color: #adb5bd;
    }
    .param-check {
        margin-right: 10px;
    }
    .response-container {
        background-color: #2d2d2d;
        color: #f8f9fa;
        border-radius: 5px;
        padding: 15px;
        font-family: monospace;
        max-height: 400px;
        overflow-y: auto;
    }
    .json-key {
        color: #f92672;
    }
    .json-value {
        color: #a6e22e;
    }
    .json-string {
        color: #e6db74;
    }
    .json-number {
        color: #ae81ff;
    }
    .tab-content {
        border-left: 1px solid #dee2e6;
        border-right: 1px solid #dee2e6;
        border-bottom: 1px solid #dee2e6;
        padding: 20px;
        border-radius: 0 0 5px 5px;
    }
    pre {
        background-color: #2d2d2d;
        color: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        overflow-x: auto;
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
                        <i class="fas fa-code me-2"></i>Consulta Avanzada API de Ventas
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Pestañas de navegación -->
                    <ul class="nav nav-tabs" id="apiTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="request-tab" data-bs-toggle="tab" data-bs-target="#request" type="button" role="tab" aria-selected="true">
                                <i class="fas fa-paper-plane me-1"></i>Solicitud
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="response-tab" data-bs-toggle="tab" data-bs-target="#response" type="button" role="tab" aria-selected="false">
                                <i class="fas fa-reply me-1"></i>Respuesta
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="code-tab" data-bs-toggle="tab" data-bs-target="#code" type="button" role="tab" aria-selected="false">
                                <i class="fas fa-code me-1"></i>Código
                            </button>
                        </li>
                    </ul>
                    
                    <!-- Contenido de las pestañas -->
                    <div class="tab-content" id="apiTabsContent">
                        <!-- Pestaña de Solicitud -->
                        <div class="tab-pane fade show active" id="request" role="tabpanel">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="input-group">
                                        <span class="input-group-text">URL</span>
                                        <input type="text" class="form-control" id="apiUrl" value="http://64.227.4.218/stargroup/middleware/awsSalesStore.php" readonly>
                                    </div>
                                </div>
                            </div>
                            
                            <h6 class="mb-3"><i class="fas fa-list me-2"></i>Parámetros de consulta</h6>
                            <div class="api-container">
                                <form id="apiQueryForm">
                                    <div class="param-row">
                                        <div class="row align-items-center">
                                            <div class="col-md-1">
                                                <div class="form-check">
                                                    <input class="form-check-input param-check" type="checkbox" id="check_accion" checked>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text" class="form-control" value="accion" readonly>
                                            </div>
                                            <div class="col-md-6">
                                                <input type="text" class="form-control" id="value_accion" value="MostrarByIdDate">
                                            </div>
                                            <div class="col-md-2">
                                                <span class="text-muted">Identificador del controlador</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="param-row">
                                        <div class="row align-items-center">
                                            <div class="col-md-1">
                                                <div class="form-check">
                                                    <input class="form-check-input param-check" type="checkbox" id="check_store_code" checked>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text" class="form-control" value="store_code" readonly>
                                            </div>
                                            <div class="col-md-6">
                                                <select class="form-select" id="value_store_code">
                                                    <option value="T01">T01</option>
                                                    <option value="T02">T02</option>
                                                    <option value="T03" selected>T03</option>
                                                    <option value="T04">T04</option>
                                                    <option value="T05">T05</option>
                                                    <option value="T06">T06</option>
                                                    <option value="T07">T07</option>
                                                    <option value="T08">T08</option>
                                                    <option value="T09">T09</option>
                                                    <option value="T10">T10</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <span class="text-muted">Código de la Tienda</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="param-row">
                                        <div class="row align-items-center">
                                            <div class="col-md-1">
                                                <div class="form-check">
                                                    <input class="form-check-input param-check" type="checkbox" id="check_id_item" checked>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text" class="form-control" value="id_item" readonly>
                                            </div>
                                            <div class="col-md-6">
                                                <input type="text" class="form-control" id="value_id_item" value="250704">
                                            </div>
                                            <div class="col-md-2">
                                                <span class="text-muted">ID del producto</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="param-row">
                                        <div class="row align-items-center">
                                            <div class="col-md-1">
                                                <div class="form-check">
                                                    <input class="form-check-input param-check" type="checkbox" id="check_fecha1" checked>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text" class="form-control" value="fecha1" readonly>
                                            </div>
                                            <div class="col-md-6">
                                                <input type="date" class="form-control" id="value_fecha1" value="2025-01-28">
                                            </div>
                                            <div class="col-md-2">
                                                <span class="text-muted">Fecha inicial (ISO 8601)</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="param-row">
                                        <div class="row align-items-center">
                                            <div class="col-md-1">
                                                <div class="form-check">
                                                    <input class="form-check-input param-check" type="checkbox" id="check_fecha2" checked>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text" class="form-control" value="fecha2" readonly>
                                            </div>
                                            <div class="col-md-6">
                                                <input type="date" class="form-control" id="value_fecha2" value="2025-03-28">
                                            </div>
                                            <div class="col-md-2">
                                                <span class="text-muted">Fecha final (ISO 8601)</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-12 text-end">
                                            <button type="button" class="btn btn-secondary me-2" id="btnResetForm">
                                                <i class="fas fa-sync-alt me-1"></i>Restablecer
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-paper-plane me-1"></i>Enviar solicitud
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Pestaña de Respuesta -->
                        <div class="tab-pane fade" id="response" role="tabpanel">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0">Estado: <span id="responseStatus" class="badge bg-secondary">Ninguno</span></h6>
                                        <div>
                                            <button class="btn btn-sm btn-outline-secondary" id="btnFormatJson">
                                                <i class="fas fa-indent me-1"></i>Formatear JSON
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary ms-2" id="btnCopyJson">
                                                <i class="fas fa-copy me-1"></i>Copiar
                                            </button>
                                        </div>
                                    </div>
                                    <div class="response-container">
                                        <pre id="responseBody">// La respuesta se mostrará aquí después de enviar la solicitud</pre>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Tabla de datos -->
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <h6 class="mb-3"><i class="fas fa-table me-2"></i>Datos en formato tabla</h6>
                                    <div class="table-responsive">
                                        <table id="apiResultsTable" class="table table-striped table-bordered">
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
                        
                        <!-- Pestaña de Código -->
                        <div class="tab-pane fade" id="code" role="tabpanel">
                            <h6 class="mb-3">Código JavaScript para consumir esta API:</h6>
                            <pre><code>// Ejemplo con JavaScript puro (Fetch API)
const apiCall = async () => {
    const params = new URLSearchParams({
        accion: 'MostrarByIdDate',
        store_code: 'T03',
        id_item: '250704',
        fecha1: '2025-01-28',
        fecha2: '2025-03-28'
    });

    const url = `http://64.227.4.218/stargroup/middleware/awsSalesStore.php?${params}`;
    
    try {
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Authorization': 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...',
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        console.log(data);
    } catch (error) {
        console.error('Error:', error);
    }
};

// Llamar a la función
apiCall();</code></pre>
                        
                            <h6 class="mt-4 mb-3">Código PHP para consumir esta API:</h6>
                            <pre><code>// Ejemplo con PHP (usando cURL)
&lt;?php
$url = 'http://64.227.4.218/stargroup/middleware/awsSalesStore.php';

$params = [
    'accion' => 'MostrarByIdDate',
    'store_code' => 'T03',
    'id_item' => '250704',
    'fecha1' => '2025-01-28',
    'fecha2' => '2025-03-28'
];

// Construir URL con parámetros
$url = $url . '?' . http_build_query($params);

// Inicializar cURL
$ch = curl_init();

// Configurar opciones de cURL
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...',
    'Content-Type: application/json'
]);

// Ejecutar solicitud
$response = curl_exec($ch);

// Verificar si hubo errores
if (curl_errno($ch)) {
    echo 'Error cURL: ' . curl_error($ch);
} else {
    // Convertir respuesta JSON a array de PHP
    $data = json_decode($response, true);
    print_r($data);
}

// Cerrar sesión cURL
curl_close($ch);
?&gt;</code></pre>
                        </div>
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
                <h5 class="mb-0">Consultando API, por favor espere...</h5>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        // Inicializar DataTable
        const apiResultsTable = $('#apiResultsTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
            },
            order: [[1, 'desc']], // Ordenar por fecha descendente
            columns: [
                { data: 'store_code' },
                { 
                    data: 'sale_date',
                    render: function(data) {
                        return new Date(data).toLocaleDateString('es-PE');
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
            ]
        });

        // Formatear JSON en la respuesta
        function formatJSON(json) {
            if (typeof json === 'string') {
                json = JSON.parse(json);
            }
            return JSON.stringify(json, null, 4);
        }

        // Resaltar sintaxis JSON
        function highlightJSON(json) {
            if (!json) return '';
            
            // Reemplazar comillas, claves y valores con clases de color
            return json
                .replace(/"([^"]+)":/g, '<span class="json-key">"$1"</span>:')
                .replace(/"([^"]+)"(?!:)/g, '<span class="json-string">"$1"</span>')
                .replace(/\b(\d+)(?!":)\b/g, '<span class="json-number">$1</span>');
        }

        // Evento de envío del formulario
        $('#apiQueryForm').on('submit', function(e) {
            e.preventDefault();
            
            // Mostrar modal de carga
            const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
            loadingModal.show();
            
            // Construir parámetros de consulta
            const params = {};
            
            // Solo incluir parámetros marcados
            if ($('#check_accion').is(':checked')) params.accion = $('#value_accion').val();
            if ($('#check_store_code').is(':checked')) params.store_code = $('#value_store_code').val();
            if ($('#check_id_item').is(':checked')) params.id_item = $('#value_id_item').val();
            if ($('#check_fecha1').is(':checked')) params.fecha1 = $('#value_fecha1').val();
            if ($('#check_fecha2').is(':checked')) params.fecha2 = $('#value_fecha2').val();
            
            // Realizar la consulta a través del proxy
            $.ajax({
                url: '{{ route("ventas.proxy") }}',
                type: 'GET',
                dataType: 'json',
                data: params,
                success: function(response) {
                    // Ocultar modal de carga
                    loadingModal.hide();
                    
                    // Cambiar a la pestaña de respuesta
                    $('#response-tab').tab('show');
                    
                    // Actualizar estado de la respuesta
                    $('#responseStatus').removeClass().addClass('badge bg-success').text('200 OK');
                    
                    // Formatear y mostrar respuesta JSON
                    let formattedJSON;
                    let dataToShow;
                    
                    // Determinar si la respuesta tiene metadatos o es un array directo
                    if (response && response.meta && response.data) {
                        dataToShow = response.data;
                        formattedJSON = formatJSON(response);
                    } else {
                        dataToShow = response;
                        formattedJSON = formatJSON(response);
                    }
                    
                    // Mostrar respuesta con resaltado de sintaxis
                    $('#responseBody').html(highlightJSON(formattedJSON));
                    
                    // Actualizar tabla de resultados
                    apiResultsTable.clear().rows.add(dataToShow).draw();
                },
                error: function(xhr, status, error) {
                    // Ocultar modal de carga
                    loadingModal.hide();
                    
                    // Cambiar a la pestaña de respuesta
                    $('#response-tab').tab('show');
                    
                    // Actualizar estado de la respuesta
                    const statusCode = xhr.status || 500;
                    $('#responseStatus').removeClass().addClass('badge bg-danger').text(statusCode + ' Error');
                    
                    // Mostrar mensaje de error
                    let errorMessage = '';
                    try {
                        const errorObj = JSON.parse(xhr.responseText);
                        errorMessage = errorObj.error || error;
                    } catch (e) {
                        errorMessage = error || 'Error desconocido';
                    }
                    
                    $('#responseBody').text(errorMessage);
                    
                    // Limpiar tabla de resultados
                    apiResultsTable.clear().draw();
                }
            });
        });
        
        // Evento para restablecer formulario
        $('#btnResetForm').on('click', function() {
            // Restablecer valores predeterminados
            $('#value_accion').val('MostrarByIdDate');
            $('#value_store_code').val('T03');
            $('#value_id_item').val('250704');
            $('#value_fecha1').val('2025-01-28');
            $('#value_fecha2').val('2025-03-28');
            
            // Marcar todas las casillas
            $('.param-check').prop('checked', true);
        });
        
        // Evento para formatear JSON
        $('#btnFormatJson').on('click', function() {
            try {
                const content = $('#responseBody').text();
                const jsonObj = JSON.parse(content);
                const formatted = formatJSON(jsonObj);
                $('#responseBody').html(highlightJSON(formatted));
            } catch (e) {
                console.error('Error al formatear JSON:', e);
            }
        });
        
        // Evento para copiar JSON
        $('#btnCopyJson').on('click', function() {
            const content = $('#responseBody').text();
            navigator.clipboard.writeText(content)
                .then(() => {
                    const $btn = $(this);
                    $btn.html('<i class="fas fa-check me-1"></i>Copiado');
                    setTimeout(() => {
                        $btn.html('<i class="fas fa-copy me-1"></i>Copiar');
                    }, 2000);
                })
                .catch(err => {
                    console.error('Error al copiar: ', err);
                });
        });
    });
</script>
@endpush
