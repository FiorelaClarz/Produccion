<div class="card shadow mb-4 production-card">
    <div class="card-header py-3 production-card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-clipboard-list mr-2"></i>Pedidos para Producción - {{ now()->format('d/m/Y') }}
            </h6>
            <div>
                <div class="btn-group btn-group-toggle" data-toggle="buttons" id="statusFilter">
                    <label class="btn btn-outline-secondary active">
                        <input type="radio" name="status" value="all" checked> Todos
                    </label>
                    <label class="btn btn-outline-primary">
                        <input type="radio" name="status" value="pending"> Pendientes
                    </label>
                    <label class="btn btn-outline-info">
                        <input type="radio" name="status" value="in_process"> En Proceso
                    </label>
                    <label class="btn btn-outline-success">
                        <input type="radio" name="status" value="completed"> Terminados
                    </label>
                    <label class="btn btn-outline-danger">
                        <input type="radio" name="status" value="cancelled"> Cancelados
                    </label>
                </div>
            </div>
            <span class="badge badge-pill badge-primary" style="color: black;">
                {{ count($recetasAgrupadas) }} {{ count($recetasAgrupadas) === 1 ? 'pedido' : 'productos' }}
            </span>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('produccion.guardar-personal') }}" method="POST" id="produccionForm">
            @csrf

            @if($equipoActivo)
            <input type="hidden" name="id_equipos" value="{{ $equipoActivo->id_equipos_cab }}">
            @endif

            <div class="table-responsive">
                <table class="table table-hover production-table" id="todayTable" width="100%" cellspacing="0">
                    <thead class="production-table-header">
                        <tr>
                            <th>Producto</th>
                            <th>Receta</th>
                            <th class="text-center">Cant. Pedido</th>
                            <th class="text-center">Unidad Pedido</th>
                            <th class="text-center">Cant. Esperada</th>
                            <th class="text-center">Cant. Producida</th>
                            <th class="text-center">Unidad Producción</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Subtotal</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Harina</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($recetasAgrupadas && count($recetasAgrupadas) > 0)
                            @php $recetaCounter = 1; @endphp
                            @foreach($recetasAgrupadas as $idReceta => $recetaData)
                                @php
                                    $receta = $recetaData['receta'] ?? null;
                                    if (!$receta) continue;
                                    
                                    // Resto de tu lógica para calcular valores...
                                @endphp

                                <!-- Fila principal -->
                                <tr class="production-item {{ $recetaData['es_personalizado'] ? 'personalizado-row' : '' }}" 
                                    id="row-{{ $idReceta }}"
                                    data-status="{{ $recetaData['status'] ?? 'pending' }}">
                                    <!-- Contenido de la fila como lo tenías antes -->
                                </tr>

                                <!-- Filas de pedidos personalizados -->
                                @if($pedidosPersonalizados->count() > 0)
                                    <!-- Contenido de pedidos personalizados -->
                                @endif
                                
                                @php $recetaCounter++; @endphp
                            @endforeach
                        @else
                            <tr>
                                <td colspan="12" class="text-center text-muted py-4 no-orders">
                                    <i class="fas fa-info-circle fa-3x mb-3"></i>
                                    <h4>No hay pedidos para producción hoy</h4>
                                    <p class="text-muted">Los pedidos aparecerán aquí cuando sean asignados a tu área.</p>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            @if($recetasAgrupadas && count($recetasAgrupadas) > 0 && $equipoActivo)
            <div class="form-group mt-4 text-center">
                <button type="submit" class="btn btn-primary btn-lg btn-save">
                    <i class="fas fa-save mr-2"></i> Guardar Producción
                </button>
            </div>
            @endif
        </form>
    </div>
</div>