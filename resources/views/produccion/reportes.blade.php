@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Reportes de Producción</h1>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filtros</h6>
        </div>
        <div class="card-body">
            <x-filtros-fecha 
                :fechaInicio="$fechaInicio" 
                :fechaFin="$fechaFin" 
                ruta="{{ route('produccion.reportes') }}" 
            />
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <x-grafico-produccion 
                titulo="Producción por Área" 
                tipo="bar" 
                :datos="$datosGraficos" 
            />
        </div>
    </div>
    
    <div class="card shadow mb-4 mt-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Resumen de Producción</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Área</th>
                            <th>Total Producido</th>
                            <th>Total Esperado</th>
                            <th>Diferencia</th>
                            <th>Eficiencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($datosGraficos->groupBy('area') as $area => $items)
                        @php
                            $totalProducido = $items->sum('total_producido');
                            $totalEsperado = $items->sum('total_esperado');
                            $diferencia = $totalProducido - $totalEsperado;
                            $eficiencia = $totalEsperado > 0 ? ($totalProducido / $totalEsperado) * 100 : 0;
                        @endphp
                        <tr>
                            <td>{{ $area }}</td>
                            <td>{{ number_format($totalProducido, 2) }}</td>
                            <td>{{ number_format($totalEsperado, 2) }}</td>
                            <td class="{{ $diferencia >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($diferencia, 2) }}
                            </td>
                            <td>
                                <div class="progress">
                                    <div class="progress-bar {{ $eficiencia >= 100 ? 'bg-success' : 'bg-warning' }}" 
                                         role="progressbar" 
                                         style="width: {{ min($eficiencia, 100) }}%" 
                                         aria-valuenow="{{ $eficiencia }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        {{ number_format($eficiencia, 2) }}%
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.progress {
    height: 20px;
    background-color: #f8f9fa;
    border-radius: 4px;
}
.progress-bar {
    line-height: 20px;
    font-size: 12px;
}
</style>
@endpush