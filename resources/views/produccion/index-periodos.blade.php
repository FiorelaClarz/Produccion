@extends('layouts.app')

@section('content')
<div class="container">
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

    <!-- Pestañas de períodos -->
    <div class="row mb-4">
        <div class="col-12">
            <ul class="nav nav-tabs" id="periodTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ $periodoActual === 'hoy' ? 'active' : '' }}" 
                       href="{{ route('produccion.periodos', ['periodo' => 'hoy', 'estado' => $estadoActual]) }}">
                        <i class="fas fa-calendar-day mr-2"></i>Hoy
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $periodoActual === 'ayer' ? 'active' : '' }}" 
                       href="{{ route('produccion.periodos', ['periodo' => 'ayer', 'estado' => $estadoActual]) }}">
                        <i class="fas fa-calendar-alt mr-2"></i>Ayer
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $periodoActual === 'semana' ? 'active' : '' }}" 
                       href="{{ route('produccion.periodos', ['periodo' => 'semana', 'estado' => $estadoActual]) }}">
                        <i class="fas fa-calendar-week mr-2"></i>Última Semana
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Pestañas de estados -->
    <div class="row mb-4">
        <div class="col-12">
            <ul class="nav nav-tabs" id="productionTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ $estadoActual === 'pendientes' ? 'active' : '' }}" 
                       href="{{ route('produccion.periodos', ['periodo' => $periodoActual, 'estado' => 'pendientes']) }}">
                        <i class="fas fa-clock mr-2"></i>Pendientes
                        <span class="badge badge-primary ml-2">{{ $totalPendientes }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $estadoActual === 'terminados' ? 'active' : '' }}" 
                       href="{{ route('produccion.periodos', ['periodo' => $periodoActual, 'estado' => 'terminados']) }}">
                        <i class="fas fa-check-circle mr-2"></i>Terminados
                        <span class="badge badge-success ml-2">{{ $totalTerminados }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $estadoActual === 'cancelados' ? 'active' : '' }}" 
                       href="{{ route('produccion.periodos', ['periodo' => $periodoActual, 'estado' => 'cancelados']) }}">
                        <i class="fas fa-times-circle mr-2"></i>Cancelados
                        <span class="badge badge-danger ml-2">{{ $totalCancelados }}</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Información del período -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="card-title">
                        <i class="fas fa-calendar mr-2"></i>
                        Período: {{ $fechaInicio->format('d/m/Y') }} - {{ $fechaFin->format('d/m/Y') }}
                    </h5>
                </div>
                <div class="col-md-6 text-right">
                    <span class="badge badge-info">
                        <i class="fas fa-filter mr-1"></i>
                        Mostrando: {{ ucfirst($estadoActual) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de producción -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-clipboard-list mr-2"></i>Producción
                </h6>
                <span class="badge badge-pill badge-primary">
                    {{ count($recetasAgrupadas) }} {{ count($recetasAgrupadas) === 1 ? 'pedido' : 'productos' }}
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Receta</th>
                            <th class="text-center">Cant. Pedido</th>
                            <th class="text-center">Unidad</th>
                            <th class="text-center">Cant. Esperada</th>
                            <th class="text-center">Cant. Producida</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Subtotal</th>
                            <th class="text-center">Costo Diseño</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Harina</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($recetasAgrupadas && count($recetasAgrupadas) > 0)
                            @foreach($recetasAgrupadas as $idReceta => $recetaData)
                                @php
                                    $receta = $recetaData['receta'];
                                    $pedidosNoPersonalizados = $recetaData['pedidos']->where('es_personalizado', false);
                                    $pedidosPersonalizados = $recetaData['pedidos']->where('es_personalizado', true);
                                    
                                    $cantidadNoPersonalizada = $pedidosNoPersonalizados->sum('cantidad');
                                    $cantidadEsperada = ($receta->id_areas == 1)
                                        ? $cantidadNoPersonalizada * $receta->constante_peso_lata
                                        : $cantidadNoPersonalizada;

                                    $subtotalReceta = 0;
                                    foreach ($receta->detalles as $detalle) {
                                        $subtotalReceta += $detalle->subtotal_receta * $cantidadEsperada;
                                    }

                                    $componenteHarina = $receta->detalles->first(function($item) {
                                        return $item->producto && stripos($item->producto->nombre, 'harina') !== false;
                                    });
                                    $cantHarina = $componenteHarina ? $componenteHarina->cantidad * $cantidadEsperada : 0;
                                @endphp

                                <!-- Fila principal -->
                                <tr class="production-item">
                                    <td>
                                        <strong>{{ $receta->producto->nombre ?? 'N/A' }}</strong>
                                        @if($pedidosPersonalizados->count() > 0)
                                            <span class="badge badge-warning ml-2">Contiene personalizado</span>
                                        @endif
                                    </td>
                                    <td>{{ $receta->nombre ?? 'N/A' }}</td>
                                    <td class="text-center">{{ number_format($cantidadNoPersonalizada, 2) }}</td>
                                    <td class="text-center">{{ $recetaData['id_u_medidas'] ?? 'N/A' }}</td>
                                    <td class="text-center">{{ number_format($cantidadEsperada, 2) }}</td>
                                    <td class="text-center">{{ number_format($recetaData['cantidad_producida_real'] ?? $cantidadEsperada, 2) }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-{{ $recetaData['estado_general'] === 'terminado' ? 'success' : ($recetaData['estado_general'] === 'cancelado' ? 'danger' : 'primary') }}">
                                            {{ ucfirst($recetaData['estado_general']) }}
                                        </span>
                                    </td>
                                    <td class="text-center">S/ {{ number_format($subtotalReceta, 2) }}</td>
                                    <td class="text-center">S/ {{ number_format($recetaData['costo_diseño'] ?? 0, 2) }}</td>
                                    <td class="text-center">S/ {{ number_format($subtotalReceta + ($recetaData['costo_diseño'] ?? 0), 2) }}</td>
                                    <td class="text-center">{{ number_format($cantHarina, 2) }} g</td>
                                    <td class="text-center">
                                        @if($receta->instructivo)
                                            <button type="button" class="btn btn-sm btn-outline-info"
                                                data-toggle="tooltip" title="Ver instructivo"
                                                onclick="cargarInstructivo({{ $receta->id_recetas }}, '{{ $recetaData['estado_general'] }}')">
                                                <i class="fas fa-book-open"></i>
                                            </button>
                                        @endif
                                        
                                        @if($pedidosPersonalizados->count() > 0)
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                data-toggle="modal" data-target="#detallesModal"
                                                onclick="mostrarDetallesPersonales({{ $idReceta }})">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>

                                <!-- Filas de pedidos personalizados -->
                                @if($pedidosPersonalizados->count() > 0)
                                    @foreach($pedidosPersonalizados as $pedido)
                                        @php
                                            $cantidadPersonalizada = $pedido->cantidad;
                                            $cantidadEsperadaPersonalizada = ($receta->id_areas == 1)
                                                ? $cantidadPersonalizada * $receta->constante_peso_lata
                                                : $cantidadPersonalizada;
                                            
                                            $subtotalPersonalizado = 0;
                                            foreach ($receta->detalles as $detalle) {
                                                $subtotalPersonalizado += $detalle->subtotal_receta * $cantidadEsperadaPersonalizada;
                                            }
                                            
                                            $harinaPersonalizada = $componenteHarina ? $componenteHarina->cantidad * $cantidadEsperadaPersonalizada : 0;
                                        @endphp

                                        <tr class="pedido-personalizado">
                                            <td colspan="2">
                                                <div class="d-flex align-items-center">
                                                    <strong class="mr-2">{{ $receta->producto->nombre ?? 'N/A' }}</strong>
                                                    <span class="badge badge-warning">Personalizado</span>
                                                </div>
                                                <small class="text-muted">Pedido #{{ $pedido->id_pedidos_det }}</small>
                                            </td>
                                            <td class="text-center">{{ number_format($cantidadPersonalizada, 2) }}</td>
                                            <td class="text-center">{{ $pedido->uMedida->nombre ?? 'N/A' }}</td>
                                            <td class="text-center">{{ number_format($cantidadEsperadaPersonalizada, 2) }}</td>
                                            <td class="text-center">{{ number_format($pedido->cantidad_producida_real ?? $cantidadPersonalizada, 2) }}</td>
                                            <td class="text-center">
                                                <span class="badge badge-{{ $pedido->id_estados == 4 ? 'success' : ($pedido->id_estados == 5 ? 'danger' : 'primary') }}">
                                                    {{ $pedido->id_estados == 4 ? 'Terminado' : ($pedido->id_estados == 5 ? 'Cancelado' : 'En proceso') }}
                                                </span>
                                            </td>
                                            <td class="text-center">S/ {{ number_format($subtotalPersonalizado, 2) }}</td>
                                            <td class="text-center">S/ {{ number_format($pedido->costo_diseño ?? 0, 2) }}</td>
                                            <td class="text-center">S/ {{ number_format($subtotalPersonalizado + ($pedido->costo_diseño ?? 0), 2) }}</td>
                                            <td class="text-center">{{ number_format($harinaPersonalizada, 2) }} g</td>
                                            <td class="text-center">
                                                @if($pedido->foto_referencial)
                                                    <button type="button" class="btn btn-sm btn-outline-primary view-image-btn"
                                                        data-image-url="{{ asset('storage/' . $pedido->foto_referencial) }}">
                                                        <i class="fas fa-image"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            @endforeach
                        @else
                            <tr>
                                <td colspan="12" class="text-center text-muted py-4">
                                    <i class="fas fa-info-circle fa-3x mb-3"></i>
                                    <h4>No hay pedidos para el período seleccionado</h4>
                                    <p class="text-muted">Los pedidos aparecerán aquí cuando sean asignados a tu área.</p>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

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
            <div class="modal-body" id="instructivoContent">
                <!-- Contenido cargado via AJAX -->
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

<!-- Modal para ver imágenes -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="imageModalLabel">Imagen de Referencia</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" class="img-fluid" alt="Imagen de referencia del pedido" style="max-height: 70vh;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para detalles de pedidos personalizados -->
<div class="modal fade" id="detallesModal" tabindex="-1" aria-labelledby="detallesModalLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="detallesModalLabel">Detalles de Pedidos Personalizados</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detallesModalContent">
                <!-- Contenido se llenará dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<style>
    .nav-tabs {
        border-bottom: 2px solid #dee2e6;
    }

    .nav-tabs .nav-link {
        border: none;
        color: #6c757d;
        font-weight: 600;
        padding: 12px 20px;
        transition: all 0.3s;
    }

    .nav-tabs .nav-link:hover {
        border: none;
        color: #4e73df;
    }

    .nav-tabs .nav-link.active {
        color: #4e73df;
        background-color: transparent;
        border-bottom: 3px solid #4e73df;
    }

    .nav-tabs .nav-link .badge {
        font-size: 0.7rem;
        position: relative;
        top: -1px;
    }

    .production-item {
        background-color: #fff;
    }

    .pedido-personalizado {
        background-color: #fff8e1;
    }

    .badge-warning {
        background-color: #ffc107;
        color: #000;
    }

    .view-image-btn {
        transition: all 0.3s;
    }

    .view-image-btn:hover {
        transform: scale(1.1);
    }
</style>

<script>
function cargarInstructivo(idReceta, estado) {
    const modal = $('#instructivoModal');
    
    $('#instructivoContent').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Cargando...</span>
            </div>
            <p class="mt-3">Cargando instructivo...</p>
        </div>
    `);
    
    modal.modal('show');

    $.ajax({
        url: "{{ route('recetas.show-instructivo') }}",
        type: 'GET',
        data: { 
            id_receta: idReceta, 
            estado: estado || 'pendiente'
        },
        success: function(data) {
            $('#instructivoContent').html(data);
        },
        error: function(xhr) {
            $('#instructivoContent').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> Error al cargar el instructivo
                </div>
            `);
        }
    });
}

function mostrarDetallesPersonales(idReceta) {
    const recetaData = {!! json_encode($recetasAgrupadas) !!}[idReceta];
    
    if (!recetaData) {
        console.error('No se encontraron datos para la receta', idReceta);
        return;
    }

    const pedidosPersonalizados = recetaData.pedidos.filter(p => p.es_personalizado);
    
    if (pedidosPersonalizados.length === 0) {
        console.error('No hay pedidos personalizados para esta receta', idReceta);
        return;
    }

    let html = '<div class="row">';
    
    pedidosPersonalizados.forEach((pedido, index) => {
        const imagenUrl = pedido.foto_referencial 
            ? '{{ asset("storage") }}/' + pedido.foto_referencial.replace('pedidos/', 'pedidos/')
            : null;
        
        html += `
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Pedido #${pedido.id_pedidos_det}</h5>
                    </div>
                    <div class="card-body">
                        <h6>Descripción:</h6>
                        <p class="text-muted">${pedido.descripcion || 'Sin descripción'}</p>
                        
                        ${imagenUrl ? `
                        <div class="mt-3">
                            <h6>Imagen de referencia:</h6>
                            <img src="${imagenUrl}" class="img-fluid rounded border" alt="Imagen de referencia" style="max-height: 200px; cursor: pointer;"
                                 onclick="mostrarImagenModal('${imagenUrl}')">
                        </div>
                        ` : ''}
                    </div>
                    <div class="card-footer bg-white">
                        <small class="text-muted">Cantidad: ${pedido.cantidad}</small>
                    </div>
                </div>
            </div>
        `;
        
        if ((index + 1) % 2 === 0) {
            html += '</div><div class="row">';
        }
    });
    
    html += '</div>';
    document.getElementById('detallesModalContent').innerHTML = html;
}

function mostrarImagenModal(imageUrl) {
    $('#modalImage').attr('src', imageUrl);
    $('#imageModal').modal('show');
}

// Inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Configuración de tooltips de Bootstrap
    if (typeof $.fn.tooltip === 'function') {
        $('[data-toggle="tooltip"]').tooltip();
    }
    
    // Mostrar imágenes al hacer clic
    $(document).on('click', '.view-image-btn', function() {
        const imageUrl = $(this).data('image-url');
        $('#modalImage').attr('src', imageUrl);
        $('#imageModal').modal('show');
    });
});
</script>
@endsection 