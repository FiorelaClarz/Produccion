@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Producción del Día</h1>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Pedidos para Producción - {{ now()->format('d/m/Y') }}</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('produccion.guardar-personal') }}" method="POST">
                @csrf
                
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Receta</th>
                                <th>Cant. Pedido</th>
                                <th>Cant. Esperada</th>
                                <th>Cant. Producida</th>
                                <th>Unidad Medida</th>
                                <th>Iniciar</th>
                                <th>Terminar</th>
                                <th>Cancelar</th>
                                <th>Subtotal Receta</th>
                                <th>Costo Diseño</th>
                                <th>Total</th>
                                <th>Cant. Harina</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($recetasAgrupadas && count($recetasAgrupadas) > 0)
                                @foreach($recetasAgrupadas as $idReceta => $recetaData)
                                    @php
                                        $receta = $recetaData['receta'];
                                        $cantidadPedido = $recetaData['cantidad_total'];
                                        $cantidadEsperada = $cantidadPedido * $receta->constante_crecimiento;
                                        $subtotalReceta = $receta->subtotal_receta * $cantidadPedido;
                                        
                                        // Buscar componente de harina
                                        $componenteHarina = $receta->detalles->first(function($item) {
                                            return $item->producto && stripos($item->producto->nombre, 'harina') !== false;
                                        });
                                        $cantHarina = $componenteHarina ? $componenteHarina->cantidad * $cantidadPedido : 0;
                                        
                                        // Calcular costo diseño
                                        $costoDiseno = $recetaData['es_personalizado'] ? (old('costo_diseño.'.$loop->index) ?? 0) : 0;
                                        $total = $subtotalReceta + $costoDiseno;
                                    @endphp
                                    <tr>
                                        <td>{{ $receta->producto->nombre ?? 'N/A' }}</td>
                                        <td>{{ $receta->nombre ?? 'N/A' }}</td>
                                        <td>{{ number_format($cantidadPedido, 2) }}</td>
                                        <td>{{ number_format($cantidadEsperada, 2) }}</td>
                                        <td>
                                            <input type="number" name="cantidad_producida_real[]" 
                                                   class="form-control form-control-sm" step="0.01" min="0"
                                                   value="{{ old('cantidad_producida_real.'.$loop->index, $cantidadEsperada) }}" 
                                                   {{ $recetaData['es_personalizado'] ? '' : 'readonly' }}>
                                        </td>
                                        <td>
                                            <select name="id_u_medidas_prodcc[]" class="form-control form-control-sm">
                                                @foreach($unidadesMedida as $unidad)
                                                <option value="{{ $unidad->id_u_medidas }}" 
                                                    {{ $unidad->id_u_medidas == $recetaData['id_u_medidas'] ? 'selected' : '' }}>
                                                    {{ $unidad->nombre }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" name="es_iniciado[]" class="form-check-input iniciar-check"
                                                   onchange="actualizarEstados(this)">
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" name="es_terminado[]" class="form-check-input terminar-check"
                                                   disabled>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" name="es_cancelado[]" class="form-check-input cancelar-check"
                                                   onchange="actualizarEstados(this)">
                                        </td>
                                        <td>{{ number_format($subtotalReceta, 2) }}</td>
                                        <td>
                                            @if($recetaData['es_personalizado'])
                                            <input type="number" name="costo_diseño[]" 
                                                   class="form-control form-control-sm" step="0.01" min="0"
                                                   value="{{ old('costo_diseño.'.$loop->index, 0) }}">
                                            @else
                                            <input type="number" value="0.00" readonly class="form-control form-control-sm">
                                            <input type="hidden" name="costo_diseño[]" value="0">
                                            @endif
                                        </td>
                                        <td>{{ number_format($total, 2) }}</td>
                                        <td>{{ number_format($cantHarina, 2) }}</td>
                                        <input type="hidden" name="id_recetas_cab[]" value="{{ $idReceta }}">
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="13" class="text-center text-muted py-4">
                                        <i class="fas fa-info-circle fa-2x mb-2"></i><br>
                                        No hay pedidos para producción hoy
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                
                @if($recetasAgrupadas && count($recetasAgrupadas) > 0)
                <div class="form-group mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Producción
                    </button>
                </div>
                @endif
            </form>
        </div>
    </div>
</div>

<script>
function actualizarEstados(checkbox) {
    const row = checkbox.closest('tr');
    const iniciarCheck = row.querySelector('.iniciar-check');
    const terminarCheck = row.querySelector('.terminar-check');
    const cancelarCheck = row.querySelector('.cancelar-check');
    
    if (checkbox === iniciarCheck && checkbox.checked) {
        terminarCheck.disabled = false;
        if (cancelarCheck.checked) {
            cancelarCheck.checked = false;
        }
    } else if (checkbox === cancelarCheck && checkbox.checked) {
        if (iniciarCheck.checked) {
            iniciarCheck.checked = false;
        }
        terminarCheck.checked = false;
        terminarCheck.disabled = true;
    } else if (checkbox === iniciarCheck && !checkbox.checked) {
        terminarCheck.checked = false;
        terminarCheck.disabled = true;
    }
    
    if (checkbox === terminarCheck && checkbox.checked && !iniciarCheck.checked) {
        iniciarCheck.checked = true;
    }
}
</script>
@endsection