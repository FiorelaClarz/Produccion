<div class="instructivo-content">
    <h4 class="text-primary">{{ $instructivo->titulo }} <small class="text-muted">v{{ $instructivo->version }}</small></h4>
    <p class="text-muted mb-3">Receta: {{ $receta->nombre }}</p>
    
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body p-2">
                    <small class="text-muted">Rendimiento base:</small>
                    <p class="mb-0">{{ $receta->cant_rendimiento }} {{ $receta->uMedida->nombre }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body p-2">
                    <small class="text-muted">Total pedido:</small>
                    <p class="mb-0">{{ $cantidadProduccion }} {{ $receta->uMedida->nombre }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body p-2">
                    <small class="text-muted">Factor de ajuste:</small>
                    <p class="mb-0">{{ number_format($factor, 2) }}</p>
                    <small class="text-muted">({{ $cantidadEsperada }} {{ $receta->uMedida->nombre }})</small>
                </div>
            </div>
        </div>
    </div>
    
    <h5 class="text-primary mt-4 mb-3">Ingredientes Ajustados</h5>
    <div class="table-responsive">
        <table class="table table-sm table-bordered">
            <thead class="bg-light">
                <tr>
                    <th>Ingrediente</th>
                    <th class="text-center">Cantidad Base</th>
                    <th class="text-center">Factor</th>
                    <th class="text-center">Cantidad Requerida</th>
                    <th class="text-center">Unidad</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ingredientesAdaptados as $ingrediente)
                <tr>
                    <td>{{ $ingrediente['nombre'] }}</td>
                    <td class="text-center">{{ number_format($ingrediente['cantidad_base'], 2) }}</td>
                    <td class="text-center">{{ number_format($factor, 2) }}</td>
                    <td class="text-center">{{ number_format($ingrediente['cantidad'], 2) }}</td>
                    <td class="text-center">{{ $ingrediente['u_medida'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <h5 class="text-primary mt-4 mb-3">Procedimiento</h5>
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
                                    $ingrediente = collect($ingredientesAdaptados)->firstWhere('id', $ingId);
                                @endphp
                                @if($ingrediente)
                                <li>
                                    {{ $ingrediente['nombre'] }}: 
                                    {{ number_format($ingrediente['cantidad'], 2) }} 
                                    {{ $ingrediente['u_medida'] }}
                                    <small class="text-muted">({{ $ingrediente['cantidad_base'] }} × {{ number_format($factor, 2) }})</small>
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