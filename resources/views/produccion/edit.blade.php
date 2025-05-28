@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Editar Producción #{{ $produccion->id_produccion_cab }}</h1>
    
    <!-- Mensaje de éxito o error -->
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
    
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Datos Generales de Producción</h6>
            <a href="{{ route('produccion.show', $produccion->id_produccion_cab) }}" class="btn btn-sm btn-info">
                <i class="fas fa-eye"></i> Ver Detalles
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('produccion.update', $produccion->id_produccion_cab) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_equipos">Equipo</label>
                            <select name="id_equipos" id="id_equipos" class="form-control" required>
                                <option value="">Seleccione un equipo</option>
                                @foreach($equipos as $equipo)
                                <option value="{{ $equipo->id_equipos_cab }}" 
                                    {{ $produccion->id_equipos == $equipo->id_equipos_cab ? 'selected' : '' }}>
                                    {{ $equipo->nombre }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_turnos">Turno</label>
                            <select name="id_turnos" id="id_turnos" class="form-control" required>
                                <option value="">Seleccione un turno</option>
                                @foreach($turnos as $turno)
                                <option value="{{ $turno->id_turnos }}" 
                                    {{ $produccion->id_turnos == $turno->id_turnos ? 'selected' : '' }}>
                                    {{ $turno->nombre }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="fecha">Fecha</label>
                            <input type="date" name="fecha" id="fecha" class="form-control" 
                                   value="{{ old('fecha', $produccion->fecha) }}" required>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="hora">Hora</label>
                            <input type="time" name="hora" id="hora" class="form-control" 
                                   value="{{ old('hora', $produccion->hora) }}" required>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="doc_interno">Documento Interno</label>
                            <input type="text" name="doc_interno" id="doc_interno" class="form-control" 
                                   value="{{ old('doc_interno', $produccion->doc_interno) }}">
                        </div>
                    </div>
                </div>
                
                <div class="form-group mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Actualizar Datos Generales
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Sección para editar detalles de producción -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Detalles de Producción</h6>
        </div>
        <div class="card-body">
            <!-- Tabla de detalles de producción -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad Pedido</th>
                            <th>Cantidad Esperada</th>
                            <th>Cantidad Real</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($produccion->produccionesDetalle as $detalle)
                        <tr>
                            <td>
                                {{ $detalle->recetaCabecera->producto->nombre ?? 'No disponible' }}
                                @if($detalle->es_personalizado)
                                <span class="badge badge-info">Personalizado</span>
                                @endif
                            </td>
                            <td>{{ number_format($detalle->cantidad_pedido, 2) }}</td>
                            <td>{{ number_format($detalle->cantidad_esperada, 2) }}</td>
                            <td>{{ number_format($detalle->cantidad_producida_real, 2) }}</td>
                            <td>
                                @if($detalle->es_cancelado)
                                    <span class="badge badge-danger">Cancelado</span>
                                @elseif($detalle->es_terminado)
                                    <span class="badge badge-success">Terminado</span>
                                @elseif($detalle->es_iniciado)
                                    <span class="badge badge-warning">En Proceso</span>
                                @else
                                    <span class="badge badge-secondary">Pendiente</span>
                                @endif
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary" 
                                        data-toggle="modal" 
                                        data-target="#editarDetalle{{ $detalle->id_produccion_det }}">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                            </td>
                        </tr>
                        
                        <!-- Modal para editar detalle -->
                        <div class="modal fade" id="editarDetalle{{ $detalle->id_produccion_det }}" tabindex="-1" role="dialog" aria-labelledby="modalLabel{{ $detalle->id_produccion_det }}" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <form action="{{ route('produccion.updateDetalle', $detalle->id_produccion_det) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title" id="modalLabel{{ $detalle->id_produccion_det }}">
                                                Editar Detalle de Producción: {{ $detalle->recetaCabecera->producto->nombre ?? 'Producto' }}
                                            </h5>
                                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <!-- Campos ocultos para identificación -->
                                            <input type="hidden" name="id_produccion_cab" value="{{ $produccion->id_produccion_cab }}">
                                            
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="cantidad_producida_real">Cantidad Producida Real:</label>
                                                        <input type="number" step="0.01" min="0" name="cantidad_producida_real" 
                                                               class="form-control" value="{{ $detalle->cantidad_producida_real }}" 
                                                               {{ $detalle->es_cancelado ? 'readonly' : '' }}>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Estado:</label>
                                                        <div class="custom-control custom-checkbox mb-2">
                                                            <input type="checkbox" class="custom-control-input estado-checkbox" 
                                                                   id="es_iniciado{{ $detalle->id_produccion_det }}" 
                                                                   name="es_iniciado" value="1" 
                                                                   {{ $detalle->es_iniciado ? 'checked' : '' }}
                                                                   data-detalle-id="{{ $detalle->id_produccion_det }}"
                                                                   data-tipo="iniciado">
                                                            <label class="custom-control-label" for="es_iniciado{{ $detalle->id_produccion_det }}">
                                                                Iniciado
                                                            </label>
                                                        </div>
                                                        <div class="custom-control custom-checkbox mb-2">
                                                            <input type="checkbox" class="custom-control-input estado-checkbox" 
                                                                   id="es_terminado{{ $detalle->id_produccion_det }}" 
                                                                   name="es_terminado" value="1" 
                                                                   {{ $detalle->es_terminado ? 'checked' : '' }}
                                                                   data-detalle-id="{{ $detalle->id_produccion_det }}"
                                                                   data-tipo="terminado">
                                                            <label class="custom-control-label" for="es_terminado{{ $detalle->id_produccion_det }}">
                                                                Terminado
                                                            </label>
                                                        </div>
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input estado-checkbox" 
                                                                   id="es_cancelado{{ $detalle->id_produccion_det }}" 
                                                                   name="es_cancelado" value="1" 
                                                                   {{ $detalle->es_cancelado ? 'checked' : '' }}
                                                                   data-detalle-id="{{ $detalle->id_produccion_det }}"
                                                                   data-tipo="cancelado">
                                                            <label class="custom-control-label" for="es_cancelado{{ $detalle->id_produccion_det }}">
                                                                Cancelado
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Costos y campos adicionales -->
                                            <div class="row">
                                                @if($detalle->es_personalizado)
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="costo_diseño">Costo Diseño:</label>
                                                        <input type="number" step="0.01" min="0" name="costo_diseño" 
                                                               class="form-control" value="{{ $detalle->costo_diseño }}"
                                                               {{ $detalle->es_cancelado ? 'readonly' : '' }}>
                                                    </div>
                                                </div>
                                                @endif
                                                
                                                <div class="col-md-{{ $detalle->es_personalizado ? '6' : '12' }}">
                                                    <div class="form-group">
                                                        <label for="observaciones">Observaciones:</label>
                                                        <textarea name="observaciones" class="form-control" rows="3">{{ $detalle->observaciones }}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">No hay detalles de producción registrados.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="mb-4">
        <a href="{{ route('produccion.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al Listado
        </a>
    </div>
</div>

@push('scripts')
<script>
    // Script para manejar la lógica de estados en los checkboxes
    document.addEventListener('DOMContentLoaded', function() {
        // Usar selectores basados en clases y atributos data
        const checkboxes = document.querySelectorAll('.estado-checkbox');
        
        // Asignar event listeners a todos los checkboxes
        checkboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                const detalleId = this.getAttribute('data-detalle-id');
                const tipo = this.getAttribute('data-tipo');
                
                // Obtener referencias a los otros checkboxes del mismo detalle
                const iniciadoCheckbox = document.getElementById('es_iniciado' + detalleId);
                const terminadoCheckbox = document.getElementById('es_terminado' + detalleId);
                const canceladoCheckbox = document.getElementById('es_cancelado' + detalleId);
                
                // Lógica específica según el tipo de checkbox
                if (tipo === 'cancelado' && this.checked) {
                    // Si se marca cancelado, deshabilitar terminado
                    if (terminadoCheckbox) {
                        terminadoCheckbox.checked = false;
                    }
                } else if (tipo === 'terminado' && this.checked) {
                    // Si se marca terminado, asegurar que iniciado esté marcado y cancelado desmarcado
                    if (iniciadoCheckbox) {
                        iniciadoCheckbox.checked = true;
                    }
                    if (canceladoCheckbox) {
                        canceladoCheckbox.checked = false;
                    }
                }
            });
        });
        
        // Validación adicional antes de enviar el formulario
        const formularios = document.querySelectorAll('form[action*="updateDetalle"]');
        formularios.forEach(function(form) {
            form.addEventListener('submit', function(event) {
                const detalleId = form.getAttribute('action').split('/').pop();
                const iniciadoCheckbox = document.getElementById('es_iniciado' + detalleId);
                const terminadoCheckbox = document.getElementById('es_terminado' + detalleId);
                const canceladoCheckbox = document.getElementById('es_cancelado' + detalleId);
                
                // Verificar si se está intentando marcar como terminado sin iniciar
                if (terminadoCheckbox && terminadoCheckbox.checked && iniciadoCheckbox && !iniciadoCheckbox.checked) {
                    event.preventDefault();
                    alert('No se puede marcar como terminado sin iniciar primero');
                    return false;
                }
                
                // Verificar si se están marcando como terminado y cancelado al mismo tiempo
                if (terminadoCheckbox && terminadoCheckbox.checked && canceladoCheckbox && canceladoCheckbox.checked) {
                    event.preventDefault();
                    alert('No se puede marcar como terminado y cancelado al mismo tiempo');
                    return false;
                }
                
                // Verificar observaciones para cancelados
                if (canceladoCheckbox && canceladoCheckbox.checked) {
                    const observaciones = form.querySelector('textarea[name="observaciones"]');
                    if (!observaciones || !observaciones.value.trim()) {
                        event.preventDefault();
                        alert('Debe ingresar observaciones al cancelar un detalle');
                        return false;
                    }
                }
            });
        });
    });
</script>
@endpush
@endsection