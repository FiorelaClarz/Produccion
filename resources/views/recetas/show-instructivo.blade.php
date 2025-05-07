@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="mb-0">{{ $instructivo->titulo }}</h3>
                        <span class="badge bg-light text-primary">Versión {{ $instructivo->version }}</span>
                    </div>
                    <p class="mb-0"><small>Receta: {{ $instructivo->receta->nombre }}</small></p>
                </div>
                
                <div class="card-body p-0">
                    <div class="row g-0">
                        <!-- Columna izquierda - Información -->
                        <div class="col-lg-4 bg-light p-3">
                            <div class="sticky-top" style="top: 20px;">
                                <h5 class="text-primary mb-3"><i class="fas fa-info-circle me-2"></i>Información</h5>
                                
                                <ul class="list-group list-group-flush small">
                                    <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
                                        <span class="text-muted">Rendimiento:</span>
                                        <span class="fw-medium">{{ $instructivo->receta->cant_rendimiento }} {{ $instructivo->receta->uMedida->nombre }}</span>
                                    </li>
                                    <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
                                        <span class="text-muted">Área:</span>
                                        <span class="fw-medium">{{ $instructivo->receta->area->nombre }}</span>
                                    </li>
                                    <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
                                        <span class="text-muted">Constante crecimiento:</span>
                                        <span class="fw-medium">{{ $instructivo->receta->constante_crecimiento }}</span>
                                    </li>
                                    <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
                                        <span class="text-muted">Constante peso/lata:</span>
                                        <span class="fw-medium">{{ $instructivo->receta->constante_peso_lata }}</span>
                                    </li>
                                    <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
                                        <span class="text-muted">Fecha creación:</span>
                                        <span class="fw-medium">{{ $instructivo->created_at->format('d/m/Y H:i') }}</span>
                                    </li>
                                </ul>
                                
                                <h5 class="text-primary mt-4 mb-3"><i class="fas fa-utensils me-2"></i>Ingredientes</h5>
                                
                                <div class="ingredients-scrollable small">
                                    <table class="table table-borderless table-sm">
                                        <tbody>
                                            @foreach($instructivo->receta->detalles as $detalle)
                                            <tr class="border-bottom">
                                                <td class="ps-0">
                                                    <div class="fw-medium">{{ $detalle->producto->nombre }}</div>
                                                    <div class="text-muted">{{ $detalle->cantidad }} {{ $detalle->uMedida->nombre }}</div>
                                                </td>
                                                <td class="pe-0 text-end">
                                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2 py-1">
                                                        ${{ number_format($detalle->costo_unitario, 2) }}
                                                    </span>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Columna derecha - Instrucciones -->
                        <div class="col-lg-8 p-4">
                            <h5 class="text-primary mb-4"><i class="fas fa-list-ol me-2"></i>Procedimiento</h5>
                            
                            <div class="pasos-container">
                                @foreach($instructivo->instrucciones as $index => $paso)
                                <div class="paso-instruction mb-3 pb-3 border-bottom">
                                    <div class="d-flex align-items-start">
                                        <span class="badge bg-primary rounded-circle me-3 mt-1" style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center;">
                                            {{ $index + 1 }}
                                        </span>
                                        <div class="flex-grow-1">
                                            <div class="paso-contenido fst-italic text-muted">
                                                {!! nl2br(e($paso['contenido'])) !!}
                                            </div>
                                            
                                            @if(isset($paso['ingredientes']) && count($paso['ingredientes']) > 0)
                                            <div class="ingredientes-paso mt-2">
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach($paso['ingredientes'] as $ing)
                                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-2">
                                                        <small>{{ $ing['nombre'] }} ({{ $ing['cantidad'] }} {{ $ing['u_medida'] }})</small>
                                                    </span>
                                                    @endforeach
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer bg-white border-top text-end py-3">
                    <a href="{{ route('recetas.show', $instructivo->id_recetas) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Volver
                    </a>
                    
                    
                    <button class="btn btn-sm btn-outline-primary" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Imprimir
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Estilo general compacto */
    body {
        font-size: 0.9rem;
    }
    
    /* Estilo para los pasos - Compacto y elegante */
    .paso-instruction {
        transition: all 0.2s ease;
    }
    
    .paso-instruction:hover {
        background-color: rgba(0, 123, 255, 0.03);
    }
    
    .paso-contenido {
        line-height: 1.5;
        font-style: italic;
        color: #495057;
    }
    
    /* Estilo para el área de ingredientes */
    .ingredients-scrollable {
        max-height: 300px;
        overflow-y: auto;
        padding-right: 8px;
    }
    
    /* Estilo para las tarjetas */
    .card {
        border-radius: 0.5rem;
    }
    
    /* Responsividad */
    @media (max-width: 991.98px) {
        .sticky-top {
            position: static !important;
        }
        
        .ingredients-scrollable {
            max-height: 200px;
        }
    }
    
    @media (max-width: 767.98px) {
        .col-lg-4, .col-lg-8 {
            padding: 1rem !important;
        }
        
        .bg-light {
            background-color: transparent !important;
        }
    }
    
    /* Estilos para impresión */
    @media print {
        body {
            font-size: 10pt;
        }
        
        .card-header {
            background-color: #fff !important;
            color: #000 !important;
            border-bottom: 1px solid #ddd !important;
        }
        
        .btn {
            display: none !important;
        }
        
        .col-lg-4 {
            background-color: transparent !important;
        }
        
        .paso-contenido {
            font-style: italic !important;
        }
        
        .ingredients-scrollable {
            max-height: none !important;
            overflow: visible !important;
        }
    }
</style>
@endpush