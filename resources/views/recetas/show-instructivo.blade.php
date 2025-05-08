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
                    <p class="mb-0"><small>Cantidad producida: {{ $cantidadProduccion }} {{ $instructivo->receta->uMedida->nombre }}</small></p>
                    <p class="mb-0"><small>Factor de ajuste: {{ number_format($factor, 2) }}x</small></p>
                </div>
                
                <div class="card-body p-0">
                    <div class="row g-0">
                        <!-- Columna izquierda - Información -->
                        <div class="col-lg-4 bg-light p-3">
                            <div class="sticky-top" style="top: 20px;">
                                <h5 class="text-primary mb-3"><i class="fas fa-info-circle me-2"></i>Información</h5>
                                
                                <ul class="list-group list-group-flush small">
                                    <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
                                        <span class="text-muted">Rendimiento base:</span>
                                        <span class="fw-medium">{{ $receta->cant_rendimiento }} {{ $receta->uMedida->nombre }}</span>
                                    </li>
                                    <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
                                        <span class="text-muted">Cantidad producida:</span>
                                        <span class="fw-medium">{{ $cantidadProduccion }} {{ $receta->uMedida->nombre }}</span>
                                    </li>
                                    <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
                                        <span class="text-muted">Factor de ajuste:</span>
                                        <span class="fw-medium">{{ number_format($factor, 2) }}x</span>
                                    </li>
                                </ul>
                                
                                <h5 class="text-primary mt-4 mb-3"><i class="fas fa-utensils me-2"></i>Ingredientes Ajustados</h5>
                                
                                <div class="ingredients-scrollable small">
                                    <table class="table table-borderless table-sm">
                                        <thead>
                                            <tr>
                                                <th>Ingrediente</th>
                                                <th class="text-end">Base</th>
                                                <th class="text-end">Ajustado</th>
                                                <th class="text-end">Unidad</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($ingredientesAdaptados as $ingrediente)
                                            <tr class="border-bottom">
                                                <td class="ps-0">
                                                    <div class="fw-medium">{{ $ingrediente['nombre'] }}</div>
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format($ingrediente['cantidad_base'], 2) }}
                                                </td>
                                                <td class="text-end">
                                                    <strong>{{ number_format($ingrediente['cantidad'], 2) }}</strong>
                                                </td>
                                                <td class="pe-0 text-end">
                                                    {{ $ingrediente['u_medida'] }}
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
                                <div class="paso-instruction mb-4">
                                    <div class="d-flex align-items-start">
                                        <div class="me-3">
                                            <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                                                {{ $index + 1 }}
                                            </span>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="paso-contenido mb-2">
                                                {!! nl2br(e($paso['contenido'])) !!}
                                            </div>
                                            @if(!empty($paso['ingredientes']))
                                            <div class="ingredientes-paso small bg-light p-2 rounded">
                                                <strong>Ingredientes para este paso:</strong>
                                                <ul class="mb-0">
                                                    @foreach($paso['ingredientes'] as $ingId)
                                                        @php
                                                            $ingrediente = $ingredientesAdaptados->firstWhere('id', $ingId);
                                                        @endphp
                                                        @if($ingrediente)
                                                        <li>
                                                            {{ $ingrediente['nombre'] }}: 
                                                            {{ number_format($ingrediente['cantidad'], 2) }} 
                                                            {{ $ingrediente['u_medida'] }}
                                                        </li>
                                                        @endif
                                                    @endforeach
                                                </ul>
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
                    <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary">
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
    body {
        font-size: 0.9rem;
    }
    
    .paso-instruction {
        transition: all 0.2s ease;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 15px;
        border-left: 4px solid #4e73df;
    }
    
    .paso-instruction:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }
    
    .paso-contenido {
        line-height: 1.6;
        color: #495057;
    }
    
    .ingredients-scrollable {
        max-height: 300px;
        overflow-y: auto;
        padding-right: 8px;
    }
    
    .ingredientes-paso {
        background-color: #f8f9fa;
        border-left: 3px solid #6c757d;
    }
    
    .card {
        border-radius: 0.5rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
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
            font-style: normal !important;
        }
        
        .ingredients-scrollable {
            max-height: none !important;
            overflow: visible !important;
        }
        
        .paso-instruction {
            page-break-inside: avoid;
        }
    }
</style>
@endpush